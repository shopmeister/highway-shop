<?php

namespace ShopmasterZalandoConnectorSix\Commands\Price;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\Commands\ZalandoCommand;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Price\ExportPriceByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'sm:price:sync')]
class PriceSyncCommand extends ZalandoCommand
{

    private Logger $logger;
    private ApiZalandoProductPsrService $apiZalandoProductPsrService;

    /**
     * @var string
     */
    protected static $defaultName = 'sm:price:sync';
    private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService;
    private ConfigService $configService;
    private SalesChannelGuard $salesChannelGuard;

    public function __construct(
        Logger                         $logger,
        ApiZalandoProductPsrService    $apiZalandoProductPsrService,
        ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        ConfigService                  $configService,
        SalesChannelGuard              $salesChannelGuard
    )
    {
        parent::__construct();
        $this->logger = $logger->withName('PriceSyncCommand');
        $this->apiZalandoProductPsrService = $apiZalandoProductPsrService;
        $this->apiZalandoSalesChannelsService = $apiZalandoSalesChannelsService;
        $this->configService = $configService;
        $this->salesChannelGuard = $salesChannelGuard;
    }

    public function runProcess(): void
    {
        if (!$this->isActive()) {
            return;
        }
        try {
            $bus = new ExportPriceByPsrMessage();
            $this->apiZalandoProductPsrService->dispatchPsrForProcessing($bus);
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
                if ($this->configService->getPriceSyncConfig($salesChannel->getSalesChannelId())->isActive()) {
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