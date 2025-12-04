<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer;

use Dde\Endroid\QrCode\Label\LabelInterface;
use Dde\Endroid\QrCode\Logo\LogoInterface;
use Dde\Endroid\QrCode\QrCodeInterface;
use Dde\Endroid\QrCode\Writer\Result\GdResult;
use Dde\Endroid\QrCode\Writer\Result\GifResult;
use Dde\Endroid\QrCode\Writer\Result\ResultInterface;

final class GifWriter extends AbstractGdWriter
{
    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, array $options = []): ResultInterface
    {
        /** @var GdResult $gdResult */
        $gdResult = parent::write($qrCode, $logo, $label, $options);

        return new GifResult($gdResult->getMatrix(), $gdResult->getImage());
    }
}
