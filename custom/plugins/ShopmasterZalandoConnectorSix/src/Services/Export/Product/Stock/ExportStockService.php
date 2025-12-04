<?php

namespace ShopmasterZalandoConnectorSix\Services\Export\Product\Stock;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Product\ProductCustomFields;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductStockService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\Stock\StockCollection;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\Stock\StockStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;

class ExportStockService
{
    private ConfigService $configService;
    private ApiZalandoProductStockService $apiZalandoProductStockService;
    private EntityRepository $productRepository;

    public function __construct(
        ConfigService                 $configService,
        ApiZalandoProductStockService $apiZalandoProductStockService,
        EntityRepository              $productRepository
    )
    {
        $this->configService = $configService;
        $this->apiZalandoProductStockService = $apiZalandoProductStockService;
        $this->productRepository = $productRepository;
    }

    /**
     * @param PsrProductCollection $psr
     * @return ResponseStruct|null
     */
    public function runExportProcessByPsr(PsrProductCollection $psr): ?ResponseStruct
    {
        $collection = new StockCollection();
        /** @var PsrProductStruct $psrProductStruct */
        foreach ($psr as $psrProductStruct) {
            $collection->merge($this->getStockCollectionByPsrProductStruct($psrProductStruct));
        }
        if (!$collection->count()) {
            return null;
        }
        return $this->apiZalandoProductStockService->updateZalandoStock($collection);
    }

    /**
     * @param PsrProductStruct $psrProductStruct
     * @return StockCollection
     */
    private function getStockCollectionByPsrProductStruct(PsrProductStruct $psrProductStruct): StockCollection
    {
        $collection = new StockCollection();
        $shopStock = $this->getStock($psrProductStruct);
        /** @var PsrProductOfferStruct $offer */
        foreach ($psrProductStruct->getOffers() as $offer) {
            if ($offer->getStock() !== $shopStock) {
                $collection->add($this->getStockStruct($shopStock, $offer, $psrProductStruct));
            }
        }
        return $collection;
    }

    /**
     * function use business logic
     * @param PsrProductStruct $psrProductStruct
     * @return int
     */
    private function getStock(PsrProductStruct $psrProductStruct): int
    {
        $config = $this->configService->getStockSyncConfig();
        if ($config->isHoliday()) {
            return 0;
        }

        $product = $psrProductStruct->getProduct();

        if (!$product || !$this->isProductActive($product)) {
            return 0;
        }
        if ($config->isIndividualStock()
            && !is_null($individualStock = $product->getCustomFields()[ProductCustomFields::CUSTOM_FIELD_INDIVIDUAL_STOCK] ?? null)
        ) {
            return ($individualStock > -1) ? $individualStock : 0;
        }

        $stock = $product->getStock();
        $stockCache = $config->getStockCache() ?? 0;
        
        return ($stock < $stockCache) ? 0 : ($stock - $stockCache);
    }

    /**
     * @param int $shopStock
     * @param PsrProductOfferStruct $offer
     * @param PsrProductStruct $psrProductStruct
     * @return StockStruct
     */
    private function getStockStruct(int $shopStock, PsrProductOfferStruct $offer, PsrProductStruct $psrProductStruct): StockStruct
    {
        $struct = new StockStruct();
        $salesChannelId = ConfigService::getSalesChannelIdByCountryCode(strtolower($offer->getCountryCode()));
        $struct->setSalesChannelId($salesChannelId)
            ->setEan($psrProductStruct->getEan())
            ->setQuantity($shopStock);
        return $struct;
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

}