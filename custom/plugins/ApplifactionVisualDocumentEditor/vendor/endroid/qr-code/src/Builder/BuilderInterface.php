<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Builder;

use Dde\Endroid\QrCode\Color\ColorInterface;
use Dde\Endroid\QrCode\Encoding\EncodingInterface;
use Dde\Endroid\QrCode\ErrorCorrectionLevel;
use Dde\Endroid\QrCode\Label\Font\FontInterface;
use Dde\Endroid\QrCode\Label\LabelAlignment;
use Dde\Endroid\QrCode\Label\Margin\MarginInterface;
use Dde\Endroid\QrCode\RoundBlockSizeMode;
use Dde\Endroid\QrCode\Writer\Result\ResultInterface;
use Dde\Endroid\QrCode\Writer\WriterInterface;

interface BuilderInterface
{
    public static function create(): BuilderInterface;

    public function writer(WriterInterface $writer): BuilderInterface;

    /** @param array<string, mixed> $writerOptions */
    public function writerOptions(array $writerOptions): BuilderInterface;

    public function data(string $data): BuilderInterface;

    public function encoding(EncodingInterface $encoding): BuilderInterface;

    public function errorCorrectionLevel(ErrorCorrectionLevel $errorCorrectionLevel): BuilderInterface;

    public function size(int $size): BuilderInterface;

    public function margin(int $margin): BuilderInterface;

    public function roundBlockSizeMode(RoundBlockSizeMode $roundBlockSizeMode): BuilderInterface;

    public function foregroundColor(ColorInterface $foregroundColor): BuilderInterface;

    public function backgroundColor(ColorInterface $backgroundColor): BuilderInterface;

    public function logoPath(string $logoPath): BuilderInterface;

    public function logoResizeToWidth(int $logoResizeToWidth): BuilderInterface;

    public function logoResizeToHeight(int $logoResizeToHeight): BuilderInterface;

    public function logoPunchoutBackground(bool $logoPunchoutBackground): BuilderInterface;

    public function labelText(string $labelText): BuilderInterface;

    public function labelFont(FontInterface $labelFont): BuilderInterface;

    public function labelAlignment(LabelAlignment $labelAlignment): BuilderInterface;

    public function labelMargin(MarginInterface $labelMargin): BuilderInterface;

    public function labelTextColor(ColorInterface $labelTextColor): BuilderInterface;

    public function validateResult(bool $validateResult): BuilderInterface;

    public function build(): ResultInterface;
}
