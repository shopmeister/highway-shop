<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Api;

enum DhlParcelDeSubSystem
{
    case Parcels;
    case Returns;
    case Account;

    public function getTestUsername(): string
    {
        return match ($this) {
            self::Parcels, self::Account => 'user-valid',
            self::Returns => '2222222222_customer',
        };
    }

    public function getTestPassword(): string
    {
        return match ($this) {
            self::Parcels, self::Account => 'SandboxPasswort2023!',
            self::Returns => 'uBQbZ62!ZiBiVVbhc',
        };
    }

    public function getProductionEndpoint(): string
    {
        return match ($this) {
            self::Parcels => 'https://api-eu.dhl.com/parcel/de/shipping/v2/',
            self::Returns => 'https://api-eu.dhl.com/parcel/de/shipping/returns/v1/',
            self::Account => 'https://api-eu.dhl.com/parcel/de/account/myaccount/v1/',
        };
    }

    public function getTestEndpoint(): string
    {
        return match ($this) {
            self::Parcels => 'https://api-sandbox.dhl.com/parcel/de/shipping/v2/',
            self::Returns => 'https://api-sandbox.dhl.com/parcel/de/shipping/returns/v1/',
            self::Account => 'https://api-sandbox.dhl.com/parcel/de/account/myaccount/v1/',
        };
    }
}
