<?php

namespace ShopmasterZalandoConnectorSix\Services\Order\Specification\Shopware;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderStatusIsSync implements ZalandoOrderCheckInterface
{
    public function isAvailable(OrderEntity $order, Context $context): bool
    {
        if ($order->getCustomFields()[OrderCustomFields::CUSTOM_FIELD_STATUS_SENT] ?? null) {
            return false;
        }
        return true;
    }


}