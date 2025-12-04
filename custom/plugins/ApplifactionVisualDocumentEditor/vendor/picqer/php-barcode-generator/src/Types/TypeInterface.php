<?php

namespace Dde\Picqer\Barcode\Types;

use Dde\Picqer\Barcode\Barcode;

interface TypeInterface
{
    public function getBarcodeData(string $code): Barcode;
}
