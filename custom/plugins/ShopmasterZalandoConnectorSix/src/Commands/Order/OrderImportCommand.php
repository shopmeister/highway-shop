<?php

namespace ShopmasterZalandoConnectorSix\Commands\Order;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\Commands\ZalandoCommand;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\ImportOrderByApiDataMessage;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Order\ApiZalandoOrderService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use Symfony\Component\Console\Attribute\AsCommand;


#[AsCommand('sm:order:import')]
class OrderImportCommand extends ZalandoCommand
{

    private Logger $logger;

    /**
     * @param Logger $logger
     * @param ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService
     * @param ConfigService $configService
     * @param ApiZalandoOrderService $apiZalandoOrderService
     * @param SalesChannelGuard $salesChannelGuard
     */
    public function __construct(
        Logger                                          $logger,
        private readonly ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        private readonly ConfigService                  $configService,
        private readonly ApiZalandoOrderService         $apiZalandoOrderService,
        private readonly SalesChannelGuard              $salesChannelGuard
    )
    {
        parent::__construct();

        $this->logger = $logger->withName('OrderImportCommand');
    }


    public function runProcess(): void
    {
        if (!$this->isActive()) {
            return;
        }
        try {
            $bus = new ImportOrderByApiDataMessage();
            $this->apiZalandoOrderService->dispatchOrderDataForProcessing($bus);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }

    private function isActive(): bool
    {
        /** @var SalesChannelStruct $salesChannel */
        foreach ($this->apiZalandoSalesChannelsService->getCollection() as $salesChannel) {
            try {
                // LIZENZ-CHECK ZUERST
                $this->salesChannelGuard->guardSalesChannel($salesChannel->getSalesChannelId());

                // Dann bestehende Feature-Flag-Prüfung
                if ($this->configService->getImportOrderConfigBySalesChannelId($salesChannel->getSalesChannelId())->isActive()) {
                    return true;
                }
            } catch (SalesChannelNotLicensedException $e) {
                // Kanal nicht lizenziert - überspringen
                $this->logger->warning($e->getMessage(), [
                    'salesChannelId' => $salesChannel->getSalesChannelId()
                ]);
                continue;
            }
        }
        return false;
    }
}