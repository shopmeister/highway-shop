<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Account;

use JsonSerializable;

class CheckCredentialsResult implements JsonSerializable
{
    private bool $areCredentialsValid;

    private function __construct() {}

    public function jsonSerialize(): array
    {
        return [
            'areCredentialsValid' => $this->areCredentialsValid,
        ];
    }

    public static function credentialsAreValid(): self
    {
        $self = new self();
        $self->areCredentialsValid = true;

        return $self;
    }

    public static function credentialsAreInvalid(): self
    {
        $self = new self();
        $self->areCredentialsValid = false;

        return $self;
    }

    public function areCredentialsValid(): bool
    {
        return $this->areCredentialsValid;
    }
}
