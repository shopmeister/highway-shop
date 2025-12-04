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

use InvalidArgumentException;

class PreferredLocationServiceOption extends AbstractShipmentOrderOption
{
    private const MAX_LENGTH = 100;

    private string $preferredLocation;

    public function __construct(string $preferredLocation)
    {
        if (mb_strlen($preferredLocation) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'The value for the preferred neighbour service should not be longer than %s characters.',
                self::MAX_LENGTH,
            ));
        }

        $this->preferredLocation = $preferredLocation;
    }

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['preferredLocation'] = $this->preferredLocation;
    }
}
