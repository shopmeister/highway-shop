<?php

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Product\StockSync;

use ShopmasterZalandoConnectorSix\Commands\RunProcessInterface;
use ShopmasterZalandoConnectorSix\Commands\Stock\StockSyncCommand;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductStockSyncTaskHandler extends ScheduledTaskHandler
{

    /**
     * @var StockSyncCommand
     */
    private RunProcessInterface $stockSyncCommand;

    public function __construct(
        EntityRepository    $scheduledTaskRepository,
        RunProcessInterface $stockSyncCommand
    )
    {
        parent::__construct($scheduledTaskRepository);
        $this->stockSyncCommand = $stockSyncCommand;
    }

    public static function getHandledMessages(): iterable
    {
        return [ProductStockSyncScheduledTask::class];
    }

    public function run(): void
    {
        $this->stockSyncCommand->runProcess();
    }
}