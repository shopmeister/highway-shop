<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\PickwareErpStarter;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\PickwareErpStarter\PickwareErpStarterService;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\TranslatorService;
use Doctrine\DBAL\Exception;
use Pickware\PickwareErpStarter\InvoiceCorrection\Events\InvoiceCorrectionOrderEvent;
use Pickware\PickwareErpStarter\InvoiceCorrection\InvoiceCorrectionCalculator;
use Pickware\PickwareErpStarter\InvoiceCorrection\InvoiceCorrectionConfigGenerator;
use Pickware\PickwareErpStarter\InvoiceCorrection\InvoiceCorrectionDocumentRenderer;
use Pickware\PickwareErpStarter\InvoiceCorrection\InvoiceCorrectionException;
use Pickware\PickwareErpStarter\OrderCalculation\CalculatableOrder;
use Pickware\PickwareErpStarter\OrderCalculation\CalculatableOrderLineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InvoiceCorrectionResolverDecorator implements OrderDocumentResolverInterface
{

    public function __construct(
        private readonly OrderDocumentResolverInterface    $decoratedResolver,
        private readonly PickwareErpStarterService         $pickwareErpStarterService,
        private readonly TranslatorService                 $translatorService,
        private readonly Logger           $logger,
        private readonly ?InvoiceCorrectionConfigGenerator $invoiceCorrectionConfigGenerator,
        private readonly ?EventDispatcherInterface         $dispatcher,
        private readonly ?InvoiceCorrectionCalculator      $invoiceCorrectionCalculator
    )
    {
    }

    /**
     * @param Context $context
     * @param null $orderNumber
     * @param null $documentType
     * @return array
     * @throws Exception
     */
    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        if ($orderNumber === null && $this->isInvoiceCorrectionDocument($documentType)) {
            $orderNumber = $this->pickwareErpStarterService->fetchOrderNumberWithInvoiceCorrection();
        }
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($documentType, $context, &$templateData) {
            $templateData = $this->postProcessTemplateData($documentType, $templateData, $context);
        }, "Invoice correction data resolution duration: %s ms");

        return $templateData;
    }

    private function applyInvoiceCorrectionToOrder(OrderEntity $orderEntity, CalculatableOrder $invoiceCorrection): void
    {
        $orderEntity->setPrice($invoiceCorrection->price);
        $orderEntity->setAmountNet($invoiceCorrection->price->getNetPrice());
        $orderEntity->setAmountTotal($invoiceCorrection->price->getTotalPrice());
        $orderEntity->setPositionPrice($invoiceCorrection->price->getPositionPrice());
        $orderEntity->setTaxStatus($invoiceCorrection->price->getTaxStatus());
        $orderEntity->setShippingTotal($invoiceCorrection->shippingCosts->getTotalPrice());
        $orderEntity->setShippingCosts($invoiceCorrection->shippingCosts);
        $orderEntity->setLineItems(new OrderLineItemCollection(array_map(
            fn(CalculatableOrderLineItem $orderLineItem) => $this->transformOrderLineItemToOrderLineItemEntity($orderLineItem, $orderEntity),
            $invoiceCorrection->lineItems,
        )));
    }

    private function transformOrderLineItemToOrderLineItemEntity(CalculatableOrderLineItem $orderLineItem, OrderEntity $order): OrderLineItemEntity
    {
        $orderLineItemEntity = new OrderLineItemEntity();
        // Set some required properties with default values
        $orderLineItemEntity->setId(Uuid::randomHex());
        $orderLineItemEntity->setPosition(0);
        $orderLineItemEntity->setGood(false);
        $orderLineItemEntity->setStackable(false);
        $orderLineItemEntity->setRemovable(false);

        $orderLineItemEntity->setOrderId($order->getId());
        $orderLineItemEntity->setQuantity($orderLineItem->quantity);
        $orderLineItemEntity->setUnitPrice($orderLineItem->price->getUnitPrice());
        $orderLineItemEntity->setTotalPrice($orderLineItem->price->getTotalPrice());
        $orderLineItemEntity->setPrice($orderLineItem->price);
        $orderLineItemEntity->setLabel($orderLineItem->label);
        $orderLineItemEntity->setType($orderLineItem->type);
        $orderLineItemEntity->setProductId($orderLineItem->productId);
        $orderLineItemEntity->setReferencedId($orderLineItem->productId);
        $orderLineItemEntity->setIdentifier($orderLineItem->productId ?? Uuid::randomHex());
        $orderLineItemEntity->setPayload($orderLineItem->payload);

        /** @var LineItem $lineItem */
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getProduct() && $lineItem->getProductId()) {
                $orderLineItemEntity->setProduct($lineItem->getProduct());
            }
        }

        return $orderLineItemEntity;
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

    /**
     * @param mixed $documentType
     * @param array $templateData
     * @param Context $context
     * @return array|OrderEntity[]
     * @throws InvoiceCorrectionException|\Exception
     */
    private function postProcessTemplateData(mixed $documentType, array $templateData, Context $context): array
    {
        if ($this->isInvoiceCorrectionDocument($documentType)) {

            /** @var $order OrderEntity */
            $order = $templateData['order'];

            try {
                $referencedDocumentConfiguration = $this->invoiceCorrectionConfigGenerator->getReferencedDocumentConfiguration($order->getId(), $context);
                $invoiceCorrection = $this->invoiceCorrectionCalculator->calculateInvoiceCorrection(
                    $order->getId(),
                    $referencedDocumentConfiguration[InvoiceCorrectionDocumentRenderer::DOCUMENT_CONFIGURATION_REFERENCED_DOCUMENT_ID_KEY],
                    $order->getVersionId(),
                    $context,
                );

                $this->applyInvoiceCorrectionToOrder($order, $invoiceCorrection);
                $this->dispatcher->dispatch(new InvoiceCorrectionOrderEvent($order, $context));

                $invoiceCorrectionTemplateData = [
                    'order' => $order
                ];

                $templateData = array_merge($templateData, $invoiceCorrectionTemplateData);
            } catch (InvoiceCorrectionException $e) {
                throw new \Exception($this->translatorService->trans('dde.exception.pickware-invoice-correction-requirements-not-met', $context));
            }

        }
        return $templateData;
    }

    /**
     * @param mixed $documentType
     * @return bool
     */
    public function isInvoiceCorrectionDocument(string $documentType): bool
    {
        return $documentType === PickwareErpStarterService::PICKWARE_ERP_INVOICE_CORRECTION &&
            !!$this->invoiceCorrectionConfigGenerator &&
            !!$this->dispatcher &&
            !!$this->invoiceCorrectionCalculator;
    }

}
