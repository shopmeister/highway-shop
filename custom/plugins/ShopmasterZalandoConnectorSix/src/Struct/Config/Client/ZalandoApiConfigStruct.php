<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Client;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class ZalandoApiConfigStruct extends Struct
{
    const SANDBOX_URL = 'https://api-sandbox.merchants.zalando.com';
    const LIVE_URL = 'https://api.merchants.zalando.com';

    protected string $clientId;
    protected string $clientSecret;
    protected string $merchantId;
    protected bool $sandbox = false;

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandbox(bool $sandbox): void
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return string
     */
    public function getClientBaseUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_URL : self::LIVE_URL;
    }

}