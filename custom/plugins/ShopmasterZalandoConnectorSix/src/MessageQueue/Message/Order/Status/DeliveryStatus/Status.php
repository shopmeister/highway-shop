<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus;

interface Status
{
    const ORDER_ZALANDO_STATUS = 'zalandoStatus';
    const DELIVERY_STATUS = [
        'shipped' => ShippedStatus::class,
        'canceled' => CanceledStatus::class,
        'returned' => ReturnedStatus::class
    ];
    const CONFIG_NAME = [
        ShippedStatus::CONFIG_NAME_STATE_IDS => ShippedStatus::class,
        CanceledStatus::CONFIG_NAME_STATE_IDS => CanceledStatus::class,
        ReturnedStatus::CONFIG_NAME_STATE_IDS => ReturnedStatus::class
    ];

    public static function getStatus(): string;

    public static function getConfigName(): string;
}