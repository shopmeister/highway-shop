<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */
 
declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Adapter\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ApplifactionInitFunctionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('applifactionDdeInit', [$this, 'init'], ['is_safe' => ['html']]),
        ];
    }

    public function init(): string
    {
        return '<!-- Created with Applifaction PDF Template Editor ©2025 -->';
    }
}
