<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Subscriber;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\AfterRenderHtmlEvent;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use DOMDocument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormatHtmlSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly Logger $logger
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // AfterRenderHtmlEvent::class => ['onAfterRenderHtml', -1024], // Highest priority (executed first)
        ];
    }

    public function onAfterRenderHtml(AfterRenderHtmlEvent $event): void
    {
        $this->logger->logExecutionDuration(function () use ($event) {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            @$doc->loadHTML($event->getHtml());
            $event->setHtml($doc->saveHTML());
        }, "- Format html duration: %s ms");
    }

}
