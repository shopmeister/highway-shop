<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus;

class ReturnedStatus implements Status
{
    const DELIVERY_STATUS_RETURNED = 'returned';
    const CONFIG_NAME_STATE_IDS = 'returnedDeliveryStateIds';

    public static function getStatus(): string
    {
        return self::DELIVERY_STATUS_RETURNED;
    }

    public static function getConfigName(): string
    {
        return self::CONFIG_NAME_STATE_IDS;
    }
}