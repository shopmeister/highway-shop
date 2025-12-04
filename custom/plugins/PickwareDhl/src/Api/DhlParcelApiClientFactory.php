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
use Pickware\DalBundle\EntityManager;
use Pickware\HttpUtils\Sanitizer\HeaderSanitizer;
use Pickware\HttpUtils\Sanitizer\HttpSanitizing;
use Pickware\PickwareDhl\Api\Handler\LocaleHeaderMiddleware;
use Pickware\PickwareDhl\Api\Handler\OAuthMiddleware;
use Pickware\ShippingBundle\Authentication\PrivateFileSystemCachedTokenRetriever;
use Pickware\ShippingBundle\Http\HttpLogger;
use Pickware\ShippingBundle\Rest\BadResponseExceptionHandlingMiddleware;
use Pickware\ShippingBundle\Rest\GuzzleLoggerMiddleware;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DhlParcelApiClientFactory
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.pickware_dhl')]
        private readonly LoggerInterface $dhlRequestLogger,
        #[Autowire(service: 'pickware-dhl.private_file_system_cached_token_retriever')]
        private readonly PrivateFileSystemCachedTokenRetriever $privateFileSystemCachedTokenRetriever,
        private readonly EntityManager $entityManager,
    ) {}

    public function createParcelApiClient(
        DhlApiClientConfig $dhlApiConfig,
        DhlParcelDeSubSystem $subSystem,
        Context $context,
    ): Client {
        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(new GuzzleLoggerMiddleware(new HttpLogger(
            $this->dhlRequestLogger,
            new HttpSanitizing(HeaderSanitizer::createForDefaultAuthHeaders()),
        )));
        $handlerStack->unshift(new BadResponseExceptionHandlingMiddleware(
            DhlApiClientException::fromClientException(...),
            DhlApiClientException::fromServerException(...),
        ));
        $handlerStack->unshift(new LocaleHeaderMiddleware($this->entityManager, $context));
        $useTestingEndpoint = $dhlApiConfig->shouldUseTestingEndpoint();

        $handlerStack->unshift(
            new OAuthMiddleware(
                dhlApiClientConfig: $dhlApiConfig,
                tokenRetriever: $this->privateFileSystemCachedTokenRetriever,
            ),
        );

        return $this->createRestClient([
            'base_uri' => $useTestingEndpoint ? $subSystem->getTestEndpoint() : $subSystem->getProductionEndpoint(),
            'handler' => $handlerStack,
        ]);
    }

    protected function createRestClient(array $config): Client
    {
        return new Client($config);
    }
}
