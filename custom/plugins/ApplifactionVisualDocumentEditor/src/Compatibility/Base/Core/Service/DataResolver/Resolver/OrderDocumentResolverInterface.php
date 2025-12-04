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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

interface OrderDocumentResolverInterface
{

    public function getAssociations(string $type): array;

    public function getOrderById($orderId, Context $context): ?OrderEntity;

    public function getPdfDocument(Context $context, $documentType, OrderEntity $order): ?DocumentEntity;

    public function canResolveType(string $type): bool;

    public function resolve(PreviewDataResolverContextInterface $context, string $type): array;

    public function getAdditionalDataTypes(string $type, Context $context): array;

    public function getAvailableEntities(): array;

    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array;
    
}
