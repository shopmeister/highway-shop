<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\PickwareErpStarter;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Pickware\PickwareErpStarter\Picklist\PicklistCustomProductGenerator;
use Pickware\PickwareErpStarter\Picklist\PicklistGenerator;
use Pickware\PickwareErpStarter\Picklist\Renderer\PicklistDocumentContentGenerator;
use Pickware\PickwareErpStarter\Warehouse\Model\WarehouseEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class PicklistResolverDecorator implements OrderDocumentResolverInterface
{
    const PICKWARE_ERP_PICKLIST = 'pickware_erp_picklist';

    public function __construct(
        private readonly OrderDocumentResolverInterface    $decoratedResolver,
        private readonly Logger           $logger,
        private readonly ?EntityRepository                 $warehouseRepository,
        private readonly ?PicklistGenerator                $picklistGenerator,
        private readonly ?PicklistCustomProductGenerator   $picklistCustomProductGenerator,
        private readonly ?PicklistDocumentContentGenerator $contentGenerator
    )
    {
    }

    /*
     * This method is only used when generating previews inside the editor.
     * The PROD documents work by default because they are based on an order and following class is used to render them (which we decorated):
     * @see \Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer
     */
    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($documentType, $context, &$templateData) {

            if ($documentType === self::PICKWARE_ERP_PICKLIST && !!$this->warehouseRepository && !!$this->picklistGenerator && !!$this->picklistCustomProductGenerator && !!$this->contentGenerator) {

                $order = $templateData['order'];
                $config = $templateData['config'];

                $warehouse = $this->getFirstCreatedWarehouse($context);
                $warehouseId = !!$warehouse ? $warehouse->getId() : null;

                $config = array_merge($config, [
                    'documentNumber' => "EXAMPLE-123",
                    'orderNumber' => $order->getOrderNumber(),
                    'warehouseId' => $warehouseId,
                    'documentDate' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'documentComment' => '',
                ]);

                $picklistTemplateData = [
                    'warehouse' => $warehouse,
                    'pickingRouteNodes' => $this->contentGenerator->createDocumentPickingRouteNodes(
                        $this->picklistGenerator->generatePicklist($order->getId(), $warehouseId, $context),
                        $order->getLineItems()->getIds(),
                        $context
                    ),
                    'customProducts' => $this->picklistCustomProductGenerator->generatorCustomProductDefinitions(
                        $order->getLineItems(),
                    ),
                    'config' => $config
                ];

                $templateData = array_merge($templateData, $picklistTemplateData);

            }

        }, "Picklist data resolution duration: %s ms");

        return $templateData;
    }

    private function getFirstCreatedWarehouse(Context $context): ?WarehouseEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));

        /** @var WarehouseEntity $warehouse */
        $warehouse = $this->warehouseRepository->search($criteria, $context)->getEntities()->first();
        return $warehouse;
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
