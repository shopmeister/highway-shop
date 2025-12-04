<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Writer;

use Dde\Endroid\QrCode\Writer\Result\ResultInterface;

interface ValidatingWriterInterface
{
    public function validateResult(ResultInterface $result, string $expectedData): void;
}
