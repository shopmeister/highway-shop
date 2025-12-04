<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer;

use Dde\Endroid\QrCode\Bacon\MatrixFactory;
use Dde\Endroid\QrCode\Label\LabelInterface;
use Dde\Endroid\QrCode\Logo\LogoInterface;
use Dde\Endroid\QrCode\QrCodeInterface;
use Dde\Endroid\QrCode\Writer\Result\ConsoleResult;
use Dde\Endroid\QrCode\Writer\Result\ResultInterface;

final class ConsoleWriter implements WriterInterface
{
    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, $options = []): ResultInterface
    {
        $matrixFactory = new MatrixFactory();
        $matrix = $matrixFactory->create($qrCode);

        return new ConsoleResult($matrix, $qrCode->getForegroundColor(), $qrCode->getBackgroundColor());
    }
}
