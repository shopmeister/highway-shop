<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product;

use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\Stock\StockCollection;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class ApiZalandoProductStockService
{
    /**
     * @var ClientService
     */
    private ClientService $clientService;

    /**
     * @param ClientService $clientService
     */
    public function __construct(
        ClientService $clientService
    )
    {
        $this->clientService = $clientService;
    }


    public function updateZalandoStock(StockCollection $stockCollection): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_POST)
            ->setUrl('/stocks');
        $request->setContent(['items' => $stockCollection->toArray()]);
        return $this->clientService->request($request);
    }
}