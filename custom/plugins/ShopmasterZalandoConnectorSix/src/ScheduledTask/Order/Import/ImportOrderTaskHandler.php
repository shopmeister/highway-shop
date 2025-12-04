<?php

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Order\Import;

use ShopmasterZalandoConnectorSix\Commands\Order\OrderImportCommand;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ImportOrderTaskHandler extends ScheduledTaskHandler
{

    private OrderImportCommand $orderImportCommand;

    public function __construct(
        EntityRepository   $scheduledTaskRepository,
        OrderImportCommand $orderImportCommand
    )
    {
        parent::__construct($scheduledTaskRepository);
        $this->orderImportCommand = $orderImportCommand;
    }

    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ImportOrderScheduledTask::class];
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->orderImportCommand->runProcess();
    }
}