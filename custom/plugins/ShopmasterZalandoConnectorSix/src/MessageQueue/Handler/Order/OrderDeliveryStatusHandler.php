<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\Services\Order\DeliveryStatus\OrderDeliveryStatusUpdateService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: OrderDeliveryStatusMessage::class)]
class OrderDeliveryStatusHandler
{
    public function __construct(
        readonly private OrderDeliveryStatusUpdateService $deliveryStatusUpdateService
    )
    {
    }

    /**
     * @param OrderDeliveryStatusMessage $message
     * @return void
     */
    public function __invoke(OrderDeliveryStatusMessage $message): void
    {
        $scope = $message->getScope();
        if (!$scope->getOrderEntity()) {
            return;
        }
        if ($message->getScope() instanceof OrderScope) {
            $message->getScope()->generate();
            $this->deliveryStatusUpdateService->updateInZalando($message->getScope(), $message->getContext());
        }
    }

}