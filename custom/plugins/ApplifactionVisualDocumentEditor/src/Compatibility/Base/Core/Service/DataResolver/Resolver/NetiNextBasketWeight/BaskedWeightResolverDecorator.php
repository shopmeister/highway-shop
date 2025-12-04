<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\NetiNextBasketWeight;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use NetInventors\NetiNextBasketWeight\Service\Order\OrderTotalWeightCalculator;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class BaskedWeightResolverDecorator implements OrderDocumentResolverInterface
{

    private OrderDocumentResolverInterface $decoratedResolver;
    private ?OrderTotalWeightCalculator $orderTotalWeightCalculator;
    private ?SystemConfigService $configService;

    public function __construct(
        OrderDocumentResolverInterface $decoratedResolver,
        private readonly Logger        $logger,
        ?OrderTotalWeightCalculator    $orderTotalWeightCalculator,
        ?SystemConfigService           $configService
    )
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->orderTotalWeightCalculator = $orderTotalWeightCalculator;
        $this->configService = $configService;
    }

    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use (&$templateData) {

            if (!$this->orderTotalWeightCalculator || !$this->configService) return $templateData;

            /** @var ?OrderEntity $order */
            $order = $templateData['order'] ?? null;
            $salesChannelId = $order ? $order->getSalesChannelId() ?? null : null;

            if (
                $order instanceof OrderEntity
                && $salesChannelId
                && $this->configService->get('NetiNextBasketWeight.config.active', $salesChannelId)
                && $this->configService->get('NetiNextBasketWeight.config.addWeightToDocuments', $salesChannelId)
            ) {
                $templateData['neti_next_basket_weight'] = [
                    'addTotalWeights' => $this->configService->get('NetiNextBasketWeight.config.addWeightToDocuments'),
                    'totalWeight' => $this->orderTotalWeightCalculator->calculateTotalWeight($order)
                ];
            }

        }, "Basket weight data resolution duration: %s ms");

        return $templateData;
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
