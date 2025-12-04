<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Order\StateSync;

use ShopmasterZalandoConnectorSix\Commands\Order\OrderStateSyncCommand;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class OrderStateSyncTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository                       $scheduledTaskRepository,
        private readonly OrderStateSyncCommand $stateSyncCommand
    )
    {
        parent::__construct($scheduledTaskRepository);
    }

    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [OrderStateSyncScheduledTask::class];
    }

    public function run(): void
    {
        $this->stateSyncCommand->runProcess();
    }
}