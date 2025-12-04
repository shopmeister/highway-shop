<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\ReturnLabel\Response;

use JsonSerializable;
use Pickware\PickwareDhl\ReturnLabel\ReturnLocation;
use Pickware\ShippingBundle\Shipment\Country;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class GetAvailableReturnLocationsResponse implements JsonSerializable
{
    /**
     * @param ReturnLocation[] $returnLocations
     */
    private function __construct(private readonly array $returnLocations) {}

    public static function fromResponseInterface(ResponseInterface $response): self
    {
        $responseJson = json_decode((string) $response->getBody(), false, 512, JSON_THROW_ON_ERROR);

        $returnLocations = array_map(
            fn(stdClass $countryData) => new ReturnLocation(
                $countryData->receiverId,
                new Country($countryData->shipperCountry),
            ),
            $responseJson,
        );

        return new self($returnLocations);
    }

    /**
     * @return array<string, ReturnLocation> Key is country ISO2 code
     */
    public function getFirstReturnReceiverForAllCounties(): array
    {
        $isoCodeReceiverIdMap = [];
        foreach ($this->returnLocations as $returnLocation) {
            $iso2Code = $returnLocation->getShipperCountry()->getIso2Code();
            $isoCodeReceiverIdMap[$iso2Code] ??= $returnLocation;
        }

        return $isoCodeReceiverIdMap;
    }

    public function getReturnLocations(): array
    {
        return $this->returnLocations;
    }

    public function jsonSerialize(): array
    {
        return $this->returnLocations;
    }
}
