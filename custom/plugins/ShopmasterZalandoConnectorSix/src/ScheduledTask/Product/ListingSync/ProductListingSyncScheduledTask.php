<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Product\ListingSync;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ProductListingSyncScheduledTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sm.zalando.product_listing_sync';
    }

    public static function getDefaultInterval(): int
    {
        return 3600; // 1 hour
    }
}