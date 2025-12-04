<?php

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Product\PriceSync;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ProductPriceSyncScheduledTask extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'sm..zalando.product_price_sync';
    }

    public static function getDefaultInterval(): int
    {
        return 4 * 3600;
    }
}