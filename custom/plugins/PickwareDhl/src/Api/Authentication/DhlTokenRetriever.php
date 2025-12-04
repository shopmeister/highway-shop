<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api\Authentication;

use DateInterval;
use Pickware\PickwareDhl\Api\DhlApiClientConfig;
use Pickware\PickwareDhl\Api\DhlParcelOAuthApiClientFactory;
use Pickware\PickwareDhl\Api\Requests\OAuthTokenRequest;
use Pickware\ShippingBundle\Authentication\Credentials;
use Pickware\ShippingBundle\Authentication\Token;
use Pickware\ShippingBundle\Authentication\TokenRetriever;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DhlTokenRetriever implements TokenRetriever
{
    public function __construct(
        private readonly DhlParcelOAuthApiClientFactory $apiClientFactory,
        private readonly ClockInterface $clock,
        #[Autowire('%env(default:parcelApiKey:PICKWARE_DHL_PARCEL_API_KEY)%')]
        private readonly string $parcelApiKey,
        #[Autowire('%env(default:parcelApiSecret:PICKWARE_DHL_PARCEL_API_SECRET)%')]
        private readonly string $parcelApiSecret,
    ) {}

    public function retrieveToken(Credentials $credentials): Token
    {
        /** @var DhlApiClientConfig $credentials */
        $dhlApiClient = $this->apiClientFactory->createDhlParcelOAuthApiClient(
            $credentials->shouldUseTestingEndpoint(),
        );

        $response = $dhlApiClient->send(
            new OAuthTokenRequest(
                $this->parcelApiKey,
                $this->parcelApiSecret,
                $credentials->getUsername(),
                $credentials->getPassword(),
            ),
        );
        $responseJson = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $tokenEndTime = $this->clock->now()->add(new DateInterval('PT' . $responseJson['expires_in'] . 'S'));

        return new Token(
            $responseJson['access_token'],
            $this->clock->now(),
            $tokenEndTime,
        );
    }
}
