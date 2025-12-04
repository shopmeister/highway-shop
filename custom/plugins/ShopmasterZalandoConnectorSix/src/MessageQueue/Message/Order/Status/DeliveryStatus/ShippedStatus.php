<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus;

class ShippedStatus implements Status
{
    const DELIVERY_STATUS_SHIPPED = 'shipped';
    const CONFIG_NAME_STATE_IDS = 'shippedDeliveryStateIds';

    public static function getStatus(): string
    {
        return self::DELIVERY_STATUS_SHIPPED;
    }

    public static function getConfigName(): string
    {
        return self::CONFIG_NAME_STATE_IDS;
    }
}