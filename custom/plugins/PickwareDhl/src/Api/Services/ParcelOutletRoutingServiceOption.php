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

use Pickware\PickwareDhl\Api\DhlApiClientException;

/**
 * ParcelOutletRouting - Wenn aktiviert, wird das Paket bei Nicht-Zustellbarkeit zuerst an eine nahegelegene Filiale
 * übergeben, wo der Kunde dann eine zweite Chance bekommt es entgegenzunehmen. Verringert Chance auf Rücksendung.
 */
class ParcelOutletRoutingServiceOption extends AbstractShipmentOrderOption
{
    public function __construct(private readonly string $email)
    {
        if ($this->email === '') {
            throw DhlApiClientException::emailAddressMissingForParcelOutletRouting();
        }
    }

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }

        $shipmentOrderArray['services']['parcelOutletRouting'] = $this->email;
    }
}
