<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event;

use Dompdf\Dompdf;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeRenderDomPdfEvent extends Event
{
    public function __construct(
        private Dompdf $dompdf,
        private RenderedDocument $document
    ) {
    }

    public function getDompdf(): Dompdf
    {
        return $this->dompdf;
    }

    public function getDocument(): RenderedDocument
    {
        return $this->document;
    }
}
