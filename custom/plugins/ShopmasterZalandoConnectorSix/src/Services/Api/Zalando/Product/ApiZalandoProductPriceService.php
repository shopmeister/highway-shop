<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice\OffersPriceCollection;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class ApiZalandoProductPriceService
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

    /**
     * @param OffersPriceCollection $collection
     * @return ResponseStruct
     * @throws MethodNameExceptions
     */
    public function updateZalandoPrice(OffersPriceCollection $collection): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_POST)
            ->setUrl('/prices')
            ->setContent(['product_prices' => $collection->toArray()]);
        return $this->clientService->request($request);
    }
}