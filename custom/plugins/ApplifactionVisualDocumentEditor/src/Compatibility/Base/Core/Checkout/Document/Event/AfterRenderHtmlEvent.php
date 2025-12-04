<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event;

use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\Event;

class AfterRenderHtmlEvent extends Event
{
    public function __construct(
        private string $html,
        private readonly ?DocumentEntity $document = null,
        private readonly ?Context $context = null,
    ) {
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    public function getDocument(): ?DocumentEntity
    {
        return $this->document;
    }

    public function getContext(): ?Context
    {
        return $this->context;
    }
}
