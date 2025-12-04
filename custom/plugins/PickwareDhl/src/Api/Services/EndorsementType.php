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

enum EndorsementType: String
{
    case Immediate = 'RETURN';
    case Abandonment = 'ABANDON';

    public static function getFromConfig(string $configValue): self
    {
        return match ($configValue) {
            'IMMEDIATE' => self::Immediate,
            'ABANDONMENT' => self::Abandonment,
            default => throw new InvalidArgumentException(sprintf('Type %s is not supported by service "Endorsement"', $configValue)),
        };
    }

    public function getApiValue(): string
    {
        return $this->value;
    }
}
