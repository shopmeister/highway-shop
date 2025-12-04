<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Adapter;

use Pickware\DocumentBundle\Document\PageFormat;

enum DhlLabelSize
{
    case A5;
    case A4SelfprintReturn;

    public function getPageFormat(): PageFormat
    {
        return match ($this) {
            self::A5 => new PageFormat(
                'DHL Label A5',
                PageFormat::createDinPageFormat('A5')->getSize(),
                'dhl_a5',
            ),
            self::A4SelfprintReturn => new PageFormat(
                'DHL A4 Selfprint Returnlabel',
                PageFormat::createDinPageFormat('A4')->getSize(),
                'dhl_a4_selfprint_return',
            ),
        };
    }

    public static function getSupportedPageFormats(): array
    {
        $pageFormats = [];

        foreach (self::cases() as $case) {
            $pageFormats[] = $case->getPageFormat();
        }

        return $pageFormats;
    }
}
