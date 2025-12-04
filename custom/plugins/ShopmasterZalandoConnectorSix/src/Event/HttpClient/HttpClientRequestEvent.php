<?php

namespace ShopmasterZalandoConnectorSix\Event\HttpClient;

use ShopmasterZalandoConnectorSix\Event\ZalandoEvent;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;

class HttpClientRequestEvent extends ZalandoEvent
{
    protected RequestStruct $request;

    public function __construct(RequestStruct $request)
    {
        $this->request = $request;
    }

    /**
     * @return RequestStruct
     */
    public function getRequest(): RequestStruct
    {
        return $this->request;
    }

    /**
     * @param RequestStruct $request
     * @return self
     */
    public function setRequest(RequestStruct $request): self
    {
        $this->request = $request;
        return $this;
    }
}