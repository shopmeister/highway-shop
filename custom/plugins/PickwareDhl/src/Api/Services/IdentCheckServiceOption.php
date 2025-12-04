<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api\Services;

use DateTime;

class IdentCheckServiceOption extends AbstractShipmentOrderOption
{
    public function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly ?DateTime $dateOfBirth,
        private readonly MinimumAge $minimumAge,
    ) {}

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['identCheck'] = [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'minimumAge' => sprintf('A%d', $this->minimumAge->getAsInt()),
        ];

        if ($this->dateOfBirth !== null) {
            $shipmentOrderArray['services']['identCheck']['dateOfBirth'] = $this->dateOfBirth->format('Y-m-d');
        }
    }
}
