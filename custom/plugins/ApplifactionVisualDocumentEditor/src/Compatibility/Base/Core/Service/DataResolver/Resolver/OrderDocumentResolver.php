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
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\CustomizedProducts\CustomizedProductsOrderItemSorter;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\TestDataProvider\OrderTestDataProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use RuntimeException;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\Renderer\CreditNoteRenderer;
use Shopware\Core\Checkout\Document\Renderer\DeliveryNoteRenderer;
use Shopware\Core\Checkout\Document\Renderer\InvoiceRenderer;
use Shopware\Core\Checkout\Document\Renderer\StornoRenderer;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;

class OrderDocumentResolver extends AbstractOrderDocumentResolver
{

    private EntityRepository $orderRepository;

    private EntityRepository $documentRepository;

    private OrderTestDataProvider $orderTestDataProvider;

    private EntityRepository $mediaRepository;

    private EntityRepository $productRepository;

    private CustomizedProductsOrderItemSorter $customizedProductsOrderItemSorter;

    protected Connection $connection;

    public function __construct(
        EntityRepository                  $orderRepository,
        EntityRepository                  $documentRepository,
        OrderTestDataProvider             $orderTestDataProvider,
        EntityRepository                  $mediaRepository,
        EntityRepository                  $productRepository,
        EntityRepository                  $customFieldSetRepository,
        CustomizedProductsOrderItemSorter $customizedProductsOrderItemSorter,
        Connection                        $connection,
        private readonly Logger           $logger
    )
    {
        $this->orderRepository = $orderRepository;
        $this->documentRepository = $documentRepository;
        $this->orderTestDataProvider = $orderTestDataProvider;
        $this->mediaRepository = $mediaRepository;
        $this->productRepository = $productRepository;
        $this->customizedProductsOrderItemSorter = $customizedProductsOrderItemSorter;
        $this->connection = $connection;
        parent::__construct($customFieldSetRepository);
    }

