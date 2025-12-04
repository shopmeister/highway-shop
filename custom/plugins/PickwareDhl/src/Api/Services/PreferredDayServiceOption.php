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

use DateTimeInterface;

class PreferredDayServiceOption extends AbstractShipmentOrderOption
{
    private const DATE_FORMAT = 'Y-m-d';

    public function __construct(private readonly DateTimeInterface $preferredDay) {}

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['preferredDay'] = $this->preferredDay->format(self::DATE_FORMAT);
    }
}
