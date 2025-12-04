<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Matrix;

use Dde\Endroid\QrCode\QrCodeInterface;

interface MatrixFactoryInterface
{
    public function create(QrCodeInterface $qrCode): MatrixInterface;
}
