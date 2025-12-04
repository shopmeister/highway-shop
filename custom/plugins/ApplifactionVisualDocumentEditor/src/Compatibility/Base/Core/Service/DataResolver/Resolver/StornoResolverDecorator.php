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
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class StornoResolverDecorator implements OrderDocumentResolverInterface
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

        $this->logger->logExecutionDuration(function () use ($documentType, &$templateData) {

            $order = $templateData['order'] ?? null;
            if ($order instanceof OrderEntity && $documentType === 'storno') {
                $this->handlePrices($order);
            }

        }, "Storno data resolution duration: %s ms");

        return $templateData;
    }

    protected function handlePrices(OrderEntity $order): OrderEntity
    {
        foreach ($order->getLineItems() ?? [] as $lineItem) {
            $lineItem->setUnitPrice($lineItem->getUnitPrice() / -1);
            $lineItem->setTotalPrice($lineItem->getTotalPrice() / -1);
            foreach ($lineItem->getPrice()->getCalculatedTaxes()->sortByTax()->getElements() as $tax) {
                $tax->setTax($tax->getTax() / -1);
            }
        }

        foreach ($order->getDeliveries() ?? [] as $delivery) {
            foreach ($delivery->getShippingCosts()->getCalculatedTaxes()->sortByTax()->getElements() as $tax) {
                $tax->setTax($tax->getTax() / -1);
            }
        }

        foreach ($order->getPrice()->getCalculatedTaxes()->sortByTax()->getElements() as $tax) {
            $tax->setTax($tax->getTax() / -1);
        }

        $order->setShippingTotal($order->getShippingTotal() / -1);
        $order->setAmountNet($order->getAmountNet() / -1);
        $order->setAmountTotal($order->getAmountTotal() / -1);
        $order->getPrice()->assign([
            'rawTotal' => $order->getPrice()->getRawTotal() / -1,
            'totalPrice' => $order->getPrice()->getTotalPrice() / -1,
        ]);

        return $order;
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
