<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\RedMagnalisterSW6;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Exception\PluginNotFoundException;
use Shopware\Core\Framework\Plugin\PluginService;
use Symfony\Component\DependencyInjection\Container;

class MagnalisterOrderResolverDecorator implements OrderDocumentResolverInterface
{

    const PLUGIN_NAME = 'RedMagnalisterSW6';

    private OrderDocumentResolverInterface $decoratedResolver;
    private Connection $connection;
    private PluginService $pluginService;

    public function __construct(
        OrderDocumentResolverInterface $decoratedResolver,
        Connection                     $connection,
        PluginService                  $pluginService,
        private readonly Logger        $logger
    )
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->connection = $connection;
        $this->pluginService = $pluginService;
    }

    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($documentType, $context, &$templateData) {

            $pluginNotFound = false;
            try {
                $magnalisterPlugin = $this->pluginService->getPluginByName(self::PLUGIN_NAME, $context);
            } catch (PluginNotFoundException $e) {
                $pluginNotFound = true;
            }
            if (!!$pluginNotFound || !$magnalisterPlugin->getInstalledAt() || !$magnalisterPlugin->getActive()) return $templateData;

            /** @var ?OrderEntity $order */
            $order = $templateData['order'] ?? null;
            if ($order instanceof OrderEntity) {
                $magnalisterOrders = $this->connection->executeQuery(
                    'SELECT * FROM `magnalister_orders` WHERE `current_orders_id` = :orders_id',
                    ['orders_id' => $order->getId()]
                )->fetchAllAssociative();
                if (!empty($magnalisterOrders)) {
                    $magnalisterOrder = $magnalisterOrders[0];
                    $magnalisterOrder['data'] = json_decode($magnalisterOrder['data']);
                    $magnalisterOrder['orderData'] = json_decode($magnalisterOrder['orderData']);
                    $templateData['magnalisterOrder'] = $magnalisterOrder;
                }
            }

        }, "Magnalister data resolution duration: %s ms");

        return $templateData;
    }

    /**
     * @return Container
     */
    private function getContainer(): Container
    {
        return $this->container;
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
