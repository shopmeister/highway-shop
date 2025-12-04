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

use Pickware\PickwareDhl\Api\DhlBillingInformation;
use Pickware\PickwareDhl\Api\DhlProduct;

/**
 * "Beilegretoure"
 */
class EnclosedReturnLabelOption extends AbstractShipmentOrderOption
{
    public function __construct(private readonly DhlBillingInformation $dhlBillingInformation) {}

    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        $shipmentOrderArray['services']['dhlRetoure'] = [
            'billingNumber' => $this->dhlBillingInformation->getBillingNumberForProduct(
                DhlProduct::getReturnProduct(),
            ),
            'returnAddress' => $shipmentOrderArray['shipper'],
        ];
    }
}
