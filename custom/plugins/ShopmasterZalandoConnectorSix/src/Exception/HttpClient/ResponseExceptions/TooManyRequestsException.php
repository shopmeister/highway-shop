<?php

namespace ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions;


use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;

class TooManyRequestsException extends ResponseException
{
    private int $retryAfter;

    public function __construct(int $retryAfter, $message = "", $code = 0, ?\Exception $previous = null)
    {
        $this->retryAfter = $retryAfter;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * @param int $retryAfter
     */
    public function setRetryAfter(int $retryAfter): void
    {
        $this->retryAfter = $retryAfter;
    }
}