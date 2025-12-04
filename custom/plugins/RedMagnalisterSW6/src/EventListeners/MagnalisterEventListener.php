<?php

declare(strict_types=1);


namespace Redgecko\Magnalister\EventListeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MagnalisterEventListener implements EventSubscriberInterface {
    public function __construct() {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::RESPONSE => 'setMagnalisterSecurityHeaders',
        ];
    }

    /**
     * @param AccountLoginPageLoadedEvent|AccountProfilePageLoadedEvent $event
     */
    public function setMagnalisterSecurityHeaders(ResponseEvent $event): void {

        $response = $event->getResponse();
        $response->headers->set('X-Frame-Options', 'sameorigin');
    }
}
