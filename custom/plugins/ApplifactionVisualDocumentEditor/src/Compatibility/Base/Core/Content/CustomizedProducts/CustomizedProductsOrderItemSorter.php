<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\CustomizedProducts;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class CustomizedProductsOrderItemSorter
{

    private const CUSTOMIZED_PRODUCTS_CHECKBOX_NAME = 'checkbox';
    private const CUSTOMIZED_PRODUCTS_DATETIME_NAME = 'datetime';
    private const CUSTOMIZED_PRODUCTS_FILEUPLOAD_NAME = 'fileupload';
    private const CUSTOMIZED_PRODUCTS_IMAGEUPLOAD_NAME = 'imageupload';
    private const CUSTOMIZED_PRODUCTS_TIMESTAMP_NAME = 'timestamp';
    private const CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE = 'customized-products';
    private const CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE = 'customized-products-option';
    private const CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE = 'option-values';

    public function sortOrderItems(OrderEntity $order)
    {
        if ($order === null || !$order instanceof OrderEntity) {
            return;
        }

        $orderLineItemCollection = $order->getLineItems();
        if ($orderLineItemCollection === null) {
            return;
        }
        $orderLineItemCollection->sortByPosition();

        $customizedProductOptionValueLineItems = $orderLineItemCollection->filterByType(
            self::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $this->adjustQuantityAndRemoveProductNumber($customizedProductOptionValueLineItems);
        $templateLineItems = $orderLineItemCollection->filterByType(
            self::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        foreach ($templateLineItems as $templateLineItem) {
            $childLineItems = $orderLineItemCollection->filterByProperty('parentId', $templateLineItem->getId());
            $productLineItems = $childLineItems->filterByType(LineItem::PRODUCT_LINE_ITEM_TYPE);
            $customizedProductOptionLineItems = $childLineItems->filterByType(
                self::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
            );

            $this->adjustQuantityAndRemoveProductNumber($customizedProductOptionLineItems);
            $this->addCustomerValueToChildLabel($customizedProductOptionLineItems, $customizedProductOptionValueLineItems);

            foreach ($productLineItems as $productLineItem) {
                $productLineItem->assign(['parentId' => null]);

                foreach ($customizedProductOptionLineItems as $child) {
                    $child->setParentId($productLineItem->getId());
                }

                $productLineItem->setChildren($customizedProductOptionLineItems);
                $productLineItem->setPosition($templateLineItem->getPosition());
            }
        }

        // removes all customized products entries except the product
        $orderLineItemCollection = $orderLineItemCollection->filter(static function (OrderLineItemEntity $lineItem) {
            return $lineItem->getType() !== self::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
                && $lineItem->getType() !== self::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
                && $lineItem->getType() !== self::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE;
        });
        $orderLineItemCollection->sortByPosition();

        // reinsert options after its parent product
        $order->setLineItems($this->flattenOrderLineItemCollection($orderLineItemCollection));
    }

    private function addCustomerValueToChildLabel(OrderLineItemCollection $optionCollection, OrderLineItemCollection $optionValueCollection): void
    {
        foreach ($optionCollection as $option) {
            $option->setLabel(\sprintf('* %s', $option->getLabel()));

            /** @var OrderLineItemCollection $childLineItems */
            $childLineItems = $optionValueCollection->filterByProperty('parentId', $option->getId());
            if ($childLineItems->count() > 0) {
                foreach ($childLineItems as $childLineItem) {
                    $childLineItem->setLabel(\sprintf('* * %s', $childLineItem->getLabel()));
                }

                $option->setChildren($childLineItems);

                continue;
            }

            if ($value = $this->extractValueFromPayload($option)) {
                $option->setLabel(\sprintf('%s: %s', $option->getLabel(), $value));
            }
        }
    }

    private function extractValueFromPayload(OrderLineItemEntity $option): ?string
    {
        $payload = $option->getPayload() ?? [];

        $type = $payload['type'] ?? null;
        $value = $payload['value'] ?? '';
        if (!$type) {
            return null;
        }

        if ($type === self::CUSTOMIZED_PRODUCTS_CHECKBOX_NAME) {
            return null;
        }

        if ($type === self::CUSTOMIZED_PRODUCTS_DATETIME_NAME) {
            return $value ? \date('d.m.Y', \strtotime($value)) : null;
        }

        if ($type === self::CUSTOMIZED_PRODUCTS_TIMESTAMP_NAME) {
            return $value ? \date('H:i', \strtotime($value)) : null;
        }

        if ($type === self::CUSTOMIZED_PRODUCTS_IMAGEUPLOAD_NAME || $type === self::CUSTOMIZED_PRODUCTS_FILEUPLOAD_NAME) {
            return \implode(', ', \array_column($option->getPayload()['media'] ?? [], 'filename'));
        }

        if (\strlen($value) > 50) {
            return \substr($value, 0, 45) . '[...]';
        }

        return $value;
    }

    private function adjustQuantityAndRemoveProductNumber(
        OrderLineItemCollection $orderLineItemCollection
    ): void {
        foreach ($orderLineItemCollection as $lineItem) {
            $payload = $lineItem->getPayload();
            $isOneTimeSurcharge = \is_array($payload) && isset($payload['isOneTimeSurcharge']) && $payload['isOneTimeSurcharge'] === true;

            if ($lineItem->getPriceDefinition() instanceof PercentagePriceDefinition || $isOneTimeSurcharge) {
                $lineItem->setQuantity(1);
            }

            if (isset($payload['productNumber']) && $payload['productNumber'] === '*') {
                unset($payload['productNumber']);
                $lineItem->setPayload($payload);
            }
        }
    }

    private function flattenOrderLineItemCollection(OrderLineItemCollection $orderLineItemCollection): OrderLineItemCollection
    {
        $newOrderLineItemCollection = new OrderLineItemCollection();

        foreach ($orderLineItemCollection->getElements() as $item) {
            $newOrderLineItemCollection->add($item);
            $children = $item->getChildren();
            if ($children !== null) {
                $newOrderLineItemCollection->merge($this->flattenOrderLineItemCollection($children));
            }
        }

        return $newOrderLineItemCollection;
    }
}
