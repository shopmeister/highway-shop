<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class PickwareTrackingExportTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'shm.pickware_tracking_export_task';
    }

    public static function getDefaultInterval(): int
    {
        return 600;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}