<?php

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Product\PriceSync;

use ShopmasterZalandoConnectorSix\Commands\Price\PriceSyncCommand;
use ShopmasterZalandoConnectorSix\Commands\RunProcessInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductPriceSyncTaskHandler extends ScheduledTaskHandler
{

    /**
     * @var PriceSyncCommand
     */
    private RunProcessInterface $priceSyncCommand;

    public function __construct(
        EntityRepository    $scheduledTaskRepository,
        RunProcessInterface $priceSyncCommand
    )
    {
        parent::__construct($scheduledTaskRepository);
        $this->priceSyncCommand = $priceSyncCommand;
    }

    public static function getHandledMessages(): iterable
    {
        return [
            ProductPriceSyncScheduledTask::class
        ];
    }

    public function run(): void
    {
        $this->priceSyncCommand->runProcess();
    }
}