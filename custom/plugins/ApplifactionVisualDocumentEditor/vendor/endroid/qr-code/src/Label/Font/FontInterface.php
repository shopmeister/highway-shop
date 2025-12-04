<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Label\Font;

interface FontInterface
{
    public function getPath(): string;

    public function getSize(): int;
}
