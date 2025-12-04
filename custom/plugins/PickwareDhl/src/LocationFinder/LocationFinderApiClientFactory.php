<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\LocationFinder;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Pickware\HttpUtils\Sanitizer\HeaderSanitizer;
use Pickware\HttpUtils\Sanitizer\HttpSanitizing;
use Pickware\ShippingBundle\Http\HttpLogger;
use Pickware\ShippingBundle\Rest\GuzzleLoggerMiddleware;
use Pickware\ShippingBundle\Rest\RestApiClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LocationFinderApiClientFactory
{
    private const PRODUCTION_BASE_URL = 'https://api.dhl.com/location-finder/v1/';
    private const API_KEY = 'tzhrLpwWvGAWHGpDrsPrGmk3ZmEfuuGV';

    public function __construct(
        #[Autowire(service: 'monolog.logger.pickware_dhl')]
        private readonly LoggerInterface $dhlRequestLogger,
    ) {}

    public function createLocationFinderApiClient(): RestApiClient
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(new GuzzleLoggerMiddleware(new HttpLogger(
            $this->dhlRequestLogger,
            new HttpSanitizing(HeaderSanitizer::createForDefaultAuthHeaders()),
        )));

        $restClient = $this->createRestClient([
            'base_uri' => self::PRODUCTION_BASE_URL,
            'handler' => $handlerStack,
            'headers' => ['DHL-API-Key' => self::API_KEY],
            'allow_redirects' => true,
        ]);

        return new RestApiClient($restClient);
    }

    protected function createRestClient(array $config): Client
    {
        return new Client($config);
    }
}
