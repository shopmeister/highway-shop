<?php

namespace ShopmasterZalandoConnectorSix\Subscribers\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class OrderStateMachineTransitionSubscriber implements EventSubscriberInterface
{
    private EntityRepository $repositoryOrderDelivery;
    private MessageBusInterface $messageBus;

    public function __construct(
        EntityRepository $repositoryOrderDelivery,
        MessageBusInterface       $messageBus
    )
    {
        $this->repositoryOrderDelivery = $repositoryOrderDelivery;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StateMachineTransitionEvent::class => 'onStateMachineTransitionEvent'
        ];
    }

    public function onStateMachineTransitionEvent(StateMachineTransitionEvent $event): void
    {
        if ($event->getEntityName() === OrderDeliveryDefinition::ENTITY_NAME) {
            $this->onDeliveryEntity($event);
        }
    }

    private function onDeliveryEntity(StateMachineTransitionEvent $event): void
    {
        $criteria = new Criteria([$event->getEntityId()]);
        /** @var OrderDeliveryEntity|null $deliveryStatus */
        $deliveryStatus = $this->repositoryOrderDelivery->search($criteria, $event->getContext())->first();
        if (!$deliveryStatus) {
            return;
        }

        $scope = new OrderScope($deliveryStatus->getOrderId());
        $message = new OrderDeliveryStatusMessage($scope, StateMachineTransitionEvent::class, $event->getContext());
        $this->messageBus->dispatch((new Envelope($message))->with(new DelayStamp(1000)));
    }


}