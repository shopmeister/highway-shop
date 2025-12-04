<?php

declare(strict_types=1);

namespace Dde\Endroid\QrCode\Label;

use Dde\Endroid\QrCode\Color\ColorInterface;
use Dde\Endroid\QrCode\Label\Font\FontInterface;
use Dde\Endroid\QrCode\Label\Margin\MarginInterface;

interface LabelInterface
{
    public function getText(): string;

    public function getFont(): FontInterface;

    public function getAlignment(): LabelAlignment;

    public function getMargin(): MarginInterface;

    public function getTextColor(): ColorInterface;
}
