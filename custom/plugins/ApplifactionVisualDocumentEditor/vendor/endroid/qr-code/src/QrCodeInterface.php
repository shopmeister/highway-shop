<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode;

use Dde\Endroid\QrCode\Color\ColorInterface;
use Dde\Endroid\QrCode\Encoding\EncodingInterface;

interface QrCodeInterface
{
    public function getData(): string;

    public function getEncoding(): EncodingInterface;

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel;

    public function getSize(): int;

    public function getMargin(): int;

    public function getRoundBlockSizeMode(): RoundBlockSizeMode;

    public function getForegroundColor(): ColorInterface;

    public function getBackgroundColor(): ColorInterface;
}
