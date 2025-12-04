<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\LocationFinder\Requests;

use GuzzleHttp\Psr7\Request;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class FindLocationsByAddressRequest extends Request
{
    // We choose to use the same radius as on the "DHL location finder website"
    // (in some areas there cannot be found locations when using a smaller radius)
    private const STANDARD_RADIUS_IN_METERS = 15000;
    private const STANDARD_LIMIT = 50;
    private const STANDARD_PROVIDER = 'parcel';
    private const STANDARD_COUNTRY_CODE = 'DE';

    private ?string $postalCode;

    public function __construct(?string $postalCode = null)
    {
        parent::__construct(
            'GET',
            sprintf('find-by-address?%s', http_build_query(
                array_merge(
                    [
                        'countryCode' => self::STANDARD_COUNTRY_CODE,
                        'radius' => self::STANDARD_RADIUS_IN_METERS,
                        'limit' => self::STANDARD_LIMIT,
                        'providerType' => self::STANDARD_PROVIDER,
                    ],
                    $postalCode ? ['postalCode' => $postalCode] : [],
                ),
            )),
        );

        $this->postalCode = $postalCode;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }
}
