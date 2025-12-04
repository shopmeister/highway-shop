<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Adapter;

use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
readonly class PickwareShipmentDhlShipmentCreationItemMapping
{
    public function __construct(
        private string $pickwareShipmentId,
        private stdClass $dhlShipmentCreationItem,
    ) {}

    public function getPickwareShipmentId(): string
    {
        return $this->pickwareShipmentId;
    }

    public function getDhlShipmentCreationItem(): stdClass
    {
        return $this->dhlShipmentCreationItem;
    }
}