    private function getOrderCriteria(): Criteria
    {
        $orderCriteria = new Criteria();

        // From CartService that dispatches the Order
        $orderCriteria
            ->addAssociation('lineItems')
            ->addAssociation('lineItems.product')
            ->addAssociation('lineItems.product.unit')
            ->addAssociation('lineItems.product.visibilities')
            ->addAssociation('lineItems.product.cover')
            ->addAssociation('lineItems.product.deliveryTime')
            ->addAssociation('deliveries.shippingMethod')
            ->addAssociation('deliveries.shippingOrderAddress.country')
            ->addAssociation('deliveries.shippingOrderAddress.countryState')
            ->addAssociation('cartPrice.calculatedTaxes')
            ->addAssociation('transactions.paymentMethod')
            ->addAssociation('currency')
            ->addAssociation('addresses.country')
            ->addAssociation('addresses.countryState')
            ->addAssociation('language')
            ->addAssociation('language.locale')
            ->addAssociation('orderCustomer')
            ->addAssociation('orderCustomer.customer')
            ->addAssociation('orderCustomer.salutation')
            ->addAssociation('documents')
            ->addAssociation('documents.documentType')

            // eager load salesChannel -> not an available association in order
            ->addAssociation('salesChannel')
            ->addAssociation('salesChannel.currencies');

        $orderCriteria->getAssociation('lineItems')->addSorting(new FieldSorting('position'));
        $orderCriteria->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));
        $orderCriteria->getAssociation('deliveries')->addSorting(new FieldSorting('createdAt'));

        $orderCriteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $orderCriteria->setLimit(1);

        return $orderCriteria;
    }

    private function getDocumentCriteria(): Criteria
    {
        $documentCriteria = new Criteria();
        $documentCriteria->addAssociation('documentType');
        $documentCriteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $documentCriteria->setLimit(1);
        return $documentCriteria;
    }

    /**
     * @throws Exception
     */
    private function getOrder(Context $context, $documentType = null, $orderNumber = null): ?OrderEntity
    {
        $isFakeOrder = !!$orderNumber && str_starts_with($orderNumber, OrderTestDataProvider::FAKE_ORDER_NUMBER_PREFIX);
        $orderCriteria = $this->getOrderCriteria();

        if ($orderNumber) {
            if (!$isFakeOrder) {
                $orderCriteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));
            }
            // only use the order's language id, if we don't render a preview PDF
            if (!isset($context->getVars()['isPreview']) || !$context->getVars()['isPreview']) {
                if ($languageId = $this->getLanguageIdByOrderNumber($orderNumber)) {
                    $context = $context->assign([
                        'languageIdChain' => array_values(array_filter(array_unique(array_merge([$languageId], $context->getLanguageIdChain())))),
                    ]);
                }
            }
        }

        if (!$isFakeOrder) {
            /** @var OrderEntity|null $order */
            $order = $this->orderRepository->search($orderCriteria, $context)->first();
            if ($order != null) {
                $order = $this->addProductVariantParentsToOrder($order, $context);
            }
        } else {
            $order = null;
        }

        if (!$order) {
            if ($orderNumber && !$isFakeOrder) {
                throw new RuntimeException("No order with order number " . $orderNumber . " found. Please make sure you use a existing order number and try it again.", 1001);
            } else {
                $fakeOrderItemCount = !!$orderNumber ? (int)substr($orderNumber, strlen(OrderTestDataProvider::FAKE_ORDER_NUMBER_PREFIX)) : 2;
                $order = $this->orderTestDataProvider->getFakeOrder($documentType, $context, $fakeOrderItemCount);
                if ($isFakeOrder) {
                    // If the fake order number was used, try to fetch the last order's id
                    $orderCriteria = $this->getOrderCriteria();
                    /** @var OrderEntity $recentOrder */
                    $recentOrder = $this->orderRepository->search($orderCriteria, $context)->first();
                    if ($recentOrder) {
                        $order->setId($recentOrder->getId());
                    }
                }
            }
        }

        $this->customizedProductsOrderItemSorter->sortOrderItems($order);

        return $order;
    }

    private function addProductVariantParentsToOrder(OrderEntity $order, Context $context): OrderEntity
    {

        // Collect parent IDs
        $parentIds = [];
        /** @var OrderLineItemEntity $lineItem */
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getProduct() && $lineItem->getProduct()->getParentId()) {
                $parentIds[] = $lineItem->getProduct()->getParentId();
            }
        }

        if (sizeof($parentIds) > 0) {

            // Get parent entities
            $criteria = new Criteria();
            $criteria->addAssociation('unit');
            $criteria->addAssociation('visibilities');
            $criteria->addAssociation('cover');
            $criteria->addAssociation('deliveryTime');

            $criteria->addFilter(new EqualsAnyFilter('id', $parentIds));
            $parents = $this->productRepository->search($criteria, $context);

            // Assign parents to variants
            /** @var OrderLineItemEntity $lineItem */
            foreach ($order->getLineItems() as $lineItem) {
                if ($lineItem->getProduct() && $lineItem->getProduct()->getParentId() && $parents->has($lineItem->getProduct()->getParentId())) {
                    $lineItem->getProduct()->setParent($parents->get($lineItem->getProduct()->getParentId()));
                }
            }

        }

        return $order;
    }

    public function getPdfDocument(Context $context, $documentType, OrderEntity $order): ?DocumentEntity
    {
        $documentCriteria = $this->getDocumentCriteria();
        if (!is_null($documentType)) {
            $documentCriteria->addFilter(new EqualsFilter('documentType.technicalName', $documentType));
        }
        $document = $this->documentRepository->search($documentCriteria, $context)->first();
        if (!$document) {
            $document = $this->orderTestDataProvider->getFakeDocument($documentType, $order);
        }
        return $document;
    }

    public function canResolveType(string $type): bool
    {
        return in_array($type, [
            InvoiceRenderer::TYPE,
            StornoRenderer::TYPE,
            DeliveryNoteRenderer::TYPE,
            CreditNoteRenderer::TYPE
        ]);
    }

    /**
     * @throws Exception
     */
    public function resolve(PreviewDataResolverContextInterface $context, string $type): array
    {
        return $this->getTemplateData($context->getContext());
    }

    public function getAvailableEntities(): array
    {
        return [
            "order" => "order",
            "config" => "document_configuration",
            "document" => "document",
            "billingAddress" => "order_address",
            "shippingAddress" => "order_address",
            "currency" => "currency"
        ];
    }

    /**
     * @param Context $context
     * @param null $orderNumber
     * @param null $documentType
     * @return array
     */
    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {

        $templateData = [];
        $this->logger->logExecutionDuration(function () use ($context, $orderNumber, $documentType, &$templateData) {

            $order = $this->getOrder($context, $documentType, $orderNumber);

            $document = $this->getPdfDocument($context, $documentType, $order);

            /* Fix for @see https://github.com/shopware/platform/issues/2672 */
            $config = $document->getConfig();
            if (!isset($config['companyPhone']) || !$config['companyPhone']) {
                $config['companyPhone'] = 'placeholder-telephone-number';
            }

            // Sort line items by parent-child relation and position
            // Modifies the Order Entity
            $this->sortLineItemsByNesting($order);

            // Enrich order data (e.g. load cover data)
            foreach ($order->getLineItems() as $lineItem) {
                if ($lineItem->getCoverId() !== null) {
                    $cover = $this->mediaRepository->search(new Criteria([$lineItem->getCoverId()]), $context)->first();
                    $lineItem->setCover($cover);
                }
            }

            $templateData = [
                'order' => $order,
                'currencyIsoCode' => $order->getCurrency()->getIsoCode(),
                "document" => $document,
                'config' => $config,
                "billingAddress" => $order->getAddresses()->get($order->getBillingAddressId())
            ];

            /*
             * In case the order has only digital products, no delivery will be available.
             * This will cause problems loading the shippingOrderAddress
             */
            if ($order->getDeliveries()->count() == 0) {
                $templateData["shippingAddress"] = $templateData["billingAddress"];
            } else {
                $templateData["shippingAddress"] = $order->getDeliveries()->first()->getShippingOrderAddress();
            }

        }, "Order data resolution duration: %s ms");

        return $templateData;
    }

    /**
     * @throws Exception
     */
    public function getOrderById($orderId, Context $context): OrderEntity
    {
        $orderNumber = $this->getOrderNumber($orderId);
        return $this->getOrder($context, null, $orderNumber);
    }

    /**
     * @throws Exception
     */
    private function getOrderNumber(string $orderId): string
    {
        return (string)$this->connection->fetchOne(
            'SELECT order_number FROM `order` WHERE `id` = :orderId LIMIT 1',
            ['orderId' => Uuid::fromHexToBytes($orderId)],
        );
    }

    /**
     * @throws Exception
     */
    private function getLanguageIdByOrderNumber(string $orderNumber): string
    {
        return (string)$this->connection->fetchOne(
            'SELECT LOWER(HEX(language_id)) FROM `order` WHERE `order_number` = :orderNumber LIMIT 1',
            ['orderNumber' => $orderNumber],
        );
    }

    /**
     * This function modifies the order by resorting the line items.
     * Line items will be sorted by "parent/child" relation and by position.
     *
     * If a line item is a child, the payload of the item ist extended:
     * "is-child", "level", "first-child", "last-child"
     *
     * - Item A
     * - item B
     *     - child A
     *     - child B
     *     - child C
     * - item C
     *
     * Will become
     *
     * - Item A
     * - item B
     * - child A (isChild=true, level=1, firstChild=true)
     * - child B (isChild=true, level=1)
     * - child C (isChild=true, level=1, lastChild=true)
     * - item C
     *
     * @param OrderEntity $order
     * @return void
     */
    private function sortLineItemsByNesting(OrderEntity $order): void
    {
        $sortedLineItems = [];

        $flattenRecursive = function (array $nestedLineItems, int $level = 0) use (&$flattenRecursive, &$sortedLineItems) {
            /** @var OrderLineItemEntity $lineItem */
            foreach ($nestedLineItems as $key => $lineItem) {
                if ($level > 0) {
                    $payload = $lineItem->getPayload() ?? [];

                    $payload['isChild'] = true;
                    $payload['level'] = $level;

                    if ($key === array_key_first($nestedLineItems)) {
                        $payload['firstChild'] = true;
                    }

                    if ($key === array_key_last($nestedLineItems)) {
                        $payload['lastChild'] = true;
                    }

                    $lineItem->setPayload($payload);
                }

                $sortedLineItems[] = $lineItem;

                if ($children = $lineItem->getChildren()) {
                    $flattenRecursive($children->getElements(), $level + 1);
                }
            }

            return $sortedLineItems;
        };

        $nestedLineItems = $order->getNestedLineItems();
        if ($nestedLineItems) {
            $flattenRecursive($nestedLineItems->getElements());
        }

        $order->setLineItems(new OrderLineItemCollection($sortedLineItems));
    }

}
