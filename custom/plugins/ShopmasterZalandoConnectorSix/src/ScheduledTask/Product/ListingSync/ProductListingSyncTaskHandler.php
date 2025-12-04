<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\ScheduledTask\Product\ListingSync;

use ShopmasterZalandoConnectorSix\Commands\Listing\ListingSyncCommand;
use ShopmasterZalandoConnectorSix\Commands\RunProcessInterface;
use ShopmasterZalandoConnectorSix\ScheduledTask\Product\ListingSync\ProductListingSyncScheduledTask;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductListingSyncTaskHandler extends ScheduledTaskHandler
{
    private RunProcessInterface $listingSyncCommand;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        RunProcessInterface $listingSyncCommand
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->listingSyncCommand = $listingSyncCommand;
    }

    public static function getHandledMessages(): iterable
    {
        return [ProductListingSyncScheduledTask::class];
    }

    public function run(): void
    {
        $this->listingSyncCommand->runProcess();
    }
}