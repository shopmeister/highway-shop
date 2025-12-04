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
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;

class ManufacturerResolverDecorator implements OrderDocumentResolverInterface
{

    public function __construct(
        private readonly OrderDocumentResolverInterface $decoratedResolver,
        private readonly EntityRepository               $manufacturerRepository,
        private readonly Logger        $logger
    )
    {
    }

    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($context, &$templateData) {

            $order = $templateData['order'] ?? null;
            if ($order instanceof OrderEntity) {
                $this->addProductManufacturerToOrder($order, $context);
            }

        }, "Manufacturer data resolution duration: %s ms");

        return $templateData;
    }


    private function addProductManufacturerToOrder(OrderEntity $order, Context $context): void
    {

        // Collect manufacturer IDs
        $manufacturerIds = [];
        /** @var OrderLineItemEntity $lineItem */
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getProduct() && $lineItem->getProduct()->getManufacturerId()) {
                $manufacturerIds[] = $lineItem->getProduct()->getManufacturerId();
            } else if ($lineItem->getProduct() && $lineItem->getProduct()->getParent() && $lineItem->getProduct()->getParent()->getManufacturerId()) {
                $manufacturerIds[] = $lineItem->getProduct()->getParent()->getManufacturerId();
            }
        }
        $manufacturerIds = array_unique($manufacturerIds);

        if (sizeof($manufacturerIds) > 0) {

            // Get manufacturer entities
            $criteria = new Criteria($manufacturerIds);
            $manufacturers = $this->manufacturerRepository->search($criteria, $context);

            // Assign a manufacturer to products
            /** @var OrderLineItemEntity $lineItem */
            foreach ($order->getLineItems() as $lineItem) {
                if ($lineItem->getProduct() && $lineItem->getProduct()->getManufacturerId()) {
                    $lineItem->getProduct()->setManufacturer($manufacturers->get($lineItem->getProduct()->getManufacturerId()));
                } else if ($lineItem->getProduct() && $lineItem->getProduct()->getParent() && $lineItem->getProduct()->getParent()->getManufacturerId()) {
                    $lineItem->getProduct()->setManufacturer($manufacturers->get($lineItem->getProduct()->getParent()->getManufacturerId()));
                }
            }

        }

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
