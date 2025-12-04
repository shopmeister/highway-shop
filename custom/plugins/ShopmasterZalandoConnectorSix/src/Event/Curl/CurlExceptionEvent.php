<?php

namespace ShopmasterZalandoConnectorSix\Event\Curl;

use ShopmasterZalandoConnectorSix\Event\ZalandoEvent;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;


class CurlExceptionEvent extends ZalandoEvent
{
    /**
     * @var ResponseException
     */
    protected ResponseException $exception;
    /**
     * @var RequestStruct
     */
    protected RequestStruct $request;
    /**
     * @var ResponseStruct|null
     */
    protected ?ResponseStruct $response = null;

    /**
     * @return ResponseException
     */
    public function getException(): ResponseException
    {
        return $this->exception;
    }

    /**
     * @param ResponseException $exception
     * @return self
     */
    public function setException(ResponseException $exception): self
    {
        $this->exception = $exception;
        return $this;
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

    /**
     * @return ResponseStruct|null
     */
    public function getResponse(): ?ResponseStruct
    {
        return $this->response;
    }

    /**
     * @param ResponseStruct|null $response
     * @return self
     */
    public function setResponse(?ResponseStruct $response): self
    {
        $this->response = $response;
        return $this;
    }
}