<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\AddressCorrecting;

use Pickware\ShippingBundle\AddressCorrecting\AddressCorrecting;
use Pickware\ShippingBundle\Shipment\Address;

class PostalOutletAddressCorrection implements AddressCorrecting
{
    public function correctAddress(Address $address): Address
    {
        if (
            $address->getStreet() === 'Deutsche Post Filiale'
            && preg_match('/^(Postnummer )?([0-9]{6,10})$/', $address->getAddressAddition(), $number)
        ) {
            $correctedAddress = $address->copy();
            $correctedAddress->setStreet('Postfiliale');
            $correctedAddress->setAddressAddition($number[2]);

            return $correctedAddress;
        }

        return $address;
    }
}
