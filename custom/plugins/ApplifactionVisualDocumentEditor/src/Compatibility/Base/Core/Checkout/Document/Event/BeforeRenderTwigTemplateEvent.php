<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeRenderTwigTemplateEvent extends Event
{
    public function __construct(private string $twigTemplate)
    {
    }

    public function getTwigTemplate(): string
    {
        return $this->twigTemplate;
    }

    public function setTwigTemplate(string $twigTemplate): void
    {
        $this->twigTemplate = $twigTemplate;
    }
}
