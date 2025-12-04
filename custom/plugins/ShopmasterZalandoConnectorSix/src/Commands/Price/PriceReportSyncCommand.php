<?php

namespace ShopmasterZalandoConnectorSix\Commands\Price;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\Commands\ZalandoCommand;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Price\ImportPriceReportByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'sm:price-report:sync')]
class PriceReportSyncCommand extends ZalandoCommand
{

    protected static $defaultName = 'sm:price-report:sync';
    private Logger $logger;
    private ConfigService $configService;
    private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService;
    private ApiZalandoProductPsrService $apiZalandoProductPsrService;

    public function __construct(
        Logger                         $logger,
        ConfigService                  $configService,
        ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        ApiZalandoProductPsrService    $apiZalandoProductPsrService
    )
    {
        parent::__construct();
        $this->logger = $logger->withName('PriceReportSyncCommand');
        $this->configService = $configService;
        $this->apiZalandoSalesChannelsService = $apiZalandoSalesChannelsService;
        $this->apiZalandoProductPsrService = $apiZalandoProductPsrService;
    }

    public function runProcess(): void
    {
        if (!$this->isActive()) {
            return;
        }
        try {
            $bus = new ImportPriceReportByPsrMessage();
            $this->apiZalandoProductPsrService->dispatchPsrForProcessing($bus);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }

    private function isActive(): bool
    {
        /** @var SalesChannelStruct $salesChannel */
        foreach ($this->apiZalandoSalesChannelsService->getCollection() as $salesChannel) {
            if ($this->configService->getPriceReportSyncConfig($salesChannel->getSalesChannelId())->isActive()) {
                return true;
            }
        }
        return false;
    }
}