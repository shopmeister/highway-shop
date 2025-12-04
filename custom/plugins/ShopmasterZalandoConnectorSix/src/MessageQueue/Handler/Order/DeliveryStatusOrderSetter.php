<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use ShopmasterZalandoConnectorSix\Services\Order\DeliveryStatus\OrderDeliverySpecificationService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: OrderDeliveryStatusMessage::class, priority: 5000)]
class DeliveryStatusOrderSetter
{
    public function __construct(
        readonly private EntityRepository                  $repositoryOrder,
        readonly private OrderDeliverySpecificationService $orderDeliverySpecificationService
    )
    {
    }

    /**
     * @param OrderDeliveryStatusMessage $message
     * @return void
     */
    public function __invoke(OrderDeliveryStatusMessage $message): void
    {
        $this->setOrderScopeData($message);
    }

    /**
     * @param OrderDeliveryStatusMessage $message
     * @return void
     */
    private function setOrderScopeData(OrderDeliveryStatusMessage $message): void
    {
        $scope = $message->getScope();
        if (!$scope->getOrderEntity()) {
            $this->setOrder($scope, $message->getContext());
        }
    }

    /**
     * @param OrderScope $scope
     * @param Context $context
     * @return void
     */
    private function setOrder(OrderScope $scope, Context $context): void
    {
        $criteria = new Criteria([$scope->getOrderId()]);
        $criteria->addAssociations([
            'deliveries.stateMachineState',
            'lineItems',
            'pickwareShippingShipments.trackingCodes',
        ]);
        /** @var OrderEntity|null $order */
        $order = $this->repositoryOrder->search($criteria, $context)->first();
        if (!$order) {
            return;
        }
        $orderIsAvailableForUpdate = $this->orderDeliverySpecificationService->swOrderIsAvailableForUpdateInZalando($order, $context);
        if (!$orderIsAvailableForUpdate) {
            return;
        }
        $scope->setOrderEntity($order);
        $scope->setStatus($context->{Status::ORDER_ZALANDO_STATUS});
    }
}