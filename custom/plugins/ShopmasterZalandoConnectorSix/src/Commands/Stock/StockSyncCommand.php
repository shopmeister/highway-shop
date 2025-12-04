<?php

namespace ShopmasterZalandoConnectorSix\Commands\Stock;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\Commands\ZalandoCommand;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Stock\ExportStockByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'sm:stock:sync')]
class StockSyncCommand extends ZalandoCommand
{
    private Logger $logger;
    private ApiZalandoProductPsrService $apiZalandoProductPsrService;

    /**
     * @var string
     */
    protected static $defaultName = 'sm:stock:sync';
    private ConfigService $configService;
    private SalesChannelGuard $salesChannelGuard;

    public function __construct(
        Logger                      $logger,
        ApiZalandoProductPsrService $apiZalandoProductPsrService,
        ConfigService               $configService,
        SalesChannelGuard           $salesChannelGuard
    )
    {
        parent::__construct();
        $this->logger = $logger->withName('StockSyncCommand');
        $this->apiZalandoProductPsrService = $apiZalandoProductPsrService;
        $this->configService = $configService;
        $this->salesChannelGuard = $salesChannelGuard;
    }


    public function runProcess(): void
    {
        if (!$this->isActive()) {
            return;
        }
        try {
            $bus = new ExportStockByPsrMessage();
            $this->apiZalandoProductPsrService->dispatchPsrForProcessing($bus);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }

    private function isActive(): bool
    {
        return $this->configService->getStockSyncConfig()->isActive();
    }
}