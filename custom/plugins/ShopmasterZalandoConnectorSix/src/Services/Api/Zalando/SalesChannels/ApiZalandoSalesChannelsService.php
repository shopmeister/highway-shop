<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels;

use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\ErrorException;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelCollection;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ApiZalandoSalesChannelsService
{

    public function __construct(
        private readonly ClientService    $clientService,
        private readonly ConfigService    $configService,
        private readonly AdapterInterface $cache
    )
    {
    }

    /**
     * @return SalesChannelCollection
     * @throws ErrorException
     * @throws MethodNameExceptions
     */
    public function getCollection(): SalesChannelCollection
    {
        $response = $this->all();
        $items = $response['items'];
        $collection = new SalesChannelCollection($items);
        $collection->setResponse($response);
        return $collection;
    }

    /**
     * @return ResponseStruct
     * @throws MethodNameExceptions
     * @throws ErrorException
     */
    public function all(): ResponseStruct
    {
        return $this->cache->get('zToken_' . md5(self::class . __FUNCTION__), function (ItemInterface $item) {
            $item->expiresAfter(5 * 60);
            $request = new RequestStruct();
            $request->setUseMerchantId(false)
                ->setUrl('/sales-channels')
                ->setQuery(['merchant_ids' => $this->configService->getZalandoApiConfig()->getMerchantId()])
                ->setMethodName($request::METHOD_GET);
            $response = $this->clientService->request($request);
            $response->isSuccessStatus(true);
            return $response;
        });
    }
}