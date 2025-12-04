<?php

namespace ShopmasterZalandoConnectorSix\Services\HttpClient;

use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

interface ClientInterface
{
    public function request(RequestStruct $request): ResponseStruct;

    public function get(RequestStruct $request): ResponseStruct;

    public function post(RequestStruct $request): ResponseStruct;

    public function put(RequestStruct $request): ResponseStruct;

    public function patch(RequestStruct $request): ResponseStruct;

    public function delete(RequestStruct $request): ResponseStruct;


}