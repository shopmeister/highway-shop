<?php

namespace ShopmasterZalandoConnectorSix\Struct\HttpClient\Token;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class OAuthTokenStruct extends Struct
{
    /**
     * @var string
     */
    protected $accessToken;
    /**
     * @var int
     */
    protected $expiresIn;
    /**
     * @var string
     */
    protected $tokenType;

    /**
     * Calculated expiration date
     *
     * @var \DateTime
     */
    protected $expireDateTime;

    public function __construct(array $token)
    {
        $this->accessToken = $token['access_token'];
        $this->expiresIn = $token['expires_in'];
        $this->tokenType = $token['token_type'];
        $expirationDateTime = new \DateTime();
        $interval = \DateInterval::createFromDateString($this->expiresIn . ' seconds');
        $this->expireDateTime = $expirationDateTime->add($interval);
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return int
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * @return \DateTime
     */
    public function getExpireDateTime(): \DateTime
    {
        return $this->expireDateTime;
    }

}