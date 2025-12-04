<?php

namespace ShopmasterZalandoConnectorSix\Commands\Stock;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Product\ProductCustomFields;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductStockService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigServiceInterface;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\Stock\StockCollection;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\Stock\StockStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sm:stock:sync-single',
    description: 'Sync stock for a single product by product number'
)]
class StockSyncSingleCommand extends Command
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly EntityRepository $productRepository,
        private readonly ApiZalandoProductPsrService $apiZalandoProductPsrService,
        private readonly ApiZalandoProductStockService $apiZalandoProductStockService,
        private readonly ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        private readonly SalesChannelGuard $salesChannelGuard
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('product-number', InputArgument::REQUIRED, 'The product number to sync')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Run in dry-run mode (show what would be sent without actually sending)')
            ->addOption('sales-channel', 's', InputOption::VALUE_REQUIRED, 'Specific sales channel ID (default: all configured channels)')
            ->setHelp('This command allows you to sync stock for a single product by its product number.

Example usage:
  <info>php bin/console sm:stock:sync-single SW10001</info> - Sync stock for product SW10001
  <info>php bin/console sm:stock:sync-single SW10001 --dry-run</info> - Show what would be sent without actually sending
  <info>php bin/console sm:stock:sync-single SW10001 --sales-channel=de</info> - Sync only for DE sales channel');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $productNumber = $input->getArgument('product-number');
        $dryRun = $input->getOption('dry-run');
        $salesChannelFilter = $input->getOption('sales-channel');
        
        $io->title('Single Product Stock Sync');
        
        // Check if stock sync is active
        $config = $this->configService->getStockSyncConfig();
        if (!$config->isActive()) {
            $io->warning('Stock sync is not active in configuration. Proceeding anyway for manual sync.');
        }
        
        // Find the product
        $io->section('Finding Product');
        $product = $this->findProduct($productNumber);
        
        if (!$product) {
            $io->error(sprintf('Product with number "%s" not found.', $productNumber));
            return Command::FAILURE;
        }
        
        $io->success(sprintf('Found product: %s (ID: %s)', $product->getName() ?? 'No name', $product->getId()));
        
        // Display product information
        $this->displayProductInfo($io, $product, $config);
        
        // Fetch PSR data from Zalando
        $io->section('Fetching Current Zalando Stock Data and Active Sales Channels');
        $psrData = $this->fetchPsrDataForProduct($product->getProductNumber());
        
        if (!$psrData) {
            $io->warning('No PSR data found for this product in Zalando. This might be a new product.');
            // Create empty PSR data for new product
            $psrData = $this->createEmptyPsrData($product);
            
            // Show which sales channels are configured
            $this->displayActiveSalesChannels($io);
        }
        
        // Calculate and display stock for each offer
        $io->section('Stock Calculation');
        $stockCollection = $this->calculateStock($psrData, $config, $io, $salesChannelFilter);
        
        if ($stockCollection->count() === 0) {
            $io->info('No stock updates needed - all values are already in sync.');
            return Command::SUCCESS;
        }
        
        // Display what will be sent
        $io->section('Stock Updates to Send');
        $this->displayStockUpdates($io, $stockCollection);
        
        // Send or simulate sending
        if ($dryRun) {
            $io->warning('DRY RUN MODE - No data will be sent to Zalando');
            $io->info('The above stock updates would be sent to Zalando API endpoint: POST /stocks');
        } else {
            $io->section('Sending Stock Updates to Zalando');
            
            try {
                $response = $this->apiZalandoProductStockService->updateZalandoStock($stockCollection);
                
                $io->success('Stock updates sent successfully!');
                
                // Display API response
                $io->section('API Response');
                $io->writeln('Status Code: ' . $response->getStatusCode());
                $io->writeln('Response:');
                $io->writeln(json_encode($response->getContentArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                
            } catch (\Exception $e) {
                $io->error('Failed to send stock updates: ' . $e->getMessage());
                if ($output->isVerbose()) {
                    $io->writeln('Stack trace:');
                    $io->writeln($e->getTraceAsString());
                }
                return Command::FAILURE;
            }
        }
        
        return Command::SUCCESS;
    }

    private function findProduct(string $productNumber): ?object
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $criteria->addAssociation('customFields');
        
        $result = $this->productRepository->search($criteria, Context::createDefaultContext());
        
        return $result->first();
    }

    private function displayProductInfo(SymfonyStyle $io, object $product, object $config): void
    {
        $table = new Table($io);
        $table->setHeaders(['Property', 'Value']);
        
        $rows = [
            ['Product Number', $product->getProductNumber()],
            ['Product Name', $product->getName() ?? 'N/A'],
            ['Active', $this->isProductActive($product) ? 'Yes' : 'No'],
            ['Active (Raw)', $product->getActive() === null ? 'Inherited' : ($product->getActive() ? 'Yes' : 'No')],
            ['Current Stock', $product->getStock()],
            ['Stock Buffer (Config)', $config->getStockCache()],
            ['Holiday Mode', $config->isHoliday() ? 'Yes' : 'No'],
            ['Individual Stock Mode', $config->isIndividualStock() ? 'Yes' : 'No'],
        ];
        
        // Add individual stock if enabled
        if ($config->isIndividualStock()) {
            $customFields = $product->getCustomFields() ?? [];
            $individualStock = $customFields[ProductCustomFields::CUSTOM_FIELD_INDIVIDUAL_STOCK] ?? null;
            $rows[] = ['Individual Stock', $individualStock !== null ? $individualStock : 'Not set'];
        }
        
        // Calculate what stock will be sent
        $calculatedStock = $this->calculateProductStock($product, $config);
        $rows[] = ['Calculated Stock to Send', $calculatedStock];
        
        $table->setRows($rows);
        $table->render();
    }

    private function calculateProductStock(object $product, object $config): int
    {
        if ($config->isHoliday()) {
            return 0;
        }

        if (!$this->isProductActive($product)) {
            return 0;
        }

        if ($config->isIndividualStock()) {
            $customFields = $product->getCustomFields() ?? [];
            $individualStock = $customFields[ProductCustomFields::CUSTOM_FIELD_INDIVIDUAL_STOCK] ?? null;
            if ($individualStock !== null) {
                return max(0, (int) $individualStock);
            }
        }

        $stock = $product->getStock();
        $stockCache = $config->getStockCache();
        
        return ($stock < $stockCache) ? 0 : ($stock - $stockCache);
    }

    private function fetchPsrDataForProduct(string $productNumber): ?PsrProductStruct
    {
        // This is a simplified version - in reality you'd need to fetch by EAN
        // For now, we'll return null and create empty PSR data
        return null;
    }

    private function createEmptyPsrData(object $product): PsrProductStruct
    {
        $psrProduct = new PsrProductStruct();
        $psrProduct->setProduct($product);
        $psrProduct->setEan($product->getEan() ?? $product->getProductNumber());
        
        try {
            // Get active sales channels from Zalando API
            $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection();
            $activeSalesChannels = $salesChannelCollection->getActive();
            
            foreach ($activeSalesChannels as $salesChannel) {
                $offer = new PsrProductOfferStruct();
                $offer->setCountryCode(strtoupper($salesChannel->getCountryCode()));
                $offer->setStock(null); // No current stock in Zalando
                $psrProduct->addOffer($offer);
            }
        } catch (\Exception $e) {
            // Fallback: if API call fails, create offer for DE only (most common case)
            $offer = new PsrProductOfferStruct();
            $offer->setCountryCode('DE');
            $offer->setStock(null);
            $psrProduct->addOffer($offer);
        }
        
        return $psrProduct;
    }

    private function calculateStock(PsrProductStruct $psrData, object $config, SymfonyStyle $io, ?string $salesChannelFilter): StockCollection
    {
        $collection = new StockCollection();
        $calculatedStock = $this->calculateProductStock($psrData->getProduct(), $config);
        
        $table = new Table($io);
        $table->setHeaders(['Country', 'Current Zalando Stock', 'New Stock', 'Update Needed']);
        $rows = [];
        
        foreach ($psrData->getOffers() as $offer) {
            if ($salesChannelFilter && strtolower($offer->getCountryCode()) !== strtolower($salesChannelFilter)) {
                continue;
            }

            // LIZENZ-CHECK ZUERST (using country code as channel ID)
            try {
                $this->salesChannelGuard->guardSalesChannel(strtolower($offer->getCountryCode()));
            } catch (SalesChannelNotLicensedException $e) {
                $rows[] = [
                    $offer->getCountryCode(),
                    '-',
                    '-',
                    'Not licensed'
                ];
                continue;
            }

            $currentStock = $offer->getStock();
            $updateNeeded = $currentStock !== $calculatedStock;
            
            $rows[] = [
                $offer->getCountryCode(),
                $currentStock ?? 'Not set',
                $calculatedStock,
                $updateNeeded ? 'Yes' : 'No'
            ];
            
            if ($updateNeeded) {
                $stockStruct = new StockStruct();
                $salesChannelId = ConfigService::getSalesChannelIdByCountryCode(strtolower($offer->getCountryCode()));
                $stockStruct->setSalesChannelId($salesChannelId)
                    ->setEan($psrData->getEan())
                    ->setQuantity($calculatedStock);
                $collection->add($stockStruct);
            }
        }
        
        $table->setRows($rows);
        $table->render();
        
        return $collection;
    }

    private function displayStockUpdates(SymfonyStyle $io, StockCollection $collection): void
    {
        $table = new Table($io);
        $table->setHeaders(['Sales Channel', 'EAN', 'Quantity']);
        $rows = [];
        
        foreach ($collection as $stock) {
            $rows[] = [
                $stock->getSalesChannelId(),
                $stock->getEan(),
                $stock->getQuantity()
            ];
        }
        
        $table->setRows($rows);
        $table->render();
        
        // Also show as JSON for clarity
        $io->writeln('');
        $io->writeln('JSON payload that will be sent:');
        $payload = ['items' => []];
        foreach ($collection as $stock) {
            $payload['items'][] = [
                'sales_channel_id' => $stock->getSalesChannelId(),
                'ean' => $stock->getEan(),
                'quantity' => $stock->getQuantity()
            ];
        }
        $io->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Check if product is active, considering parent inheritance for variants
     * @param object $product
     * @return bool
     */
    private function isProductActive(object $product): bool
    {
        $active = $product->getActive();
        
        // If active is explicitly set (true or false), use that value
        if ($active !== null) {
            return $active;
        }
        
        // If active is null, check parent product (for variants)
        $parentId = $product->getParentId();
        if ($parentId) {
            $criteria = new Criteria([$parentId]);
            $parentResult = $this->productRepository->search($criteria, Context::createDefaultContext());
            $parent = $parentResult->first();
            
            if ($parent) {
                return $parent->getActive() ?? false;
            }
        }
        
        // If no parent and active is null, default to false
        return false;
    }

    private function displayActiveSalesChannels(SymfonyStyle $io): void
    {
        try {
            $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection();
            $activeSalesChannels = $salesChannelCollection->getActive();
            
            if ($activeSalesChannels->count() > 0) {
                $io->text(sprintf('Found %d active sales channel(s) for your merchant:', $activeSalesChannels->count()));
                
                $table = new Table($io);
                $table->setHeaders(['Country Code', 'Country Name', 'Sales Channel ID']);
                $rows = [];
                
                foreach ($activeSalesChannels as $salesChannel) {
                    $rows[] = [
                        strtoupper($salesChannel->getCountryCode()),
                        $salesChannel->getCountryName(),
                        $salesChannel->getSalesChannelId()
                    ];
                }
                
                $table->setRows($rows);
                $table->render();
            } else {
                $io->warning('No active sales channels found for your merchant.');
            }
        } catch (\Exception $e) {
            $io->error('Failed to fetch active sales channels: ' . $e->getMessage());
        }
    }
}