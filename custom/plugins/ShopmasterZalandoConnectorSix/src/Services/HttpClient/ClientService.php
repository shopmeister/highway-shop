<?php

namespace ShopmasterZalandoConnectorSix\Services\HttpClient;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Event\HttpClient\HttpClientRequestEvent;
use ShopmasterZalandoConnectorSix\Event\HttpClient\HttpClientResponseEvent;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Auth\ApiZalandoAuthService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\HttpClient\Curl\Client;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


class ClientService extends Client
{
    private ContainerInterface $container;
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        LoggerInterface          $logger,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->setLogger($logger);
        $this->eventDispatcher = $eventDispatcher;
    }

//    /**
//     * @param RequestStruct $request
//     * @return ResponseStruct
//     * @throws MethodNameExceptions
//     */
//    public function request(RequestStruct $request): ResponseStruct
//    {
//        $this->eventDispatcher->dispatch(new HttpClientRequestEvent($request));
//        if (!$request->getMethodName()) {
//            throw new MethodNameExceptions('Method name is mandatory');
//        }
//        $response = $this->{$request->getMethodName()}($request);
//        $this->eventDispatcher->dispatch(new HttpClientResponseEvent($response));
//        return $response;
//    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function get(RequestStruct $request): ResponseStruct
    {
        $this->configToRequest($request);
        return parent::get($request->setMethodName($request::METHOD_GET))->setRequest($request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function post(RequestStruct $request): ResponseStruct
    {
        $this->configToRequest($request);
        return parent::post($request->setMethodName($request::METHOD_POST))->setRequest($request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function put(RequestStruct $request): ResponseStruct
    {
        $this->configToRequest($request);
        return parent::put($request->setMethodName($request::METHOD_PUT))->setRequest($request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function patch(RequestStruct $request): ResponseStruct
    {
        $this->configToRequest($request);
        return parent::patch($request->setMethodName($request::METHOD_PATCH))->setRequest($request);
    }

    /**
     * @param RequestStruct $request
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     */
    public function delete(RequestStruct $request): ResponseStruct
    {
        $this->configToRequest($request);
        return parent::delete($request->setMethodName($request::METHOD_DELETE))->setRequest($request);
    }

    /**
     * @param RequestStruct $request
     * @return void
     * @throws ClientException
     * @throws ResponseException
     */
    private function configToRequest(RequestStruct $request): void
    {
        $zalandoApiConfig = $this->container->get(ConfigService::class)->getZalandoApiConfig();
        $request->setMerchantId($zalandoApiConfig->getMerchantId());
        if (empty($request->getBaseUrl())) {
            $request->setBaseUrl($zalandoApiConfig->getClientBaseUrl());
        }
        if ($request->isUseZalandoToken()) {
            $apiZalandoAuthService = $this->container->get(ApiZalandoAuthService::class);
            $request->setZOAuthToken($apiZalandoAuthService->getAccessToken());
        }
    }

    /**
     * @internal
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $this->container = $container;
        return $this->container;
    }
}