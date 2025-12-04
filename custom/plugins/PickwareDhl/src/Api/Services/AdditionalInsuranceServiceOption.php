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

class AdditionalInsuranceServiceOption extends AbstractShipmentOrderOption
{
    private float $insuranceAmountInEuro;

    public function __construct(float $insuranceAmountInEuro)
    {
        if ($insuranceAmountInEuro <= 0) {
            throw new InvalidArgumentException('Negative insurance amounts are not supported.');
        }

        $this->insuranceAmountInEuro = $insuranceAmountInEuro;
    }

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['additionalInsurance'] = [
            'currency' => 'EUR',
            'value' => sprintf('%01.2f', $this->insuranceAmountInEuro),
        ];
    }
}
