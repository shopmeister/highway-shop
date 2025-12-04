<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;

class CreditNoteResolverDecorator implements OrderDocumentResolverInterface
{

    public function __construct(
        private readonly OrderDocumentResolverInterface $decoratedResolver,
        private readonly Logger        $logger
    )
    {
    }

    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($orderNumber, $documentType, &$templateData) {

            $order = $templateData['order'] ?? null;
            if ($order instanceof OrderEntity && $documentType == 'credit_note') {

                $lineItems = $order->getLineItems();
                $creditItems = new OrderLineItemCollection();

                if ($lineItems) {
                    $creditItems = $lineItems->filter(function (OrderLineItemEntity $lineItem) {
                        $type = $lineItem->getType();
                        return $type === LineItem::CREDIT_LINE_ITEM_TYPE || $type === "product-credit" || $type === "product-shipping" || $type === "promotion-credit" || $type === "shipping-credit";
                    });
                }

                if ($orderNumber && $creditItems->count() === 0) {
                    throw new \RuntimeException('Can not generate credit note document because no credit line items exists. Order Number: ' . $order->getOrderNumber());
                }
                $this->calculatePrice($creditItems, $order);
            }

        }, "Credit note template data resolution duration: %s ms");

        return $templateData;
    }

    private function calculatePrice(OrderLineItemCollection $creditItems, OrderEntity $order): CartPrice
    {
        foreach ($creditItems as $creditItem) {
            $creditItem->setUnitPrice($creditItem->getUnitPrice() !== 0.0 ? -$creditItem->getUnitPrice() : 0.0);
            $creditItem->setTotalPrice($creditItem->getTotalPrice() !== 0.0 ? -$creditItem->getTotalPrice() : 0.0);
            foreach ($creditItem->getPrice()->getCalculatedTaxes()->sortByTax()->getElements() as $tax) {
                $tax->setTax($tax->getTax() !== 0.0 ? -$tax->getTax() : 0.0);
            }
        }

        $creditItemsCalculatedPrice = $creditItems->getPrices()->sum();
        $totalPrice = $creditItemsCalculatedPrice->getTotalPrice();
        $taxAmount = $creditItemsCalculatedPrice->getCalculatedTaxes()->getAmount();
        $taxes = $creditItemsCalculatedPrice->getCalculatedTaxes();

        if ($order->getPrice()->hasNetPrices()) {
            $price = new CartPrice(
                -$totalPrice,
                (abs($totalPrice) + abs($taxAmount)),
                -$order->getPositionPrice(),
                $taxes,
                $creditItemsCalculatedPrice->getTaxRules(),
                $order->getTaxStatus()
            );
        } else {
            $price = new CartPrice(
                (abs($totalPrice) - $taxAmount),
                -$totalPrice,
                -$order->getPositionPrice(),
                $taxes,
                $creditItemsCalculatedPrice->getTaxRules(),
                $order->getTaxStatus()
            );
        }

        $order->setLineItems($creditItems);
        $order->setPrice($price);
        $order->setAmountNet($price->getNetPrice());
        $order->setAmountTotal($price->getTotalPrice());

        return $price;
    }

    public function getAssociations(string $type): array
    {
        return $this->decoratedResolver->getAssociations($type);
    }

    public function getOrderById($orderId, Context $context): ?OrderEntity
    {
        return $this->decoratedResolver->getOrderById($orderId, $context);
    }

    public function getPdfDocument(Context $context, $documentType, OrderEntity $order): ?DocumentEntity
    {
        return $this->decoratedResolver->getPdfDocument($context, $documentType, $order);
    }

    public function canResolveType(string $type): bool
    {
        return $this->decoratedResolver->canResolveType($type);
    }

    public function resolve(PreviewDataResolverContextInterface $context, string $type): array
    {
        return $this->decoratedResolver->resolve($context, $type);
    }

    public function getAdditionalDataTypes(string $type, Context $context): array
    {
        return $this->decoratedResolver->getAdditionalDataTypes($type, $context);
    }

    public function getAvailableEntities(): array
    {
        return $this->decoratedResolver->getAvailableEntities();
    }

}
