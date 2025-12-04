<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Order\StateSync;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class OrderStateSyncScheduledTask extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'sm.zalando.order_state_sync';
    }

    public static function getDefaultInterval(): int
    {
        return 60 * 60;
    }
}