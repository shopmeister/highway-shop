<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus;

class CanceledStatus implements Status
{
    const DELIVERY_STATUS_CANCELED = 'canceled';

    const CONFIG_NAME_STATE_IDS = 'canceledDeliveryStateIds';

    public static function getStatus(): string
    {
        return self::DELIVERY_STATUS_CANCELED;
    }

    public static function getConfigName(): string
    {
        return self::CONFIG_NAME_STATE_IDS;
    }
}