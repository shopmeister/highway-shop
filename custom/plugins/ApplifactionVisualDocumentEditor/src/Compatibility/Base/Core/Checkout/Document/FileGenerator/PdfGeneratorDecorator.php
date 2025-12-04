<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\FileGenerator;

use Shopware\Core\Checkout\Document\FileGenerator\PdfGenerator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Shopware\Core\Checkout\Document\GeneratedDocument;

/**
 * Applies Dompdf Options which was added earlier to the GeneratedDocument to DomPdf
 */
class PdfGeneratorDecorator extends PdfGenerator
{

    /**
     * @var array<string, mixed>
     */
    private array $dompdfOptions;

    public function __construct()
    {
        $this->dompdfOptions = [
            "isRemoteEnabled" => true,
            "isHtml5ParserEnabled" => true
        ];
    }

    public function generate(GeneratedDocument $generatedDocument): string
    {
        $extensions = $generatedDocument->getExtensions();
        $options = new Options(array_merge($this->dompdfOptions, $extensions['dompdfOptions'] ?? []));

        $dompdf = new Dompdf($options);
        $dompdf->setPaper($generatedDocument->getPageSize(), $generatedDocument->getPageOrientation());
        $dompdf->loadHtml($generatedDocument->getHtml());

        /*
         * Dompdf creates and destroys a lot of objects. The garbage collector slows the process down by ~50% for
         * PHP <7.3 and still some ms for 7.4
         */
        $gcEnabledAtStart = gc_enabled();
        if ($gcEnabledAtStart) {
            gc_collect_cycles();
            gc_disable();
        }

        $dompdf->render();

        // $this->injectPageCount($dompdf);

        if ($gcEnabledAtStart) {
            gc_enable();
        }

        return (string)$dompdf->output();
    }
}
