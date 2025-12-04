<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Order\LineItem;

use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\LineItem\OrderLineItemScope;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Order\ApiZalandoOrderService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineSetStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Item\ImportOrderItemStruct;
use Shopware\Core\Framework\Context;

class LineItemService
{

    public function __construct(
        private readonly LoggerInterface        $logger,
        private readonly ApiZalandoOrderService $apiZalandoOrderService,
        private readonly ConfigService          $configService,
    )
    {
    }

    public function updateLineItems(OrderScope $scope, Context $context): void
    {
        $orderCustomFields = $scope->getOrderEntity()->getCustomFields() ?? [];
        $orderId = $orderCustomFields[OrderCustomFields::CUSTOM_FIELD_ORDER_ID] ?? null;
        if (!$orderId) {
            $this->logger->error('can not find zalando order Id');
            return;
        }
        /** @var OrderLineItemScope $item */
        foreach ($scope->getLineItemScopeCollection() as $item) {
            $customFields = $item->getLineItemEntity()->getCustomFields() ?? [];
            $zOrderItemId = $customFields[ImportOrderItemStruct::Z_ORDER_ITEM_ID] ?? null;
            $zOrderLineId = $customFields[ImportOrderItemStruct::Z_ORDER_LINE_ID] ?? null;
            if (!$zOrderItemId || !$zOrderLineId) {
                continue;
            }
            $logisticCenterId = $this->configService->getImportOrderConfigBySalesChannelId($scope->getOrderEntity()->getSalesChannelId())->getLogisticCenterId();

            $struct = new OrderLineSetStruct();
            $struct->setId($zOrderLineId)
                ->setOrderId($orderId)
                ->setOrderItemId($zOrderItemId)
                ->setStatus($item->getStatus()->getStatus())
                ->setLogisticCenterId($logisticCenterId);
            try {
                $response = $this->apiZalandoOrderService->saveOrderLine($struct);
                $this->logger->info("Order line item save [status: {$item->getStatus()->getStatus()} , id: {$zOrderLineId} ] Response ID:", $response->getContentArray() ?? []);
            } catch (\Throwable $exception) {
                $this->logger->error($exception->getMessage(), $exception->getTrace());
            } finally {
                sleep(1);
            }
        }

    }
}