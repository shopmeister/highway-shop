<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer;

use Dde\Endroid\QrCode\Label\LabelInterface;
use Dde\Endroid\QrCode\Logo\LogoInterface;
use Dde\Endroid\QrCode\QrCodeInterface;
use Dde\Endroid\QrCode\Writer\Result\GdResult;
use Dde\Endroid\QrCode\Writer\Result\ResultInterface;
use Dde\Endroid\QrCode\Writer\Result\WebPResult;

final class WebPWriter extends AbstractGdWriter
{
    public const WRITER_OPTION_QUALITY = 'quality';

    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, array $options = []): ResultInterface
    {
        if (!isset($options[self::WRITER_OPTION_QUALITY])) {
            $options[self::WRITER_OPTION_QUALITY] = -1;
        }

        /** @var GdResult $gdResult */
        $gdResult = parent::write($qrCode, $logo, $label, $options);

        return new WebPResult($gdResult->getMatrix(), $gdResult->getImage(), $options[self::WRITER_OPTION_QUALITY]);
    }
}
