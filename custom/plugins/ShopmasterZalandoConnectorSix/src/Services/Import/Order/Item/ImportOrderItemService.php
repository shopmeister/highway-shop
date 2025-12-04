<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Import\Order\Item;

use ShopmasterZalandoConnectorSix\Exception\Order\ExceptionImportOrder;
use ShopmasterZalandoConnectorSix\Services\Import\Order\Price\ImportOrderPriceService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Item\ImportOrderItemCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Item\ImportOrderItemStruct;
use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;

class ImportOrderItemService
{

    /**
     * @param EntityRepository $repositoryProduct
     * @param ImportOrderPriceService $importOrderPriceService
     */
    public function __construct(
        private readonly EntityRepository        $repositoryProduct,
        private readonly ImportOrderPriceService $importOrderPriceService
    )
    {
    }

    /**
     * @param OrderItemStruct $item
     * @param OrderStruct $order
     * @param Context $context
     * @return ImportOrderItemCollection
     * @throws ExceptionImportOrder
     */
    public function getOrderImportItemCollection(OrderItemStruct $item, OrderStruct $order, Context $context): ImportOrderItemCollection
    {
        $collection = new ImportOrderItemCollection();
        /** @var OrderLineStruct $orderLineStruct */
        foreach ($item->getOrderLines() as $orderLineStruct) {
            $struct = $this->getOrderImportItemStruct($orderLineStruct, $order, $context);
            $collection->set($struct->getId(), $struct);
        }
        return $collection;
    }

    /**
     * @param OrderLineStruct $lineItem
     * @param OrderStruct $order
     * @param Context $context
     * @return ImportOrderItemStruct
     * @throws ExceptionImportOrder
     */
    private function getOrderImportItemStruct(OrderLineStruct $lineItem, OrderStruct $order, Context $context): ImportOrderItemStruct
    {
        $product = $this->getProduct($lineItem->getOrderItemStruct(), $context);
        $payload = $this->getPayload($product);
        $price = $this->importOrderPriceService->calculateOrderItemCartPrice($lineItem, $order->getSalesChannelId(), $context);
        $priceDefinition = new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules());

        $struct = new ImportOrderItemStruct();
        $struct->setId(Struct::uuidToId($lineItem->getId()))
            ->setPayload($payload)
            ->setProductId($product->getId())
            ->setIdentifier($product->getId())
            ->setReferencedId($product->getId())
            ->setQuantity(1)
            ->setLabel($this->getLable($product))
            ->setPrice($price)
            ->setPriceDefinition($priceDefinition)
            ->setCustomFields([
                $struct::Z_ORDER_ITEM_ID => $lineItem->getOrderItemId(),
                $struct::Z_ORDER_LINE_ID => $lineItem->getId(),
            ]);
        return $struct;
    }

    /**
     * @param OrderItemStruct $item
     * @param Context $context
     * @return ProductEntity
     * @throws ExceptionImportOrder
     */
    private function getProduct(OrderItemStruct $item, Context $context): ProductEntity
    {
        return $this->getProductByExternalId($item->getExternalId(), $context);
    }

    /**
     * @param ProductEntity $product
     * @return array
     */
    private function getPayload(ProductEntity $product): array
    {
        return [
            'isCloseout' => $product->getIsCloseout(),
            'customFields' => $product->getCustomFields(),
            'createdAt' => $product->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'releaseDate' => $product->getReleaseDate()?->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'isNew' => false,
            'markAsTopseller' => $product->getMarkAsTopseller(),
            'purchasePrices' => null,
            'productNumber' => $product->getProductNumber(),
            'manufacturerId' => $product->getManufacturerId(),
            'taxId' => $product->getTaxId(),
            'tagIds' => $product->getTagIds(),
            'categoryIds' => $product->getCategoryTree(),
            'propertyIds' => $product->getPropertyIds(),
            'optionIds' => $product->getOptionIds(),
            'options' => $product->getVariation(),
        ];
    }

    /**
     * @param string $externalId
     * @param Context $context
     * @return ProductEntity|null
     * @throws ExceptionImportOrder
     */
    private function getProductByExternalId(string $externalId, Context $context): ?ProductEntity
    {
        //temporary hotfix - replace special numbers
        if ($externalId === 'BW00222017N') {
            $externalId = '4260639723411';
        }
        if ($externalId === 'BW00462017N') {
            $externalId = '4260639725293';
        }
        if ($externalId === 'BW00502017N') {
            $externalId = '4260639725330';
        }
        if ($externalId === '63-BW00032017N') {
            $externalId = '4260639720021';
        }

        //get numbers from the string to have the ean if there is no number found
        $number = preg_replace('/[^0-9]/', '', $externalId);

        $criteria = new Criteria();
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('ean', $number),
            new EqualsFilter('productNumber', $number)
        ]));

        $criteria->addAssociations([
            'options.group',
            'configuratorSettings',
        ]);
        /** @var ProductEntity $product */
        $product = $this->repositoryProduct->search($criteria, $context)->first();

        if (!$product) {
            throw new ExceptionImportOrder('Can not find product from ean,number - ' . $externalId, 10100);
        }

        if ($product->getParentId()) {
            $criteria = new Criteria([$product->getParentId()]);
            $parent = $this->repositoryProduct->search($criteria, $context)->first();
            $product->setParent($parent);
        }
        return $product;
    }

    private function getLable(ProductEntity $product): string
    {
        if ($product->getParent()) {
            $lable = $product->getParent()->getName() ?? $product->getParent()->getTranslation('name');
        } else {
            $lable = $product->getName() ?? $product->getTranslation('name');
        }
        if (!is_string($lable)) {
            $lable = $product->getEan() ?? $product->getId();
        }
        return $lable;
    }


}