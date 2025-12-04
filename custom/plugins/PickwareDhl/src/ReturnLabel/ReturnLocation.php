<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\ReturnLabel;

use JsonSerializable;
use Pickware\ShippingBundle\Shipment\Country;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class ReturnLocation implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private readonly Country $shipperCountry,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getShipperCountry(): Country
    {
        return $this->shipperCountry;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'shipperCountry' => $this->shipperCountry,
        ];
    }
}
