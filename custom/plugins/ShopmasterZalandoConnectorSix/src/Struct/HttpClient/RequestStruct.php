<?php

namespace ShopmasterZalandoConnectorSix\Struct\HttpClient;

use ShopmasterZalandoConnectorSix\Struct\HttpClient\Token\OAuthTokenStruct;
use ShopmasterZalandoConnectorSix\Struct\Struct;

class RequestStruct extends Struct
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_PUT = 'put';
    const METHOD_PATCH = 'patch';
    const METHOD_DELETE = 'delete';
    const METHODS_NAME = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
        self::METHOD_DELETE,
    ];
    /**
     * @var null|string
     */
    private ?string $zOAuthToken = null;
    /**
     * @var bool
     */
    private bool $useZalandoToken = true;
    /**
     * @var bool
     */
    private bool $useMerchantId = true;


    /**
     * @var null|string
     */
    protected ?string $baseUrl = null;
    /**
     * @var string
     */
    protected string $url = '';
    /**
     * @var string|null
     */
    protected ?string $link = null;
    /**
     * @var string|null
     */
    protected ?string $merchantId = null;
    /**
     * @var array
     */
    protected array $query = [];
    /**
     * @var string
     */
    protected string $content = '';
    /**
     * @var string
     */
    protected string $contentType = 'application/json';
    /**
     * @var array|null
     */
    private ?array $curlOptUserPwd = null;
    /**
     * @var ResponseStruct|null
     */
    protected ?ResponseStruct $response;
    /**
     * @var string|null
     */
    protected ?string $methodName;

    public function __construct(?string $methodName = null)
    {
        if ($methodName) {
            $this->setMethodName($methodName);
        }
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = trim($url, '/');
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }


    /**
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param array $paths
     * @return $this
     */
    public function setPaths(array $paths): self
    {
        $this->url = implode('/', $paths);
        return $this;
    }

    /**
     * @param string $key
     * @param $val
     * @return $this
     */
    public function addQuery(string $key, $val): self
    {
        $this->query[$key] = $val;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        if ($this->link) {
            return $this->link;
        }
        $relativeUrl = $this->getUrl() . (!empty($this->getQuery()) ? '?' . http_build_query($this->getQuery()) : '');
        if ($this->isUseMerchantId() && $this->getMerchantId()) {
            $relativeUrl = "merchants/{$this->getMerchantId()}/$relativeUrl";
        }
        return $this->getBaseUrl() . '/' . $relativeUrl;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param string|null $baseUrl
     * @return $this
     */
    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = trim($baseUrl, '/');
        return $this;
    }


    /**
     * @param bool $useZalandoToken
     * @return $this
     */
    public function setUseZalandoToken(bool $useZalandoToken): self
    {
        $this->useZalandoToken = $useZalandoToken;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseZalandoToken(): bool
    {
        return $this->useZalandoToken;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string|array|object $content
     * @return $this
     */
    public function setContent($content): self
    {
        if (is_string($content)) {
            $this->content = $content;
        } elseif (is_array($content)) {
            $this->content = json_encode($content);
        } elseif (is_object($content)) {
            $this->content = (string)$content;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurlOptUserPwd(): ?string
    {
        return is_array($this->curlOptUserPwd) ? $this->curlOptUserPwd['user'] . ':' . $this->curlOptUserPwd['pass'] : null;
    }

    /**
     * @param string $user
     * @param string $pass
     * @return $this
     */
    public function setCurlOptUserPwd(string $user, string $pass): self
    {
        $this->curlOptUserPwd['user'] = $user;
        $this->curlOptUserPwd['pass'] = $pass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getZOAuthToken(): ?string
    {
        return $this->zOAuthToken;
    }

    /**
     * @param string|null $zOAuthToken
     * @return $this
     */
    public function setZOAuthToken(?string $zOAuthToken): self
    {
        $this->zOAuthToken = $zOAuthToken;
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
     * @return $this
     */
    public function setResponse(?ResponseStruct $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseMerchantId(): bool
    {
        return $this->useMerchantId;
    }

    /**
     * @param bool $useMerchantId
     * @return $this
     */
    public function setUseMerchantId(bool $useMerchantId): self
    {
        $this->useMerchantId = $useMerchantId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }

    /**
     * @param string|null $merchantId
     * @return $this
     */
    public function setMerchantId(?string $merchantId): self
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * @param string|null $link
     * @return $this
     */
    public function setLink(?string $link): self
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * @param string|null $methodName
     * @return self
     */
    public function setMethodName(?string $methodName): self
    {
        if (in_array($methodName, self::METHODS_NAME)) {
            $this->methodName = $methodName;
        }
        return $this;
    }

}