<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Export\Product\Listing;

use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Product\ProductCustomFields;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Export\Product\Listing\ExportListingByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ExportListingService
{
    public function __construct(
        private ConfigService $configService,
        private ApiZalandoProductService $apiZalandoProductService,
        private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        private EntityRepository $productRepository,
        private LoggerInterface $logger
    ) {
    }

    public function exportByPsr(ExportListingByPsrMessage $message): void
    {
        $psrProductCollection = $message->getPsr();
        if ($psrProductCollection->count() === 0) {
            $this->logger->info('No PSR products to process for listing');
            return;
        }

        $this->logger->info('Starting listing export for PSR products', [
            'productCount' => $psrProductCollection->count(),
            'isDryRun' => $message->isDryRun()
        ]);

        // Get active sales channels
        $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection();

        if ($message->getSpecificSalesChannelId()) {
            $salesChannelCollection = $salesChannelCollection->filter(function ($channel) use ($message) {
                return $channel->getSalesChannelId() === $message->getSpecificSalesChannelId();
            });
        } else {
            $salesChannelCollection = $salesChannelCollection->getActive();
        }

        if ($salesChannelCollection->count() === 0) {
            $this->logger->warning('No active sales channels found for listing export');
            return;
        }

        $processedCount = 0;
        $listedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($psrProductCollection as $psrProduct) {
            try {
                $result = $this->processProduct($psrProduct, $salesChannelCollection, $message->isDryRun());
                
                $processedCount++;
                
                if ($result['listed']) {
                    $listedCount++;
                } elseif ($result['skipped']) {
                    $skippedCount++;
                }

                if ($result['errors'] > 0) {
                    $errorCount += $result['errors'];
                }

            } catch (\Exception $e) {
                $this->logger->error('Error processing product for listing', [
                    'productEan' => $psrProduct->getEan(),
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
        }

        $this->logger->info('Listing export completed', [
            'processedCount' => $processedCount,
            'listedCount' => $listedCount,
            'skippedCount' => $skippedCount,
            'errorCount' => $errorCount,
            'isDryRun' => $message->isDryRun()
        ]);
    }

    private function processProduct(PsrProductStruct $psrProduct, $salesChannelCollection, bool $isDryRun): array
    {
        $result = [
            'listed' => false,
            'skipped' => false,
            'errors' => 0
        ];

        $product = $psrProduct->getProduct();
        if (!$product) {
            $this->logger->debug('Skipping PSR product - no Shopware product found', [
                'ean' => $psrProduct->getEan()
            ]);
            $result['skipped'] = true;
            return $result;
        }

        // Check if product is excluded from Zalando
        if ($this->isProductExcludedFromZalando($product)) {
            $this->logger->debug('Skipping product - excluded from Zalando', [
                'productId' => $product->getId(),
                'ean' => $product->getProductNumber()
            ]);
            $result['skipped'] = true;
            return $result;
        }

        // Check if product is active (with parent inheritance)
        if (!$this->isProductActive($product)) {
            $this->logger->debug('Skipping product - not active', [
                'productId' => $product->getId(),
                'ean' => $product->getProductNumber()
            ]);
            $result['skipped'] = true;
            return $result;
        }

        // Check if product has stock
        if (!$this->hasProductStock($product)) {
            $this->logger->debug('Skipping product - no stock', [
                'productId' => $product->getId(),
                'ean' => $product->getProductNumber()
            ]);
            $result['skipped'] = true;
            return $result;
        }

        foreach ($salesChannelCollection as $salesChannel) {
            // Check if listing is active for this sales channel
            $config = $this->configService->getFormBySalesChannelAndType($salesChannel->getSalesChannelId(), 'listing');
            if (!$config || !($config['activeListing'] ?? false)) {
                continue;
            }

            try {
                // Check if product is already listed in PSR for this sales channel
                if ($psrProduct->getOffers()) {
                    $countryCode = strtoupper($salesChannel->getCountryCode());
                    $existingOffer = $psrProduct->getOffers()->get($countryCode);
                    
                    if ($existingOffer) {
                        $this->logger->debug('Product already listed in PSR - skipping', [
                            'ean' => $product->getProductNumber(),
                            'salesChannel' => $salesChannel->getCountryCode() . ' - ' . $salesChannel->getCountryName(),
                            'psrFound' => true
                        ]);
                        continue;
                    }
                }

                $merchantId = $this->configService->getZalandoApiConfig()->getMerchantId();
                
                // Check if product exists at Zalando
                $checkResponse = $this->apiZalandoProductService->checkProductExistsByEan(
                    $product->getProductNumber(), 
                    $merchantId
                );

                $contentArray = $checkResponse->getContentArray();
                $productExists = $checkResponse->isSuccessStatus() && !empty($contentArray['items'] ?? []);

                if (!$productExists) {
                    $this->logger->debug('Product does not exist at Zalando', [
                        'ean' => $product->getProductNumber(),
                        'salesChannel' => $salesChannel->getCountryCode() . ' - ' . $salesChannel->getCountryName(),
                        'status' => $checkResponse->getStatus()
                    ]);
                    continue;
                }

                // Product exists, prepare listing payload
                $payload = $this->prepareListingPayload($product, $salesChannel);

                if ($isDryRun) {
                    $this->logger->info('DRY-RUN: Would list product at Zalando', [
                        'ean' => $product->getProductNumber(),
                        'salesChannel' => $salesChannel->getCountryCode() . ' - ' . $salesChannel->getCountryName(),
                        'payload' => $payload
                    ]);
                } else {
                    // Send listing request
                    $listingResponse = $this->apiZalandoProductService->onboardExistingProduct(
                        $payload, 
                        $merchantId, 
                        $product->getProductNumber()
                    );
                    
                    // REST API returns 204 No Content on success
                    if ($listingResponse->getStatus() === 204) {
                        $this->logger->info('Product successfully listed at Zalando', [
                            'ean' => $product->getProductNumber(),
                            'salesChannel' => $salesChannel->getCountryCode() . ' - ' . $salesChannel->getCountryName()
                        ]);
                        $result['listed'] = true;
                    } else {
                        $this->logger->error('Failed to list product at Zalando', [
                            'ean' => $product->getProductNumber(),
                            'salesChannel' => $salesChannel->getCountryCode() . ' - ' . $salesChannel->getCountryName(),
                            'response' => $listingResponse->getContentArray()
                        ]);
                        $result['errors']++;
                    }
                }

            } catch (\Exception $e) {
                $this->logger->error('Exception while processing product for sales channel', [
                    'ean' => $product->getProductNumber(),
                    'salesChannel' => $salesChannel->getCountryCode() . ' - ' . $salesChannel->getCountryName(),
                    'error' => $e->getMessage()
                ]);
                $result['errors']++;
            }
        }

        return $result;
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

        // REST API payload structure - minimum required field
        return [
            'merchant_product_simple_id' => $merchantProductSimpleId,
            'merchant_product_config_id' => $merchantProductConfigId,
            'merchant_product_model_id' => $merchantProductModelId
        ];
    }
}