<?php

namespace ShopmasterZalandoConnectorSix\Services\Export\Product\Price;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPriceService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormPriceSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\OffersPriceCollection;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\OffersPriceStruct;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\PriceStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ExportPriceService
{
    /**
     * @var ConfigService
     */
    private ConfigService $configService;
    /**
     * @var ApiZalandoSalesChannelsService
     */
    private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService;
    /**
     * @var ApiZalandoProductPriceService
     */
    private ApiZalandoProductPriceService $apiZalandoProductPriceService;
    
    /**
     * @var EntityRepository
     */
    private EntityRepository $productRepository;

    /**
     * @param ConfigService $configService
     * @param ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService
     * @param ApiZalandoProductPriceService $apiZalandoProductPriceService
     * @param EntityRepository $productRepository
     */
    public function __construct(
        ConfigService                  $configService,
        ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        ApiZalandoProductPriceService  $apiZalandoProductPriceService,
        EntityRepository               $productRepository
    )
    {
        $this->configService = $configService;
        $this->apiZalandoSalesChannelsService = $apiZalandoSalesChannelsService;
        $this->apiZalandoProductPriceService = $apiZalandoProductPriceService;
        $this->productRepository = $productRepository;
    }

    /**
     * @param PsrProductCollection $psr
     * @return ResponseStruct|null
     * @throws MethodNameExceptions
     */
    public function runExportProcessByPsr(PsrProductCollection $psr): ?ResponseStruct
    {
        $collection = $this->getOffersPriceCollection($psr);
        if (!$collection->count()) {
            return null;
        }
        return $this->apiZalandoProductPriceService->updateZalandoPrice($collection);
    }

    /**
     * @param PsrProductCollection $psr
     * @return OffersPriceCollection
     */
    private function getOffersPriceCollection(PsrProductCollection $psr): OffersPriceCollection
    {
        $collection = new OffersPriceCollection();

        /** @var PsrProductStruct $psrProductStruct */
        foreach ($psr as $psrProductStruct) {
            /** @var ProductEntity $product */
            $product = $psrProductStruct->getProduct();
            
            // Skip inactive products
            if (!$this->isProductActive($product)) {
                continue;
            }
            
            // Get only active sales channels
            $activeSalesChannels = $this->apiZalandoSalesChannelsService->getCollection()->getActive();
            
            /** @var SalesChannelStruct $salesChannel */
            foreach ($activeSalesChannels as $salesChannel) {
                if (!$this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId())->isActive()) {
                    continue;
                }
                /** @var PsrProductOfferStruct $psrItem */
                $psrProductOfferStruct = $psrProductStruct->getOffers()->get(strtoupper($salesChannel->getCountryCode()));
                
                // Pass PSR offer data to getOffersPriceStruct to handle promotional price logic
                $struct = $this->getOffersPriceStruct($product, $salesChannel, $psrProductOfferStruct);
                if (!$struct) {
                    continue;
                }
                
                // Check if price update is needed
                if (!$psrProductOfferStruct) {
                    // No PSR entry exists - send price
                    $collection->add($struct);
                } elseif ($psrProductOfferStruct->getRegularPrice() !== $struct->getRegularPrice()->getAmount()
                    || ($struct->getPromotionalPrice() && $psrProductOfferStruct->getDiscountedPrice() !== $struct->getPromotionalPrice()->getAmount())) {
                    $collection->add($struct);
                }
            }
        }
        return $collection;
    }

    /**
     * @param ProductEntity $product
     * @param SalesChannelStruct $salesChannel
     * @param PsrProductOfferStruct|null $psrOffer Current PSR offer data for comparison
     * @return OffersPriceStruct|null
     */
    private function getOffersPriceStruct(ProductEntity $product, SalesChannelStruct $salesChannel, ?PsrProductOfferStruct $psrOffer = null): ?OffersPriceStruct
    {
        $config = $this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId());
        $price = $this->getProductPriceByConfig($product, $config);
        if (!$price) {
            return null;
        }
        $struct = new OffersPriceStruct();
        $struct->setEan($product->getEan())
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

    /**
     * Get product price by configuration with fallback logic:
     * 1. Try to get price from product (advanced price with rule ID or standard price)
     * 2. If variant has no price, fallback to parent product:
     *    a. Try parent's advanced price with same rule ID
     *    b. If no advanced price found, fallback to parent's standard price
     * 
     * @param ProductEntity $product
     * @param SettingFormPriceSyncStruct $config
     * @return Price|null
     */
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
    
    /**
     * Get parent product for variant
     */
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

}