<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer\Result;

use Dde\Endroid\QrCode\Matrix\MatrixInterface;

interface ResultInterface
{
    public function getMatrix(): MatrixInterface;

    public function getString(): string;

    public function getDataUri(): string;

    public function saveToFile(string $path): void;

    public function getMimeType(): string;
}
