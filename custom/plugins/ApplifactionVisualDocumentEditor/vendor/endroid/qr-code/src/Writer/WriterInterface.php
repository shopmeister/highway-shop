<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer;

use Dde\Endroid\QrCode\Label\LabelInterface;
use Dde\Endroid\QrCode\Logo\LogoInterface;
use Dde\Endroid\QrCode\QrCodeInterface;
use Dde\Endroid\QrCode\Writer\Result\ResultInterface;

interface WriterInterface
{
    /** @param array<string, mixed> $options */
    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, array $options = []): ResultInterface;
}
