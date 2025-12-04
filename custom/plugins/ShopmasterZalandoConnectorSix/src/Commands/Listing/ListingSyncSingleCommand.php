<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Commands\Listing;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Product\ProductCustomFields;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sm:listing:sync-single',
    description: 'Sync single product listing with Zalando'
)]
class ListingSyncSingleCommand extends Command
{
    public function __construct(
        private ConfigService $configService,
        private EntityRepository $productRepository,
        private ApiZalandoProductService $apiZalandoProductService,
        private ApiZalandoProductPsrService $apiZalandoProductPsrService,
        private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        private SalesChannelGuard $salesChannelGuard
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('ean', InputArgument::REQUIRED, 'The EAN (product number) to check and list')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Run in dry-run mode (show what would be listed without actually doing it)')
            ->addOption('sales-channel', 's', InputOption::VALUE_REQUIRED, 'Specific sales channel ID to sync. If not specified, all active Zalando sales channels will be synced')
            ->setHelp('This command allows you to check if a product exists at Zalando by EAN and list it if it does.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $ean = $input->getArgument('ean');
        $isDryRun = $input->getOption('dry-run');
        $specificSalesChannelId = $input->getOption('sales-channel');

        $io->title(sprintf('Listing Sync for EAN: %s', $ean));

        if ($isDryRun) {
            $io->note('Running in DRY-RUN mode - no changes will be made');
        }

        try {
            // Find product by EAN
            $product = $this->findProductByEan($ean);
            if (!$product) {
                $io->error(sprintf('Product with EAN "%s" not found in Shopware', $ean));
                return Command::FAILURE;
            }

            $this->displayProductInfo($io, $product);

            // Check if product is excluded from Zalando
            $isExcluded = $this->isProductExcludedFromZalando($product);
            if ($isExcluded) {
                $io->warning('Product is excluded from Zalando (checkbox "Von Zalando ausschließen" is active)');
                return Command::SUCCESS;
            }

            // Check if product is active
            if (!$this->isProductActive($product)) {
                $io->warning('Product is not active in Shopware');
                return Command::SUCCESS;
            }

            // Check if product has stock
            if (!$this->hasProductStock($product)) {
                $io->warning('Product has no stock available');
                return Command::SUCCESS;
            }

            // Get active sales channels
            $salesChannels = $this->getActiveSalesChannels($specificSalesChannelId);
            if ($salesChannels->count() === 0) {
                $io->warning('No active Zalando sales channels found');
                return Command::SUCCESS;
            }

            $this->displayActiveSalesChannels($io, $salesChannels);

            // Check PSR status first
            $io->section('Checking PSR (Product State Report) status');
            $psrProduct = null;
            try {
                $psrProduct = $this->apiZalandoProductPsrService->getPsrByEan($product->getEan() ?: $product->getProductNumber());
                if ($psrProduct && $psrProduct->getOffers() && $psrProduct->getOffers()->count() > 0) {
                    $io->success('Product found in PSR - already listed at Zalando');
                    
                    // Display PSR offers
                    $psrRows = [];
                    foreach ($psrProduct->getOffers() as $offer) {
                        $psrRows[] = [
                            $offer->getCountryCode(),
                            $offer->getStock() !== null ? $offer->getStock() : 'N/A',
                            $offer->getRegularPrice() !== null ? number_format($offer->getRegularPrice(), 2) . ' €' : 'Not set'
                        ];
                    }
                    
                    $io->table(['Country', 'Stock', 'Regular Price'], $psrRows);
                    $io->warning('Product is already listed in PSR. Skipping new listing for channels where it already exists.');
                } else {
                    $io->note('Product not found in PSR - can proceed with listing');
                }
            } catch (\Exception $e) {
                $io->note('Could not fetch PSR data: ' . $e->getMessage());
            }

            // Check if product exists at Zalando
            $io->section('Checking if product exists at Zalando');
            
            foreach ($salesChannels as $salesChannel) {
                // LIZENZ-CHECK ZUERST
                try {
                    $this->salesChannelGuard->guardSalesChannel($salesChannel->getSalesChannelId());
                } catch (SalesChannelNotLicensedException $e) {
                    $io->warning(sprintf('Sales channel %s - %s is not licensed',
                        strtoupper($salesChannel->getCountryCode()),
                        $salesChannel->getCountryName()
                    ));
                    continue;
                }

                // Check if product is already listed in PSR for this specific sales channel
                if ($psrProduct && $psrProduct->getOffers()) {
                    $countryCode = strtoupper($salesChannel->getCountryCode());
                    $existingOffer = $psrProduct->getOffers()->get($countryCode);

                    if ($existingOffer) {
                        $io->text(sprintf('Skipping sales channel %s - %s (already listed in PSR)',
                            strtoupper($salesChannel->getCountryCode()),
                            $salesChannel->getCountryName()
                        ));
                        continue;
                    }
                }

                $merchantId = $this->configService->getZalandoApiConfig()->getMerchantId();
                
                $io->text(sprintf('Checking for sales channel: %s - %s (Merchant ID: %s)', 
                    strtoupper($salesChannel->getCountryCode()),
                    $salesChannel->getCountryName(), 
                    $merchantId
                ));

                $response = $this->apiZalandoProductService->checkProductExistsByEan($ean, $merchantId);
                
                // Check response structure for REST API
                $contentArray = $response->getContentArray();
                $productExists = $response->isSuccessStatus() && !empty($contentArray['items'] ?? []);
                
                if ($productExists) {
                    $io->success(sprintf('Product exists at Zalando for sales channel: %s - %s', 
                        strtoupper($salesChannel->getCountryCode()),
                        $salesChannel->getCountryName()
                    ));
                    
                    // Prepare listing payload
                    $payload = $this->prepareListingPayload($product, $salesChannel);
                    
                    $io->section('Listing Payload');
                    $io->table(
                        ['Field', 'Value'],
                        [
                            ['Merchant Product Simple ID', $payload['merchant_product_simple_id']],
                            ['Merchant Product Config ID', $payload['merchant_product_config_id'] ?? 'Not set'],
                            ['Merchant Product Model ID', $payload['merchant_product_model_id'] ?? 'Not set'],
                        ]
                    );

                    if ($output->isVerbose()) {
                        $io->section('Full Payload (JSON)');
                        $io->text(json_encode($payload, JSON_PRETTY_PRINT));
                    }

                    if (!$isDryRun) {
                        $io->text('Sending listing request to Zalando...');
                        
                        $listingResponse = $this->apiZalandoProductService->onboardExistingProduct($payload, $merchantId, $ean);
                        
                        // REST API returns 204 No Content on success
                        if ($listingResponse->getStatus() === 204) {
                            $io->success('Product successfully listed at Zalando');
                        } else {
                            $io->error('Failed to list product at Zalando');
                            $io->text('Error: ' . json_encode($listingResponse->getContentArray(), JSON_PRETTY_PRINT));
                        }
                    } else {
                        $io->note('DRY-RUN: Would send listing request to Zalando');
                    }
                } else {
                    if ($response->getStatus() === 404 || ($response->isSuccessStatus() && empty($contentArray['items'] ?? []))) {
                        $io->warning(sprintf('Product does not exist at Zalando for sales channel: %s - %s', 
                            strtoupper($salesChannel->getCountryCode()),
                            $salesChannel->getCountryName()
                        ));
                    } else {
                        $io->error(sprintf('Error checking product existence (Status: %d): %s', 
                            $response->getStatus(),
                            json_encode($response->getContentArray())
                        ));
                    }
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            
            if ($output->isVerbose()) {
                $io->text($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }

    private function findProductByEan(string $ean): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $ean));
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('customFields');

        $result = $this->productRepository->search($criteria, Context::createDefaultContext());
        
        return $result->first();
    }

    private function displayProductInfo(SymfonyStyle $io, ProductEntity $product): void
    {
        $io->section('Product Information');
        
        $parentInfo = $product->getParentId() ? 'Yes (Variant)' : 'No (Main Product)';
        
        // Determine actual active status (with inheritance)
        $actualActiveStatus = $this->isProductActive($product);
        $displayActiveStatus = $actualActiveStatus ? 'Yes' : 'No';
        
        // Show inheritance info if applicable
        if ($product->getParentId() && $product->getActive() === null) {
            $displayActiveStatus .= ' (inherited from parent)';
        }
        
        $io->table(
            ['Property', 'Value'],
            [
                ['Product ID', $product->getId()],
                ['Product Number (EAN)', $product->getProductNumber()],
                ['Name', $product->getName()],
                ['Has Parent', $parentInfo],
                ['Active', $displayActiveStatus],
            ]
        );

        if ($product->getOptions() && $product->getOptions()->count() > 0) {
            $io->text('Options:');
            foreach ($product->getOptions() as $option) {
                $io->text(sprintf('  - %s: %s', $option->getGroup()->getName(), $option->getName()));
            }
        }
    }

    private function isProductExcludedFromZalando(ProductEntity $product): bool
    {
        $customFields = $product->getCustomFields();
        if (!$customFields) {
            return false;
        }

        return $customFields[ProductCustomFields::CUSTOM_FIELD_EXCLUDE_FROM_ZALANDO] ?? false;
    }

    private function isProductActive(ProductEntity $product): bool
    {
        // Check if product has explicit active status
        $productActive = $product->getActive();
        
        // If explicitly set (true or false), use that
        if ($productActive !== null) {
            return $productActive;
        }

        // If null (inherited), check parent (for variants)
        if ($product->getParentId()) {
            $criteria = new Criteria([$product->getParentId()]);
            $parent = $this->productRepository->search($criteria, Context::createDefaultContext())->first();
            
            return $parent ? $parent->getActive() ?? false : false;
        }

        // Main product without explicit active status - default to false
        return false;
    }

    private function hasProductStock(ProductEntity $product): bool
    {
        // Check available stock
        $stock = $product->getAvailableStock();
        
        if ($stock > 0) {
            return true;
        }
        
        // If this is a variant with no stock, check parent
        if ($product->getParentId()) {
            $criteria = new Criteria([$product->getParentId()]);
            $parent = $this->productRepository->search($criteria, Context::createDefaultContext())->first();
            
            if ($parent) {
                return $parent->getAvailableStock() > 0;
            }
        }
        
        return false;
    }

    private function getActiveSalesChannels(?string $specificSalesChannelId)
    {
        $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection();

        if ($specificSalesChannelId) {
            return $salesChannelCollection->filter(function ($channel) use ($specificSalesChannelId) {
                return $channel->getSalesChannelId() === $specificSalesChannelId;
            });
        }

        return $salesChannelCollection->getActive();
    }

    private function displayActiveSalesChannels(SymfonyStyle $io, $salesChannels): void
    {
        $io->section('Active Zalando Sales Channels');
        
        $channelData = [];
        foreach ($salesChannels as $channel) {
            $channelData[] = [
                strtoupper($channel->getCountryCode()),
                $channel->getCountryName(),
                $channel->getSalesChannelId()
            ];
        }

        $io->table(['Country Code', 'Country Name', 'Sales Channel ID'], $channelData);
    }

    private function prepareListingPayload(ProductEntity $product, $salesChannel): array
    {
        // Simple ID is just the EAN
        $merchantProductSimpleId = $product->getEan() ?: $product->getProductNumber();
        
        // Get parent product number if this is a variant
        $parentProductNumber = $product->getProductNumber();
        if ($product->getParentId()) {
            $parentCriteria = new Criteria([$product->getParentId()]);
            $parent = $this->productRepository->search($parentCriteria, Context::createDefaultContext())->first();
            if ($parent) {
                $parentProductNumber = $parent->getProductNumber();
            }
        }

        // Config ID: Parent product number (without option)
        $merchantProductConfigId = $parentProductNumber;
        
        // Model ID: Parent product number + parent product name
        $merchantProductModelId = $parentProductNumber;
        if ($product->getParentId()) {
            $parentCriteria = new Criteria([$product->getParentId()]);
            $parent = $this->productRepository->search($parentCriteria, Context::createDefaultContext())->first();
            if ($parent && $parent->getName()) {
                $merchantProductModelId .= ' ' . $parent->getName();
            }
        }

        // REST API payload structure
        return [
            'merchant_product_simple_id' => $merchantProductSimpleId,
            'merchant_product_config_id' => $merchantProductConfigId,
            'merchant_product_model_id' => $merchantProductModelId
        ];
    }
}