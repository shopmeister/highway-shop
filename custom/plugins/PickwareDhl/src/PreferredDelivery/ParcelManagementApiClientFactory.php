<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\PreferredDelivery;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Pickware\HttpUtils\Sanitizer\HeaderSanitizer;
use Pickware\HttpUtils\Sanitizer\HttpSanitizing;
use Pickware\PickwareDhl\Api\DhlApiClientConfig;
use Pickware\ShippingBundle\Http\HttpLogger;
use Pickware\ShippingBundle\Rest\GuzzleLoggerMiddleware;
use Pickware\ShippingBundle\Rest\RestApiClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ParcelManagementApiClientFactory
{
    private const BASE_URL_TESTING = 'https://cig.dhl.de/services/sandbox/rest/';
    private const BASE_URL_PRODUCTION = 'https://cig.dhl.de/services/production/rest/';

    public function __construct(
        #[Autowire(service: 'monolog.logger.pickware_dhl')]
        private readonly LoggerInterface $dhlRequestLogger,
        #[Autowire('%env(default:productionUserFallback:PICKWARE_DHL_APP_ID)%')]
        private readonly string $productionUser,
        #[Autowire('%env(default:productionPasswordFallback:PICKWARE_DHL_TOKEN)%')]
        private readonly string $productionPassword,
    ) {}

    public function createDhlParcelManagementApiClient(
        DhlApiClientConfig $dhlApiClientConfig,
        string $customerNumber,
    ): RestApiClient {
        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(new GuzzleLoggerMiddleware(new HttpLogger(
            $this->dhlRequestLogger,
            new HttpSanitizing(HeaderSanitizer::createForDefaultAuthHeaders()),
        )));

        $useTestingEndpoint = $dhlApiClientConfig->shouldUseTestingEndpoint();

        $restClient = $this->createRestClient([
            'base_uri' => $useTestingEndpoint ? self::BASE_URL_TESTING : self::BASE_URL_PRODUCTION,
            'auth' => [
                $useTestingEndpoint ? $dhlApiClientConfig->getUsername() : $this->productionUser,
                $useTestingEndpoint ? $dhlApiClientConfig->getPassword() : $this->productionPassword,
            ],
            'handler' => $handlerStack,
            'headers' => [
                'X-EKP' => $customerNumber,
            ],
            'allow_redirects' => true,
        ]);

        return new RestApiClient($restClient);
    }

    protected function createRestClient(array $config): Client
    {
        return new Client($config);
    }
}
