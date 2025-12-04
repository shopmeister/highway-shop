<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event;

use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeRenderTemplateDataEvent extends Event
{
    public function __construct(
        private array $templateData,
        private readonly ?Context $context = null
    ) {
    }

    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    public function setTemplateData(array $data): void
    {
        $this->templateData = $data;
    }

    public function getContext(): ?Context
    {
        return $this->context;
    }
}
