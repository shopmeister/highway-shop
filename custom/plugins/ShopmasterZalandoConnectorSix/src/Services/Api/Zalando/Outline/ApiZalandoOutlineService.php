<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Outline;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class ApiZalandoOutlineService
{
    private ClientService $clientService;

    public function __construct(
        ClientService $clientService
    )
    {
        $this->clientService = $clientService;
    }

    /**
     * @throws MethodNameExceptions
     */
    public function getOutlines(): ResponseStruct
    {
        $request = new RequestStruct(RequestStruct::METHOD_GET);
        $request->setUrl('/outlines');
        $response = $this->clientService->request($request);
        return $response;
    }
}