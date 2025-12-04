<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Pickware\HttpUtils\Sanitizer\HttpSanitizing;
use Pickware\ShippingBundle\Http\HttpLogger;
use Pickware\ShippingBundle\Rest\BadResponseExceptionHandlingMiddleware;
use Pickware\ShippingBundle\Rest\GuzzleLoggerMiddleware;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DhlParcelOAuthApiClientFactory
{
    private const BASE_URL_TESTING = 'https://api-sandbox.dhl.com/parcel/de/account/auth/ropc/v1/';
    private const BASE_URL_PRODUCTION = 'https://api-eu.dhl.com/parcel/de/account/auth/ropc/v1/';

    public function __construct(
        #[Autowire(service: 'monolog.logger.pickware_dhl')]
        private readonly LoggerInterface $dhlRequestLogger,
    ) {}

    public function createDhlParcelOAuthApiClient(bool $useTestingEndpoint): Client
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(new GuzzleLoggerMiddleware(new HttpLogger(
            $this->dhlRequestLogger,
            new HttpSanitizing(new OAuthBodySanitizer()),
        )));
        $handlerStack->unshift(new BadResponseExceptionHandlingMiddleware(
            DhlApiClientException::fromClientException(...),
            DhlApiClientException::fromServerException(...),
        ));

        return $this->createRestClient([
            'base_uri' => $useTestingEndpoint ? self::BASE_URL_TESTING : self::BASE_URL_PRODUCTION,
            'handler' => $handlerStack,
        ]);
    }

    protected function createRestClient(array $config): Client
    {
        return new Client($config);
    }
}
