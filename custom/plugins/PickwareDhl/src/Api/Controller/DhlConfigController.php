<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api\Controller;

use Pickware\PickwareDhl\Account\CheckCredentialsResult;
use Pickware\PickwareDhl\Account\DhlContractData;
use Pickware\PickwareDhl\Account\DhlContractDataBookedProduct;
use Pickware\PickwareDhl\Account\Request\UserRequest;
use Pickware\PickwareDhl\Api\DhlApiClientConfig;
use Pickware\PickwareDhl\Api\DhlApiClientException;
use Pickware\PickwareDhl\Api\DhlParcelApiClientFactory;
use Pickware\PickwareDhl\Api\DhlParcelDeSubSystem;
use Pickware\PickwareDhl\Api\Requests\CreateLabelsRequest;
use Pickware\PickwareDhl\ReturnLabel\Request\GetAvailableReturnLocationsRequest;
use Pickware\PickwareDhl\ReturnLabel\Response\GetAvailableReturnLocationsResponse;
use Pickware\ValidationBundle\Annotation\JsonParameter;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(defaults: ['_routeScope' => ['api']])]
class DhlConfigController
{
    public function __construct(
        private readonly DhlParcelApiClientFactory $apiClientFactory,
    ) {}

    #[Route(path: '/api/_action/pickware-dhl/check-dhl-bcp-credentials', methods: ['Post'])]
    public function checkDhlBcpCredentials(
        #[JsonParameter] string $username,
        #[JsonParameter] string $password,
        Context $context,
    ): JsonResponse {
        $config = new DhlApiClientConfig(
            username: $username,
            password: $password,
            useTestingEndpoint: false,
        );

        $apiClient = $this->apiClientFactory->createParcelApiClient(
            $config,
            DhlParcelDeSubSystem::Parcels,
            $context,
        );

        try {
            $apiClient->send(new CreateLabelsRequest(shipments: [], mustEncode: false));
        } catch (DhlApiClientException $e) {
            if ($e->getPrevious()->getResponse()->getStatusCode() === 401) {
                $credentialsCheckResult = CheckCredentialsResult::credentialsAreInvalid();
            } elseif ($e->getPrevious()->getResponse()->getStatusCode() === 400) {
                $credentialsCheckResult = CheckCredentialsResult::credentialsAreValid();
            } else {
                throw $e;
            }
        }

        return new JsonResponse($credentialsCheckResult);
    }

    #[Route(path: '/api/_action/pickware-dhl/fetch-dhl-contract-data', methods: ['Post'])]
    public function fetchDhlContractData(
        #[JsonParameter] string $username,
        #[JsonParameter] string $password,
        #[JsonParameter] bool $useTestingEndpoint,
        Context $context,
    ): JsonResponse {
        $subSystem = DhlParcelDeSubSystem::Account;

        $config = new DhlApiClientConfig(
            username: $useTestingEndpoint ? $subSystem->getTestUsername() : $username,
            password: $useTestingEndpoint ? $subSystem->getTestPassword() : $password,
            useTestingEndpoint: $useTestingEndpoint,
        );

        $apiClient = $this->apiClientFactory->createParcelApiClient(
            $config,
            $subSystem,
            $context,
        );

        $response = $apiClient->send(new UserRequest());

        $responseJson = json_decode((string) $response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $bookedProducts = DhlContractDataBookedProduct::createFromMyAccountApi($responseJson);
        if (count($bookedProducts) > 0) {
            $customerNumber = mb_substr($bookedProducts[0]->getBillingNumbers()[0], 0, 10);
        }

        $contractData = new DhlContractData(
            $customerNumber ?? '',
            $bookedProducts,
        );

        return new JsonResponse($contractData);
    }

    #[Route(path: '/api/_action/pickware-dhl/fetch-dhl-return-receivers', methods: ['Post'])]
    public function fetchDhlReturnReceivers(
        #[JsonParameter] string $username,
        #[JsonParameter] string $password,
        #[JsonParameter] bool $useTestingEndpoint,
        Context $context,
    ): JsonResponse {
        $subSystem = DhlParcelDeSubSystem::Returns;

        $config = new DhlApiClientConfig(
            username: $useTestingEndpoint ? $subSystem->getTestUsername() : $username,
            password: $useTestingEndpoint ? $subSystem->getTestPassword() : $password,
            useTestingEndpoint: $useTestingEndpoint,
        );
        $apiClient = $this->apiClientFactory->createParcelApiClient(
            $config,
            $subSystem,
            $context,
        );

        return new JsonResponse(
            GetAvailableReturnLocationsResponse::fromResponseInterface(
                $apiClient->send(new GetAvailableReturnLocationsRequest()),
            )->getFirstReturnReceiverForAllCounties(),
        );
    }
}
