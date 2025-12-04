<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\PickwareErpStarter;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\TestDataProvider\SupplierOrderDataProvider;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class SupplierOrderResolverDecorator implements OrderDocumentResolverInterface
{
    const PICKWARE_ERP_SUPPLIER_ORDER = 'pickware_erp_supplier_order';

    /**
     * @var OrderDocumentResolverInterface
     */
    private OrderDocumentResolverInterface $decoratedResolver;

    private SupplierOrderDataProvider $supplierOrderDataProvider;

    public function __construct(
        OrderDocumentResolverInterface    $decoratedResolver,
        SupplierOrderDataProvider         $supplierOrderDataProvider
    )
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->supplierOrderDataProvider = $supplierOrderDataProvider;
    }

    /**
     * @param Context $context
     * @param null $orderNumber
     * @param null $documentType
     * @return array
     */
    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        if ($documentType === self::PICKWARE_ERP_SUPPLIER_ORDER) {
            return $this->supplierOrderDataProvider->getTestData();
        }
        return $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);
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
