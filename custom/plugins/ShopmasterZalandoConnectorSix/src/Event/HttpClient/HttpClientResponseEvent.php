<?php

namespace ShopmasterZalandoConnectorSix\Event\HttpClient;

use ShopmasterZalandoConnectorSix\Event\ZalandoEvent;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class HttpClientResponseEvent extends ZalandoEvent
{
    private ResponseStruct $response;

    public function __construct(ResponseStruct $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseStruct
     */
    public function getResponse(): ResponseStruct
    {
        return $this->response;
    }

    /**
     * @param ResponseStruct $response
     * @return self
     */
    public function setResponse(ResponseStruct $response): self
    {
        $this->response = $response;
        return $this;
    }
}