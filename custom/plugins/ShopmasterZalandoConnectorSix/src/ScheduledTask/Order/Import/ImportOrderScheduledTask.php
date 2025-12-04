<?php

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Order\Import;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ImportOrderScheduledTask extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'sm.zalando.order_import';
    }

    public static function getDefaultInterval(): int
    {
        return 600;
    }
}