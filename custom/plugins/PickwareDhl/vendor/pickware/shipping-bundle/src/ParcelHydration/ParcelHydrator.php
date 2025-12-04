<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\ShippingBundle\ParcelHydration;

use InvalidArgumentException;
use LogicException;
use Pickware\DalBundle\ContextFactory;
use Pickware\DalBundle\EntityManager;
use Pickware\MoneyBundle\Currency;
use Pickware\MoneyBundle\MoneyValue;
use Pickware\ShippingBundle\Notifications\NotificationService;
use Pickware\ShippingBundle\Parcel\Parcel;
use Pickware\ShippingBundle\Parcel\ParcelItem;
use Pickware\ShippingBundle\Shipment\Country;
use Pickware\UnitsOfMeasurement\Dimensions\BoxDimensions;
use Pickware\UnitsOfMeasurement\PhysicalQuantity\Length;
use Pickware\UnitsOfMeasurement\PhysicalQuantity\Weight;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\State;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyDefinition;

class ParcelHydrator
{
    private const ORDER_LINE_ITEMS_TYPES_TO_DISTRIBUTE = [
        LineItem::PROMOTION_LINE_ITEM_TYPE,
        LineItem::DISCOUNT_LINE_ITEM,
        LineItem::CUSTOM_LINE_ITEM_TYPE,
    ];

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly ContextFactory $contextFactory,
        private readonly NotificationService $notificationService,
    ) {}

    public function hydrateParcelFromOrder(
        string $orderId,
        ?array $productsInParcel,
        Context $context,
    ): Parcel {
        // Consider inheritance when fetching products for inherited fields (e.g. name, weight)
        $orderContext = $this->contextFactory->deriveOrderContext($orderId, $context);
        $orderContext->setConsiderInheritance(true);
        /** @var OrderEntity $order */
        $order = $this->entityManager->findByPrimaryKey(
            OrderDefinition::class,
            $orderId,
            $orderContext,
            [
                'currency',
                'documents.documentType',
                'lineItems.product',
            ],
        );

        $parcel = new Parcel();
        $parcel->setCustomerReference($order->getOrderNumber());
        $calculatedUnitPricesByOrderLineItemIds = $this->calculateProductLineItemUnitPrices($order->getLineItems());

        $lineItemsInParcel = self::getSupportedOrderLineItemsInParcel($order, $productsInParcel);
        foreach ($lineItemsInParcel as $orderLineItem) {
            $parcel->addItem($this->createParcelItemFromLineItem(
                order: $order,
                orderLineItem: $orderLineItem,
                calculatedPrice: $calculatedUnitPricesByOrderLineItemIds[$orderLineItem->getId()],
                context: $context,
            ));
        }

        return $parcel;
    }

    /**
     * @return OrderLineItemCollection order line items mapped from the products in parcel. If no specific products in
     * parcel are given, all line items are returned. If a product in a parcel is provided which is not part of the
     * order, it is ignored.
     */
    private static function getSupportedOrderLineItemsInParcel(
        OrderEntity $order,
        ?array $productsInParcel,
    ): OrderLineItemCollection {
        $supportedLineItems = $order->getLineItems()->filter(
            fn(OrderLineItemEntity $lineItem) => self::isSupportedOrderLineItem($lineItem, supportDigitalProducts: false),
        );

        if ($productsInParcel === null) {
            return $supportedLineItems;
        }

        $groupedProductsInParcel = [];
        foreach ($productsInParcel as $product) {
            $productId = $product['productId'];
            $groupedProductsInParcel[$productId] ??= 0;
            $groupedProductsInParcel[$productId] += $product['quantity'];
        }

        $lineItemsInParcel = [];
        foreach ($supportedLineItems as $lineItem) {
            $quantityInParcel = $groupedProductsInParcel[$lineItem->getProductId()] ?? null;
            if ($quantityInParcel === null || $quantityInParcel <= 0) {
                continue;
            }
            $lineItemQuantity = min($quantityInParcel, $lineItem->getQuantity());
            $lineItem->setQuantity($lineItemQuantity);
            $groupedProductsInParcel[$lineItem->getProductId()] -= $lineItemQuantity;

            $lineItemsInParcel[] = $lineItem;
        }

        return new OrderLineItemCollection($lineItemsInParcel);
    }

    private function createParcelItemFromLineItem(
        OrderEntity $order,
        OrderLineItemEntity $orderLineItem,
        float $calculatedPrice,
        Context $context,
    ): ParcelItem {
        $parcelItem = new ParcelItem($orderLineItem->getQuantity());

        $product = $orderLineItem->getProduct();
        if (!$product) {
            // If the product does not exist (i.e. has been deleted) only use the label of the order line item
            $parcelItem->setName($orderLineItem->getLabel());
            $parcelItem->setCustomsDescription($orderLineItem->getLabel());
            $this->notificationService->emit(ParcelHydrationNotification::productWasDeleted(
                $order,
                $orderLineItem->getLabel(),
            ));

            return $parcelItem;
        }

        $productName = $product->getName() ?: $product->getTranslation('name');
        $parcelItem->setName($productName);
        $parcelItem->setUnitWeight($product->getWeight() ? new Weight($product->getWeight(), 'kg') : null);
        $parcelItem->setProductNumber($product->getProductNumber());
        if (
            ($product->getWidth() ?? 0.0) > 0.0
            && ($product->getHeight() ?? 0.0) > 0.0
            && ($product->getLength() ?? 0.0) > 0.0
        ) {
            $parcelItem->setUnitDimensions(new BoxDimensions(
                new Length($product->getWidth(), 'mm'),
                new Length($product->getHeight(), 'mm'),
                new Length($product->getLength(), 'mm'),
            ));
        }

        $customFields = $product->getTranslation('customFields');

        $description = $customFields[CustomsInformationCustomFieldSet::CUSTOM_FIELD_NAME_CUSTOMS_INFORMATION_DESCRIPTION] ?? '';
        if (!$description) {
            // If no explicit description for this product was provided, use the product name as fallback
            $description = $productName;
        }

        $parcelItem->setCustomsDescription($description);
        $parcelItem->setTariffNumber(
            $customFields[CustomsInformationCustomFieldSet::CUSTOM_FIELD_NAME_CUSTOMS_INFORMATION_TARIFF_NUMBER] ?? null,
        );

        $currency = $order->getCurrency();
        // CustomsValue Fallback cases
        // 1. LineItemPrice if price is not zero (>= 0.001, We use this value because the currency with the most decimal
        //   places has a maximum of 3, and this way, we efficiently avoid floating point inaccuracies.
        // 2. FallbackCustomsValue if LineItemPrice = 0
        // 3. ProductPrice Net if LineItemPrice and FallbackCustomsValue = 0 and TaxStatus is tax-free
        // 4. ProductPrice Gross if LineItemPrice and FallbackCustomsValue = 0 and TaxStatus is not tax-free
        if ($calculatedPrice >= 0.001) {
            $customsValue = $calculatedPrice;
        } elseif (($customFields[CustomsInformationCustomFieldSet::CUSTOM_FIELD_NAME_CUSTOMS_INFORMATION_CUSTOMS_VALUE] ?? 0) > 0) {
            $currency = $this->entityManager->getByPrimaryKey(
                CurrencyDefinition::class,
                Defaults::CURRENCY,
                $context,
            );

            $customsValue = $customFields[CustomsInformationCustomFieldSet::CUSTOM_FIELD_NAME_CUSTOMS_INFORMATION_CUSTOMS_VALUE] ?? 0;
        } elseif ($order->getTaxStatus() === CartPrice::TAX_STATE_FREE) {
            $customsValue = $orderLineItem->getProduct()->getPrice()?->getCurrencyPrice($currency->getId(), false)?->getNet();
        } else {
            $customsValue = $orderLineItem->getProduct()->getPrice()?->getCurrencyPrice($currency->getId(), false)?->getGross();
        }

        $parcelItem->setUnitPrice(new MoneyValue(
            $customsValue ?? 0,
            new Currency($currency->getIsoCode()),
        ));

        try {
            if ($customFields[CustomsInformationCustomFieldSet::CUSTOM_FIELD_NAME_CUSTOMS_INFORMATION_COUNTRY_OF_ORIGIN] ?? null) {
                $country = new Country(
                    $customFields[CustomsInformationCustomFieldSet::CUSTOM_FIELD_NAME_CUSTOMS_INFORMATION_COUNTRY_OF_ORIGIN],
                );
            } else {
                $country = null;
            }
            $parcelItem->setCountryOfOrigin($country);
        } catch (InvalidArgumentException $exception) {
            // Since the customs information are optional, we can ignore this error, add a notification and continue
            // with the remaining parcel items (without throwing an Exception).
            $this->notificationService->emit(
                ParcelHydrationNotification::parcelItemCustomsInformationInvalid(
                    $orderLineItem->getLabel(),
                    $exception,
                ),
            );
        }

        return $parcelItem;
    }

    /**
     * Calculates the prices for product line items. Discounts, promotions and custom line items are distributed
     * proportionally among all product line items.
     * @return array<string, float> The calculated unit prices for each order line item ID
     */
    private function calculateProductLineItemUnitPrices(OrderLineItemCollection $orderLineItems): array
    {
        $totalValueOfLineItemsReceivingDistribution = 0.0;
        $totalValueToDistribute = 0.0;
        /** @var OrderLineItemEntity $orderLineItem */
        foreach ($orderLineItems as $orderLineItem) {
            if (in_array($orderLineItem->getType(), self::ORDER_LINE_ITEMS_TYPES_TO_DISTRIBUTE, strict: true)) {
                $totalValueToDistribute += $orderLineItem->getPrice()->getTotalPrice();
            }

            // We explicitly want digital products to be included for distribution of e.g. discounts even though they
            // are not contained in the parcel
            if (self::isSupportedOrderLineItem($orderLineItem, supportDigitalProducts: true)) {
                $totalValueOfLineItemsReceivingDistribution += $orderLineItem->getPrice()->getTotalPrice();
            }
        }

        $calculatedPricesByLineItemId = [];
        foreach ($orderLineItems as $orderLineItem) {
            if (!self::isSupportedOrderLineItem($orderLineItem, supportDigitalProducts: false)) {
                continue;
            }

            if ($orderLineItem->getPrice()->getQuantity() === 0) {
                throw new LogicException(sprintf(
                    'Unexpected line item with quantity 0 found: %s',
                    $orderLineItem->getLabel(),
                ));
            }

            if ($totalValueOfLineItemsReceivingDistribution === 0.0) {
                $calculatedPricesByLineItemId[$orderLineItem->getId()] = $orderLineItem->getPrice()->getUnitPrice();

                continue;
            }

            $unitPriceChangeOfLineItem = (($orderLineItem->getPrice()->getTotalPrice() / $totalValueOfLineItemsReceivingDistribution) * $totalValueToDistribute) / $orderLineItem->getPrice()->getQuantity();
            $calculatedPricesByLineItemId[$orderLineItem->getId()] = $orderLineItem->getPrice()->getUnitPrice() + $unitPriceChangeOfLineItem;
        }

        return $calculatedPricesByLineItemId;
    }

    private static function isSupportedOrderLineItem(
        OrderLineItemEntity $orderLineItem,
        bool $supportDigitalProducts,
    ): bool {
        return
            $orderLineItem->getQuantity() > 0
            && $orderLineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE
            && ($supportDigitalProducts || !in_array(State::IS_DOWNLOAD, $orderLineItem->getStates(), true));
    }
}
