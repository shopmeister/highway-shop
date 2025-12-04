<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product;

use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\PriceAttempt\PriceAttemptSetStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Product\PriceReport\PriceReportCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Product\PriceReport\PriceReportStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class ApiZalandoProductPriceAttemptService
{
    private ClientService $clientService;
    private EntityRepository $repositoryPriceReport;
    private ConfigService $configService;

    public function __construct(
        ClientService    $clientService,
        EntityRepository $repositoryPriceReport,
        ConfigService    $configService
    )
    {
        $this->clientService = $clientService;
        $this->repositoryPriceReport = $repositoryPriceReport;
        $this->configService = $configService;
    }

    public function makePriceReportByPsr(PsrProductCollection $psr): void
    {
        $requestDataStruct = $this->makePriceAttemptSetStructFromPsr($psr);
        if (!$requestDataStruct) {
            return;
        }
        $request = new RequestStruct(RequestStruct::METHOD_POST);
        $request->setContent($requestDataStruct)
            ->setUrl('/price-attempts');
        $this->makePriceAttemptSetStructFromRequest($request, $psr);
    }

    private function makePriceAttemptSetStructFromPsr(PsrProductCollection $psr): ?PriceAttemptSetStruct
    {
        $activeSalesChannels = $this->getActiveSalesChannels($psr);
        if (!$activeSalesChannels) {
            return null;
        }
        $struct = new PriceAttemptSetStruct();
        $struct->setEans($psr->getEanList())
            ->setSalesChannels($activeSalesChannels)
            ->setPageSize($psr->count());
        return $struct;
    }

    private function makePriceAttemptSetStructFromRequest(RequestStruct $request, PsrProductCollection $psr)
    {
        $response = $this->clientService->request($request);
        $this->upsertByResponseItems($response["items"], $psr);
        if (!empty($response["cursors"]["next"])) {
            $request->setLink($response["cursors"]["next"]);
            $this->makePriceAttemptSetStructFromRequest($request, $psr);
        }
    }

    private function upsertByResponseItems(array $items, PsrProductCollection $psr)
    {
        $collection = new PriceReportCollection();
        /** @var array $item */
        foreach ($items as $item) {
            $struct = new PriceReportStruct();
            $struct->setId($struct::uuidToId($item["id"]))
                ->setProductId($psr->get($item["ean"])->getProduct()->getId())
                ->setZSalesChannelId($item["sales_channel_id"])
                ->setBaseRegularPriceAmount($item["base_price"]["regular_price"]["amount"])
                ->setBaseRegularPriceCurrency($item["base_price"]["regular_price"]["currency"])
                ->setBasePromotionalPriceAmount($item["base_price"]["promotional_price"]["amount"] ?? null)
                ->setBasePromotionalPriceCurrency($item["base_price"]["promotional_price"]["currency"] ?? null)
                ->setZStatusType($item["base_price"]["status"]);
            $collection->add($struct);
        }
        if ($collection->count()) {
            $this->repositoryPriceReport->upsert($collection->toArray(), Context::createDefaultContext());
        }
    }

    private function getActiveSalesChannels(PsrProductCollection $psr): ?array
    {
        $data = null;
        foreach ($psr->getSalesChannels() as $salesChannel) {
            if ($this->configService->getPriceReportSyncConfig($salesChannel)->isActive()) {
                $data[] = $salesChannel;
            }
        }
        return $data;
    }


}