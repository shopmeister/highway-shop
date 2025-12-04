<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer;

use Dde\Endroid\QrCode\Bacon\MatrixFactory;
use Dde\Endroid\QrCode\Label\LabelInterface;
use Dde\Endroid\QrCode\Logo\LogoInterface;
use Dde\Endroid\QrCode\QrCodeInterface;
use Dde\Endroid\QrCode\Writer\Result\BinaryResult;
use Dde\Endroid\QrCode\Writer\Result\ResultInterface;

final class BinaryWriter implements WriterInterface
{
    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, array $options = []): ResultInterface
    {
        $matrixFactory = new MatrixFactory();
        $matrix = $matrixFactory->create($qrCode);

        return new BinaryResult($matrix);
    }
}
