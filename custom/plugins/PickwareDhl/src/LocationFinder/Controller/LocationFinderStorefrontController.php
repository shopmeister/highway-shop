<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\LocationFinder\Controller;

use Pickware\HttpUtils\ResponseFactory;
use Pickware\PickwareDhl\LocationFinder\LocationFinderApiClientFactory;
use Pickware\PickwareDhl\LocationFinder\LocationFinderResponseProcessor;
use Pickware\PickwareDhl\LocationFinder\Requests\FindLocationsByAddressRequest;
use Pickware\PickwareDhl\LocationFinder\Requests\FindLocationsByCoordinatesRequest;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(defaults: ['_routeScope' => ['storefront']])]
class LocationFinderStorefrontController extends StorefrontController
{
    public function __construct(
        private readonly LocationFinderApiClientFactory $locationFinderApiClientFactory,
        private readonly LocationFinderResponseProcessor $locationFinderResponseProcessor,
    ) {}

    #[Route(
        path: '/pickware-dhl/location-finder/locations',
        name: 'pickware-dhl.frontend.location-finder.locations',
        options: ['seo' => false],
        defaults: ['XmlHttpRequest' => true],
        methods: ['GET'],
    )]
    public function getLocations(Request $request): Response
    {
        $locationFinderApiClient = $this->locationFinderApiClientFactory->createLocationFinderApiClient();

        $latitude = (float) $request->get('latitude');
        $longitude = (float) $request->get('longitude');

        if ($latitude && $longitude) {
            $dhlApiRequest = new FindLocationsByCoordinatesRequest(
                $latitude,
                $longitude,
                $request->get('radiusInMeters') ? (int) $request->get('radiusInMeters') : null,
            );
        } else {
            if (!$request->get('zipcode')) {
                return ResponseFactory::createParameterMissingResponse('zipcode');
            }

            $dhlApiRequest = new FindLocationsByAddressRequest($request->get('zipcode'));
        }

        $locations = $this->locationFinderResponseProcessor->processResponse(
            $locationFinderApiClient->sendRequest($dhlApiRequest),
            $request->get('allowedLocationType'),
        );

        return new JsonResponse($locations);
    }
}
