<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Subscribers\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\LineItem\ReturnLineItemProcessStartMessage;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class LineItemReturnedSubscriber implements EventSubscriberInterface
{
    /**
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    )
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerArgumentsEvent::class => 'onControllerArgumentsEvent'
        ];
    }

    /**
     * @param ControllerArgumentsEvent $event
     * @return void
     */
    public function onControllerArgumentsEvent(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        if (!in_array($route, [
            'api.action.pickware-erp.request-and-approve-return-order',
            'api.action.pickware-erp.create-completed-return-orders',
        ])) {
            return;
        }
        $context = null;
        foreach ($event->getArguments() as $argument) {
            if ($argument instanceof Context) {
                $context = clone $argument;
                break;
            }
        }
        if (!$context) {
            $context = Context::createDefaultContext();
        }

        $returnOrderPayloads = $request->get('returnOrders', []);
        foreach ($returnOrderPayloads as $returnOrderPayload) {
            if (!empty($returnOrderPayload['orderId'])) {
                $message = new ReturnLineItemProcessStartMessage($returnOrderPayload['orderId'], $context);
                $this->messageBus->dispatch((new Envelope($message))->with(new DelayStamp(5)));
            }
        }

        $returnOrderPayload = $request->get('returnOrder');
        if (!empty($returnOrderPayload['orderId'])) {
            $message = new ReturnLineItemProcessStartMessage($returnOrderPayload['orderId'], $context);
            $this->messageBus->dispatch((new Envelope($message))->with(new DelayStamp(5)));
        }
    }
}