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

/**
 * Service endorsement is used to specify handling if recipient not met. There are two options: IMMEDIATE (Sending back
 * to sender), ABANDONMENT (Abandonment of parcel at the hands of sender (free of charge))
 */
class EndorsementServiceOption extends AbstractShipmentOrderOption
{
    public function __construct(private readonly EndorsementType $type) {}

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['endorsement'] = $this->type->getApiValue();
    }
}
