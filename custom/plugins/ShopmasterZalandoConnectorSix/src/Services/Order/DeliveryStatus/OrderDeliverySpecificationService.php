<?php

namespace ShopmasterZalandoConnectorSix\Services\Order\DeliveryStatus;

use ShopmasterZalandoConnectorSix\Services\Order\Specification\Shopware\ZalandoOrderCheckInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderDeliverySpecificationService
{
    public function __construct(
        private readonly iterable $zalandoOrderCheckingHandler,
        private readonly iterable $zalandoOrderUpdateDeliveryStatusHandler
    )
    {
    }

    public function swOrderIsAvailableForUpdateInZalando(OrderEntity $order, Context $context): bool
    {
        /** @var ZalandoOrderCheckInterface $checkingHandler */
        foreach ($this->zalandoOrderCheckingHandler as $checkingHandler) {
            if (!$checkingHandler->isAvailable($order, $context)) {
                return false;
            }
        }

        /** @var ZalandoOrderCheckInterface $checkingHandler */
        foreach ($this->zalandoOrderUpdateDeliveryStatusHandler as $checkingHandler) {
            if (!$checkingHandler->isAvailable($order, $context)) {
                return false;
            }
        }
        return true;
    }
}