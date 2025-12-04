<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\LogisticCenter;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class ApiZalandoLogisticCenterService
{

    public function __construct(
        private readonly ClientService $clientService
    )
    {
    }

    /**
     * @throws MethodNameExceptions
     */
    public function getLogisticCenters(): ResponseStruct
    {
        $request = new RequestStruct(RequestStruct::METHOD_GET);
        $request->setUrl('/logistic-centers');
        return $this->clientService->request($request);
    }
}