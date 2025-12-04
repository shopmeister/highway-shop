<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Encoding;

interface EncodingInterface extends \Stringable
{
    public function __toString(): string;
}
