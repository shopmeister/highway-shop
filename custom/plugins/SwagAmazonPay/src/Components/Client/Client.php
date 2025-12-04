<?php
declare(strict_types=1);


namespace Swag\AmazonPay\Components\Client;

use Psr\Log\LoggerInterface;

class Client extends \AmazonPayApiSdkExtension\Client\Client
{
    private LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function apiCall($method, $urlFragment, $payload, $headers = null, $queryParams = null): array
    {
        $this->logger->debug('REQUEST @' . \mb_substr($urlFragment, 0, 200) . ' (' . $method . ')', [
            'payload' => $payload,
            'headers' => $headers,
            'queryParams' => $queryParams,
        ]);
        $response = parent::apiCall($method, $urlFragment, $payload, $headers, $queryParams);

        $this->logger->debug('RESPONSE @' . \mb_substr($urlFragment, 0, 200) . ' (' . $method . ') / Status: ' . $response['status'], [
            'response' => $response,
        ]);

        return $response;
    }
}
