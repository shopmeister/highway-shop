<?php

namespace ShopmasterZalandoConnectorSix\Commands\Price;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPriceService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormPriceSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\OffersPriceCollection;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\OffersPriceStruct;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\PriceStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sm:price:sync-single',
    description: 'Sync price for a single product by product number'
)]
class PriceSyncSingleCommand extends Command
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly EntityRepository $productRepository,
        private readonly ApiZalandoProductPsrService $apiZalandoProductPsrService,
        private readonly ApiZalandoProductPriceService $apiZalandoProductPriceService,
        private readonly ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        private readonly LoggerInterface $logger,
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
            ->setHelp('This command allows you to sync price for a single product by its product number.

Example usage:
  <info>php bin/console sm:price:sync-single SW10001</info> - Sync price for product SW10001
  <info>php bin/console sm:price:sync-single SW10001 --dry-run</info> - Show what would be sent without actually sending
  <info>php bin/console sm:price:sync-single SW10001 --sales-channel=de</info> - Sync only for DE sales channel
  <info>php bin/console sm:price:sync-single SW10001 -v</info> - Show detailed price information with verbose output');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $productNumber = $input->getArgument('product-number');
        $dryRun = $input->getOption('dry-run');
        $salesChannelFilter = $input->getOption('sales-channel');
        
        $io->title('Single Product Price Sync');
        
        // Find the product with associations for prices
        $io->section('Finding Product');
        $product = $this->findProduct($productNumber);
        
        if (!$product) {
            $io->error(sprintf('Product with number "%s" not found.', $productNumber));
            return Command::FAILURE;
        }
        
        $io->success(sprintf('Found product: %s (ID: %s)', $product->getName() ?? 'No name', $product->getId()));
        
        // Check if product is active
        if (!$this->isProductActive($product)) {
            $io->warning('Product is not active. Price sync will be skipped.');
            return Command::SUCCESS;
        }
        
        // Display product information
        $this->displayProductInfo($io, $product, $output);
        
        // Fetch PSR data from Zalando
        $io->section('Fetching Current Zalando Price Data and Active Sales Channels');
        $psrData = $this->fetchPsrDataForProduct($product);
        
        if (!$psrData) {
            $io->warning('No PSR data found for this product in Zalando. This might be a new product.');
            $psrData = $this->createEmptyPsrData($product);
            $this->displayActiveSalesChannels($io);
        } else {
            // Display PSR data in verbose mode
            if ($output->isVerbose()) {
                $this->displayPsrData($io, $psrData);
            }
            $this->displayActiveSalesChannels($io);
        }
        
        // Show configuration info if verbose
        if ($output->isVerbose()) {
            $io->section('Price Sync Configuration');
            $this->displayPriceSyncConfig($io);
        }
        
        // Calculate and display prices for each sales channel
        $io->section('Price Calculation');
        $priceCollection = $this->calculatePrices($product, $psrData, $io, $salesChannelFilter, $output);
        
        if ($priceCollection->count() === 0) {
            $io->info('No price updates needed - all values are already in sync.');
            return Command::SUCCESS;
        }
        
        // Display what will be sent
        $io->section('Price Updates to Send');
        $this->displayPriceUpdates($io, $priceCollection);
        
        // Send or simulate sending
        if ($dryRun) {
            $io->warning('DRY RUN MODE - No data will be sent to Zalando');
            $io->info('The above price updates would be sent to Zalando API endpoint: POST /offers-prices');
        } else {
            $io->section('Sending Price Updates to Zalando');
            
            try {
                $response = $this->apiZalandoProductPriceService->updateZalandoPrice($priceCollection);
                
                $io->success('Price updates sent successfully!');
                
                // Display API response
                $io->section('API Response');
                $io->writeln('Status: ' . $response->getStatus());
                $io->writeln('Response:');
                $io->writeln(json_encode($response->getContentArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                
            } catch (\Exception $e) {
                $io->error('Failed to send price updates: ' . $e->getMessage());
                if ($output->isVerbose()) {
                    $io->writeln('Stack trace:');
                    $io->writeln($e->getTraceAsString());
                }
                return Command::FAILURE;
            }
        }
        
        return Command::SUCCESS;
    }

    private function findProduct(string $productNumber): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $criteria->addAssociation('prices');
        $criteria->addAssociation('prices.price');
        $criteria->addAssociation('customFields');
        
        $result = $this->productRepository->search($criteria, Context::createDefaultContext());
        
        return $result->first();
    }

    private function displayProductInfo(SymfonyStyle $io, ProductEntity $product, OutputInterface $output): void
    {
        $table = new Table($io);
        $table->setHeaders(['Property', 'Value']);
        
        $rows = [
            ['Product Number', $product->getProductNumber()],
            ['Product Name', $product->getName() ?? 'N/A'],
            ['Active', $this->isProductActive($product) ? 'Yes' : 'No'],
            ['Active (Raw)', $product->getActive() === null ? 'Inherited' : ($product->getActive() ? 'Yes' : 'No')],
            ['EAN', $product->getEan() ?? 'Not set'],
            ['Is Variant', $product->getParentId() ? 'Yes' : 'No'],
        ];
        
        if ($product->getParentId()) {
            $rows[] = ['Parent ID', $product->getParentId()];
        }
        
        // Show price information if verbose
        if ($output->isVerbose()) {
            if ($product->getPrice() && $product->getPrice()->first()) {
                $defaultPrice = $product->getPrice()->first();
                $rows[] = ['Default Price (Gross)', number_format($defaultPrice->getGross(), 2) . ' €'];
                $rows[] = ['Default Price (Net)', number_format($defaultPrice->getNet(), 2) . ' €'];
                if ($defaultPrice->getListPrice()) {
                    $rows[] = ['List Price (Gross)', number_format($defaultPrice->getListPrice()->getGross(), 2) . ' €'];
                }
            } else {
                $rows[] = ['Default Price', 'Not set'];
            }
            
            $rows[] = ['Total Price Rules', $product->getPrices() ? $product->getPrices()->count() : 0];
        }
        
        $table->setRows($rows);
        $table->render();
    }

    private function fetchPsrDataForProduct(ProductEntity $product): ?PsrProductStruct
    {
        $ean = $product->getEan();
        if (!$ean) {
            return null;
        }
        
        try {
            return $this->apiZalandoProductPsrService->getPsrByEan($ean);
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch PSR data', [
                'ean' => $ean,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function createEmptyPsrData(ProductEntity $product): PsrProductStruct
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
                $offer->setRegularPrice(null); // No current price in Zalando
                $offer->setDiscountedPrice(null);
                $psrProduct->addOffer($offer);
            }
        } catch (\Exception $e) {
            // Fallback: if API call fails, create offer for DE only (most common case)
            $offer = new PsrProductOfferStruct();
            $offer->setCountryCode('DE');
            $offer->setRegularPrice(null);
            $offer->setDiscountedPrice(null);
            $psrProduct->addOffer($offer);
        }
        
        return $psrProduct;
    }

    private function calculatePrices(ProductEntity $product, PsrProductStruct $psrData, SymfonyStyle $io, ?string $salesChannelFilter, OutputInterface $output): OffersPriceCollection
    {
        $collection = new OffersPriceCollection();
        
        $table = new Table($io);
        $headers = ['Country', 'Config Active', 'Current Regular', 'Current Promotional', 'New Regular', 'New Promotional', 'Update Needed'];
        if ($output->isVerbose()) {
            $headers[] = 'Price Source';
            $headers[] = 'Rule Used';
        }
        $table->setHeaders($headers);
        $rows = [];
        
        /** @var SalesChannelStruct $salesChannel */
        foreach ($this->apiZalandoSalesChannelsService->getCollection() as $salesChannel) {
            if ($salesChannelFilter && strtolower($salesChannel->getCountryCode()) !== strtolower($salesChannelFilter)) {
                continue;
            }

            // LIZENZ-CHECK ZUERST
            try {
                $this->salesChannelGuard->guardSalesChannel($salesChannel->getSalesChannelId());
            } catch (SalesChannelNotLicensedException $e) {
                $rows[] = array_merge([
                    $salesChannel->getCountryCode(),
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    'Not licensed'
                ], $output->isVerbose() ? ['License required', '-'] : []);
                continue;
            }

            $config = $this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId());

            if (!$config->isActive()) {
                $rows[] = array_merge([
                    $salesChannel->getCountryCode(),
                    'No',
                    '-',
                    '-',
                    '-',
                    '-',
                    'Skipped'
                ], $output->isVerbose() ? ['Config disabled', '-'] : []);
                continue;
            }
            
            // Get current Zalando prices first
            $psrOffer = $psrData->getOffers()->get(strtoupper($salesChannel->getCountryCode()));
            
            $offersPriceStruct = $this->getOffersPriceStruct($product, $salesChannel, $psrOffer, $output->isVerbose());
            
            if (!$offersPriceStruct) {
                $rows[] = array_merge([
                    $salesChannel->getCountryCode(),
                    'Yes',
                    '-',
                    '-',
                    'No price found',
                    '-',
                    'Skipped'
                ], $output->isVerbose() ? ['No price available', '-'] : []);
                continue;
            }
            $currentRegular = $psrOffer ? $psrOffer->getRegularPrice() : null;
            $currentPromotional = $psrOffer ? $psrOffer->getDiscountedPrice() : null;
            
            $newRegular = $offersPriceStruct->getRegularPrice()->getAmount();
            $newPromotional = $offersPriceStruct->getPromotionalPrice() ? $offersPriceStruct->getPromotionalPrice()->getAmount() : null;
            
            // Check if update is needed
            $updateNeeded = ($currentRegular !== $newRegular) || 
                           ($currentPromotional !== $newPromotional);
            
            $row = [
                $salesChannel->getCountryCode(),
                'Yes',
                $currentRegular ? number_format($currentRegular, 2) . ' €' : 'Not set',
                $currentPromotional ? number_format($currentPromotional, 2) . ' €' : '-',
                number_format($newRegular, 2) . ' €',
                $newPromotional ? number_format($newPromotional, 2) . ' €' : '-',
                $updateNeeded ? 'Yes' : 'No'
            ];
            
            if ($output->isVerbose()) {
                $priceInfo = $this->getPriceSourceInfo($product, $config);
                $row[] = $priceInfo['source'];
                $row[] = $priceInfo['rule'];
            }
            
            $rows[] = $row;
            
            if ($updateNeeded) {
                $collection->add($offersPriceStruct);
            }
        }
        
        $table->setRows($rows);
        $table->render();
        
        return $collection;
    }

    private function getOffersPriceStruct(ProductEntity $product, SalesChannelStruct $salesChannel, ?PsrProductOfferStruct $psrOffer = null, bool $verbose = false): ?OffersPriceStruct
    {
        $config = $this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId());
        $price = $this->getProductPriceByConfig($product, $config);
        
        if (!$price) {
            return null;
        }
        
        $struct = new OffersPriceStruct();
        $struct->setEan($product->getEan() ?: $product->getProductNumber())
            ->setArticleNumber($product->getProductNumber())
            ->setIgnoreWarnings($config->isIgnoreWarnings())
            ->setSalesChannelId($salesChannel->getSalesChannelId());

        // Calculate regular price
        $regularPrice = $price->getGross();
        $hasPromotionalPrice = $config->isActivePromotionalPrice()
            && $price->getListPrice()
            && $price->getListPrice()->getGross() > $price->getGross();

        if ($hasPromotionalPrice) {
            $regularPrice = $price->getListPrice()->getGross();
        }

        $struct->setRegularPrice(new PriceStruct($regularPrice));

        // Only set promotional price under specific conditions:
        // 1. If no PSR entry exists - send only regular price (no promotional)
        // 2. If PSR exists and regular price unchanged - can send promotional price
        // 3. If PSR exists and regular price changed - send only regular price (no promotional)
        // 4. If promotional price equals regular price - send only regular price
        // 5. If discount is less than 15% - either auto-adjust or skip promotional price

        if ($hasPromotionalPrice) {
            $promotionalPrice = $price->getGross();

            // Check if promotional price equals regular price
            if (abs($promotionalPrice - $regularPrice) < 0.01) {
                // Promotional price equals regular price - send only regular price
                // This ensures existing promotional prices are cleared when set back to full price
                return $struct;
            }

            // Calculate discount percentage
            $discountPercent = (($regularPrice - $promotionalPrice) / $regularPrice) * 100;

            // Check if discount is less than 15%
            if ($discountPercent < 15.0) {
                if ($config->isAutoAdjustPromotionalPriceTo15Percent()) {
                    // Auto-adjust to exactly 15% discount
                    $promotionalPrice = $regularPrice * 0.85;
                } else {
                    // Skip promotional price - send only regular price
                    return $struct;
                }
            }

            if (!$psrOffer) {
                // No PSR entry - send only regular price
                // Do not set promotional price
            } elseif ($psrOffer->getRegularPrice() === $regularPrice) {
                // Regular price unchanged - can send promotional price
                $struct->setPromotionalPrice(new PriceStruct($promotionalPrice));
            } else {
                // Regular price changed - send only regular price
                // Do not set promotional price
            }
        }

        return $struct;
    }

    private function getProductPriceByConfig(ProductEntity $product, SettingFormPriceSyncStruct $config): ?Price
    {
        $price = null;
        
        // First try to get price from the product itself
        if (!empty($config->getRuleId())) {
            if ($product->getPrices()) {
                $priceEntity = $product->getPrices()->filter(function (ProductPriceEntity $priceEntity) use ($config) {
                    return ($priceEntity->getRuleId() === $config->getRuleId() && $priceEntity->getQuantityStart() === 1);
                })->first();
                if ($priceEntity && $priceEntity->getPrice()) {
                    $price = $priceEntity->getPrice()->first();
                }
            }
        } else {
            if ($product->getPrice()) {
                $price = $product->getPrice()->first();
            }
        }
        
        // Fallback to parent product if variant has no price
        if (!$price && $product->getParentId()) {
            $parentProduct = $this->getParentProduct($product);
            if ($parentProduct) {
                // Try to get advanced price with rule ID from parent
                if (!empty($config->getRuleId())) {
                    if ($parentProduct->getPrices()) {
                        $priceEntity = $parentProduct->getPrices()->filter(function (ProductPriceEntity $priceEntity) use ($config) {
                            return ($priceEntity->getRuleId() === $config->getRuleId() && $priceEntity->getQuantityStart() === 1);
                        })->first();
                        if ($priceEntity && $priceEntity->getPrice()) {
                            $price = $priceEntity->getPrice()->first();
                        }
                    }
                }
                
                // If still no price and we had a rule ID, fallback to standard price of parent
                if (!$price && !empty($config->getRuleId())) {
                    if ($parentProduct->getPrice()) {
                        $price = $parentProduct->getPrice()->first();
                    }
                }
                
                // If no rule ID was set, use standard price directly
                if (!$price && empty($config->getRuleId())) {
                    if ($parentProduct->getPrice()) {
                        $price = $parentProduct->getPrice()->first();
                    }
                }
            }
        }
        
        return $price;
    }

    private function getParentProduct(ProductEntity $product): ?ProductEntity
    {
        if (!$product->getParentId()) {
            return null;
        }
        
        $criteria = new Criteria([$product->getParentId()]);
        $criteria->addAssociation('prices');
        $criteria->addAssociation('prices.price');
        
        $result = $this->productRepository->search($criteria, Context::createDefaultContext());
        
        return $result->first();
    }

    private function getPriceSourceInfo(ProductEntity $product, SettingFormPriceSyncStruct $config): array
    {
        $source = 'Unknown';
        $rule = '-';
        
        if (!empty($config->getRuleId())) {
            $priceEntity = null;
            if ($product->getPrices()) {
                $priceEntity = $product->getPrices()->filter(function (ProductPriceEntity $priceEntity) use ($config) {
                    return ($priceEntity->getRuleId() === $config->getRuleId() && $priceEntity->getQuantityStart() === 1);
                })->first();
            }
            
            if ($priceEntity) {
                $source = 'Advanced Pricing';
                $rule = substr($config->getRuleId(), 0, 8) . '...';
            } else if ($product->getParentId()) {
                $parentProduct = $this->getParentProduct($product);
                if ($parentProduct && $parentProduct->getPrices()) {
                    $parentPriceEntity = $parentProduct->getPrices()->filter(function (ProductPriceEntity $priceEntity) use ($config) {
                        return ($priceEntity->getRuleId() === $config->getRuleId() && $priceEntity->getQuantityStart() === 1);
                    })->first();
                    if ($parentPriceEntity) {
                        $source = 'Parent Advanced Pricing';
                        $rule = substr($config->getRuleId(), 0, 8) . '...';
                    } else {
                        $source = 'No price for rule';
                        $rule = substr($config->getRuleId(), 0, 8) . '...';
                    }
                } else {
                    $source = 'No parent prices';
                    $rule = substr($config->getRuleId(), 0, 8) . '...';
                }
            } else {
                $source = 'No price for rule';
                $rule = substr($config->getRuleId(), 0, 8) . '...';
            }
        } else {
            if ($product->getPrice() && $product->getPrice()->first()) {
                $source = 'Default Price';
            } else if ($product->getParentId()) {
                $source = 'Parent Default Price';
            } else {
                $source = 'No price found';
            }
        }
        
        return ['source' => $source, 'rule' => $rule];
    }

    private function displayPriceUpdates(SymfonyStyle $io, OffersPriceCollection $collection): void
    {
        $table = new Table($io);
        $table->setHeaders(['Sales Channel', 'EAN', 'Regular Price', 'Promotional Price', 'Ignore Warnings']);
        $rows = [];
        
        foreach ($collection as $price) {
            $rows[] = [
                $price->getSalesChannelId(),
                $price->getEan(),
                number_format($price->getRegularPrice()->getAmount(), 2) . ' €',
                $price->getPromotionalPrice() ? number_format($price->getPromotionalPrice()->getAmount(), 2) . ' €' : '-',
                $price->isIgnoreWarnings() ? 'Yes' : 'No'
            ];
        }
        
        $table->setRows($rows);
        $table->render();
        
        // Also show as JSON for clarity
        $io->writeln('');
        $io->writeln('JSON payload that will be sent:');
        $payload = ['items' => []];
        foreach ($collection as $price) {
            $item = [
                'sales_channel_id' => $price->getSalesChannelId(),
                'ean' => $price->getEan(),
                'regular_price' => [
                    'amount' => $price->getRegularPrice()->getAmount(),
                    'currency' => $price->getRegularPrice()->getCurrency()
                ],
                'ignore_warnings' => $price->isIgnoreWarnings()
            ];
            
            if ($price->getPromotionalPrice()) {
                $item['promotional_price'] = [
                    'amount' => $price->getPromotionalPrice()->getAmount(),
                    'currency' => $price->getPromotionalPrice()->getCurrency()
                ];
            }
            
            $payload['items'][] = $item;
        }
        $io->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Check if product is active, considering parent inheritance for variants
     */
    private function isProductActive(ProductEntity $product): bool
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

    private function displayPsrData(SymfonyStyle $io, PsrProductStruct $psrData): void
    {
        $io->success('PSR data found for product!');
        
        $table = new Table($io);
        $table->setHeaders(['Country', 'Stock', 'Regular Price', 'Discounted Price']);
        
        $rows = [];
        foreach ($psrData->getOffers() as $offer) {
            $rows[] = [
                $offer->getCountryCode(),
                $offer->getStock() !== null ? $offer->getStock() : 'N/A',
                $offer->getRegularPrice() !== null ? number_format($offer->getRegularPrice(), 2) . ' €' : 'Not set',
                $offer->getDiscountedPrice() !== null ? number_format($offer->getDiscountedPrice(), 2) . ' €' : '-'
            ];
        }
        
        if (empty($rows)) {
            $io->note('No offer data available in PSR');
        } else {
            $table->setRows($rows);
            $table->render();
        }
    }
    
    private function displayActiveSalesChannels(SymfonyStyle $io): void
    {
        try {
            $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection();
            $activeSalesChannels = $salesChannelCollection->getActive();
            
            if ($activeSalesChannels->count() > 0) {
                $io->text(sprintf('Found %d active sales channel(s) for your merchant:', $activeSalesChannels->count()));
                
                $table = new Table($io);
                $table->setHeaders(['Country Code', 'Country Name', 'Sales Channel ID', 'Price Sync Active']);
                $rows = [];
                
                foreach ($activeSalesChannels as $salesChannel) {
                    $config = $this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId());
                    $rows[] = [
                        strtoupper($salesChannel->getCountryCode()),
                        $salesChannel->getCountryName(),
                        $salesChannel->getSalesChannelId(),
                        $config->isActive() ? 'Yes' : 'No'
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
    
    private function displayPriceSyncConfig(SymfonyStyle $io): void
    {
        try {
            $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection();

            $table = new Table($io);
            $table->setHeaders(['Sales Channel', 'Active', 'Rule ID', 'Ignore Warnings', 'Promotional Prices', 'Auto-Adjust 15%']);
            $rows = [];

            foreach ($salesChannelCollection as $salesChannel) {
                $config = $this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId());
                $rows[] = [
                    $salesChannel->getSalesChannelId(),
                    $config->isActive() ? 'Yes' : 'No',
                    $config->getRuleId() ?: 'Default prices',
                    $config->isIgnoreWarnings() ? 'Yes' : 'No',
                    $config->isActivePromotionalPrice() ? 'Yes' : 'No',
                    $config->isAutoAdjustPromotionalPriceTo15Percent() ? 'Yes' : 'No'
                ];
            }

            $table->setRows($rows);
            $table->render();
        } catch (\Exception $e) {
            $io->error('Failed to display price sync config: ' . $e->getMessage());
        }
    }
}