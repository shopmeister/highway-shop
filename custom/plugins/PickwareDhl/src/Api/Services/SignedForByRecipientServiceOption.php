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
 * SignedForByRecipient - Wenn aktiviert, wird das Paket per Unterschrift durch den Empfänger anstelle des DHL Fahrers
 * bestätigt.
 */
class SignedForByRecipientServiceOption extends AbstractShipmentOrderOption
{
    public function applyToShipmentArray(array &$shipmentOrderArray): void
    {
        if (!isset($shipmentOrderArray['services'])) {
            $shipmentOrderArray['services'] = [];
        }
        $shipmentOrderArray['services']['signedForByRecipient'] = true;
    }
}
