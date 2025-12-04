<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Auth;

use Psr\Cache\InvalidArgumentException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\Token\OAuthTokenStruct;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ApiZalandoAuthService
{
    const CACHE_ID = 'zalando_auth_access_token';

    /**
     * @var ConfigService
     */
    private ConfigService $configService;
    /**
     * @var ClientService
     */
    private ClientService $clientService;
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $cache;
    /**
     * @var OAuthTokenStruct|null
     */
    private ?OAuthTokenStruct $authToken = null;

    /**
     * @param ConfigService $configService
     * @param ClientService $clientService
     * @param AdapterInterface $cache
     */
    public function __construct(
        ConfigService    $configService,
        ClientService    $clientService,
        AdapterInterface $cache
    )
    {
        $this->configService = $configService;
        $this->clientService = $clientService;
        $this->cache = $cache;
    }

    /**
     * @return OAuthTokenStruct
     * @throws ClientException
     * @throws InvalidArgumentException
     * @throws ResponseException
     */
    public function getAuthToken(): OAuthTokenStruct
    {
        if (!$this->authToken) {
            $this->authToken = $this->getAuthTokenFromCache();
        }
        if ($this->authToken->getExpireDateTime()->getTimestamp() < time()) {
            $this->reset();
            $this->authToken = $this->getAuthTokenFromCache();
        }
        return $this->authToken;
    }

    /**
     * @return string
     * @throws ClientException
     * @throws ResponseException|InvalidArgumentException
     */
    public function getAccessToken(): string
    {
        return $this->getAuthToken()->getAccessToken();
    }

    /**
     * @return OAuthTokenStruct
     * @throws ClientException
     * @throws ResponseException
     */
    private function getAuthTokenFromCache(): OAuthTokenStruct
    {
        return $this->cache->get(self::CACHE_ID, function (ItemInterface $item) {
            $request = new RequestStruct();
            $request->setMethodName($request::METHOD_POST)
                ->setUrl('/auth/token')
                ->setCurlOptUserPwd($this->configService->getZalandoApiConfig()->getClientId(), $this->configService->getZalandoApiConfig()->getClientSecret())
                ->setContentType('application/x-www-form-urlencoded')
                ->setContent("grant_type=client_credentials")
                ->setUseZalandoToken(false)
                ->setUseMerchantId(false);
            $response = $this->clientService->request($request);
            $response->isSuccessStatus(true);
            $token = new OAuthTokenStruct($response->getContentArray());

            $item->expiresAfter($token->getExpiresIn() - 10);

            return $token;
        });

    }


    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function reset(): void
    {
        $this->cache->delete(self::CACHE_ID);
        $this->authToken = null;
    }
}