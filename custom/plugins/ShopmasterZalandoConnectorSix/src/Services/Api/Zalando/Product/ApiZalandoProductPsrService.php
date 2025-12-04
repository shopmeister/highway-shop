<?php

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product;


use GraphQL\RequestBuilder;
use GraphQL\RequestBuilder\Interfaces\TypeInterface;
use Monolog\Logger;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\RequestExceptions\MethodNameExceptions;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\ErrorException;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\SalesChannel\SalesChannelStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferCollection;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer\PsrProductOfferStruct;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductStruct;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;


class ApiZalandoProductPsrService
{

    private ConfigService $configService;
    private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService;
    private Logger $logger;
    private ClientService $clientService;
    private MessageBusInterface $messageBus;

    public function __construct(
        ConfigService                  $configService,
        ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        Logger                         $logger,
        ClientService                  $clientService,
        MessageBusInterface            $messageBus
    )
    {
        $this->configService = $configService;
        $this->apiZalandoSalesChannelsService = $apiZalandoSalesChannelsService;
        $this->logger = $logger->withName('ApiZalandoProductPsrService');
        $this->clientService = $clientService;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws ErrorException
     * @throws ClientException
     * @throws ResponseException
     */
    public function dispatchPsrForProcessing(MessagePsrInterface $bus): void
    {
        $this->chunkProducts($this->getQuery('', $bus->getLimit()), null, $bus);
    }
    
    /**
     * Get PSR data for a single product by EAN
     * 
     * @param string $ean
     * @return PsrProductStruct|null
     * @throws ClientException
     * @throws ErrorException
     * @throws ResponseException
     */
    public function getPsrByEan(string $ean): ?PsrProductStruct
    {
        $query = $this->getQuery('', 1, $ean);
        $response = $this->sendRequest($query);
        
        if (!$response->isSuccessStatus()) {
            $this->logger->error('PSR fetch failed', ['ean' => $ean, 'response' => $response->getContent()]);
            return null;
        }
        
        if (is_array($response->get('errors'))) {
            $this->logger->error('PSR errors', $response->get('errors'));
            return null;
        }
        
        $content = $response->getContentArray();
        $productModels = $content['data']['psr']['product_models'] ?? [];
        
        if (empty($productModels['items'])) {
            return null;
        }
        
        // Process the first matching item
        foreach ($productModels['items'] as $item) {
            foreach ($item['product_configs'] as $config) {
                foreach ($config['product_simples'] as $productSimple) {
                    if ($productSimple['ean'] === $ean) {
                        $struct = new PsrProductStruct();
                        $struct->setEan($productSimple['ean']);
                        
                        foreach ($productSimple['offers'] as $offer) {
                            if (!empty($offer['country']['code'])) {
                                $offerStruct = new PsrProductOfferStruct();
                                $offerStruct->setCountryCode($offer['country']['code'])
                                    ->setStock($offer['stock']['amount'] ?? null)
                                    ->setRegularPrice($offer['price']['regular_price']['amount'] ?? null)
                                    ->setDiscountedPrice($offer['price']['discounted_price']['amount'] ?? null);
                                $struct->addOffer($offerStruct);
                            }
                        }
                        
                        $this->addEmptyOffersIfNotHas($struct->getOffers());
                        return $struct;
                    }
                }
            }
        }
        
        return null;
    }


    /**
     * @param TypeInterface $query
     * @param PsrProductCollection|null $collection
     * @param MessagePsrInterface|null $bus
     * @return PsrProductCollection|null
     * @throws ClientException
     * @throws ErrorException
     * @throws ResponseException
     */
    protected function chunkProducts(RequestBuilder\Interfaces\TypeInterface $query, ?PsrProductCollection $collection = null, ?MessagePsrInterface $bus = null): PsrProductCollection
    {
        /**
         * sleep
         * @DocUrl https://developers.merchants.zalando.com/docs/psr-api.html#psr-api-rate-limiting
         */
        usleep(250000);

        if (!$collection) {
            $collection = new PsrProductCollection();
        }
        $response = $this->sendRequest($query);
        $response->isSuccessStatus(true);
        if (is_array($response->get('errors'))) {
            $this->logger->error('Psr error', $response->get('errors'));
            throw new ErrorException($response->getContent());
        }
        $content = $response->getContentArray();
        $productModels = $content['data']['psr']['product_models'];
        $tempCollection = new PsrProductCollection();
        foreach ($productModels['items'] as $item) {
            foreach ($item['product_configs'] as $config) {
                foreach ($config['product_simples'] as $productSimple) {
                    $struct = new PsrProductStruct();
                    $struct->setEan($productSimple['ean']);
                    foreach ($productSimple['offers'] as $offer) {
                        if (
                            isset($offer['stock']['amount'])
                            || !empty($offer['country']['code'])
                        ) {
                            $offerStruct = new PsrProductOfferStruct();
                            $offerStruct->setCountryCode($offer['country']['code'])
                                ->setStock($offer['stock']['amount'] ?? null)
                                ->setRegularPrice($offer['price']['regular_price']['amount'] ?? null)
                                ->setDiscountedPrice($offer['price']['discounted_price']['amount'] ?? null);
                            $struct->addOffer($offerStruct);
                        }
                    }
                    $this->addEmptyOffersIfNotHas($struct->getOffers());
                    $tempCollection->set($struct->getEan(), $struct);
                }
            }
        }
        if ($bus) {
            $this->messageBus->dispatch(
                (new Envelope($bus->setPsr($tempCollection)))->with(new DelayStamp(10000))
            );
        }
        $collection->merge($tempCollection);
        if (!empty($productModels['cursor'])) {
            $this->chunkProducts($this->getQuery($productModels['cursor'], $bus->getLimit()), $collection, $bus);
        }
        return $collection;
    }

    /**
     * @param PsrProductOfferCollection $offers
     * @return void
     */
    private function addEmptyOffersIfNotHas(PsrProductOfferCollection $offers)
    {
        /** @var SalesChannelStruct $channel */
        foreach ($this->apiZalandoSalesChannelsService->getCollection() as $channel) {
            if (!$channel->isLive()) {
                continue;
            }
            $offerIsHas = false;
            /** @var PsrProductOfferStruct $offer */
            foreach ($offers as $offer) {
                if (strtoupper($channel->getCountryCode()) === $offer->getCountryCode()) {
                    $offerIsHas = true;
                    break;
                }
            }
            if (!$offerIsHas) {
                $offerStruct = new PsrProductOfferStruct();
                $offerStruct->setCountryCode(strtoupper($channel->getCountryCode()));
                $offerStruct->setStock(null);
                $offerStruct->setRegularPrice(null);
                $offerStruct->setDiscountedPrice(null);
                $offers->set($offerStruct->getCountryCode(), $offerStruct);
            }
        }
    }

    /**
     * @param string $cursor
     * @param int $limit
     * @param string $search_value
     * @param array $country_codes
     * @param array $status_clusters
     * @param array $season_codes
     * @param array $brand_codes
     * @return RequestBuilder\Interfaces\TypeInterface
     */
    protected function getQuery(
        string $cursor = '',
        int    $limit = 100,
        string $search_value = '',
        array  $country_codes = [],
        array  $status_clusters = [],
        array  $season_codes = [],
        array  $brand_codes = []
    ): RequestBuilder\Interfaces\TypeInterface
    {
        $arguments = [
            new RequestBuilder\ArrayArgument('merchant_ids', [$this->configService->getZalandoApiConfig()->getMerchantId()]),
            new RequestBuilder\ArrayArgument('season_codes', $season_codes),
            new RequestBuilder\ArrayArgument('brand_codes', $brand_codes),
            new RequestBuilder\ArrayArgument('country_codes', $country_codes),
            new RequestBuilder\Argument('limit', $limit),
            new RequestBuilder\Argument('search_value', $search_value),
        ];

        if (!empty($cursor)) {
            $arguments[] = new RequestBuilder\Argument('cursor', $cursor);
        }

        if (!empty($status_clusters)) {
            $arguments[] = new RequestBuilder\EnumArgument('status_clusters', '[' . implode(',', $status_clusters) . ']');
        }

        return (new RequestBuilder\RootType('psr'))->addSubType(
            (new RequestBuilder\Type('product_models'))
                ->addArgument(new RequestBuilder\Argument('input', new RequestBuilder\ArrayArgument('', $arguments)))
                ->addSubType(new RequestBuilder\Type('cursor'))
                ->addSubType((new RequestBuilder\Type('items'))->addSubType(
                    (new RequestBuilder\Type('product_configs'))->addSubType(
                        (new RequestBuilder\Type('product_simples'))->addSubTypes([
                            'ean',
                            (new RequestBuilder\Type('offers'))->addSubTypes([
                                (new RequestBuilder\Type('country'))->addSubType('code'),
                                (new RequestBuilder\Type('stock'))->addSubType('amount'),
                                (new RequestBuilder\Type('price'))->addSubTypes([
                                    (new RequestBuilder\Type('regular_price'))->addSubType('amount'),
                                    (new RequestBuilder\Type('discounted_price'))->addSubType('amount'),
                                ])
                            ])
                        ])
                    )
                ))
        );
    }

    /**
     * @param TypeInterface $query
     * @return ResponseStruct
     * @throws MethodNameExceptions
     */
    protected function sendRequest(RequestBuilder\Interfaces\TypeInterface $query): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_POST)
            ->setUrl('/graphql')
            ->setContent(['query' => 'query ' . $query])
            ->setUseMerchantId(false);
        return $this->clientService->request($request);
    }
}