<?php

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Product\StockSync;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ProductStockSyncScheduledTask extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'sm.zalando.product_stock_sync';
    }

    public static function getDefaultInterval(): int
    {
        return 900;
    }
}