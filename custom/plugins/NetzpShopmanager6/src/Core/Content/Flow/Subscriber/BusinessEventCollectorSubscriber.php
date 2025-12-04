<?php declare(strict_types=1);

namespace NetzpShopmanager6\Core\Content\Flow\Subscriber;

use NetzpShopmanager6\Core\Framework\Event\MobilePushAware;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BusinessEventCollectorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BusinessEventCollectorEvent::NAME => 'addMobilePushAware',
        ];
    }

    public function addMobilePushAware(BusinessEventCollectorEvent $event): void
    {
        foreach ($event->getCollection()->getElements() as $definition)
        {
            $className = \explode('\\', MobilePushAware::class);
            $definition->addAware(\lcfirst(\end($className)));
        }
    }
}
