<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\LineItem\ReturnLineItemProcessStartMessage;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\LineItem\LineItemScopeCollection;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\ReturnedStatus;
use ShopmasterZalandoConnectorSix\Services\Order\LineItem\LineItemService;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: ReturnLineItemProcessStartMessage::class)]
class ReturnLineItemHandler
{
    public function __construct(
        private readonly LineItemService  $lineItemService,
        private readonly EntityRepository $repositoryOrder
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(ReturnLineItemProcessStartMessage $message): void
    {
        $order = $this->getOrder($message->getOrderId(), $message->getContext());
        if (!$order) {
            return;
        }
        $orderScope = new OrderScope($message->getOrderId(), $order);
        $this->makeReturnLineItems($orderScope, $order);
        $this->lineItemService->updateLineItems($orderScope, $message->getContext());
    }

    private function getOrder(string $orderId, Context $context): ?OrderEntity
    {
        $criteria = (new Criteria([$orderId]))
            ->addAssociations([
                'lineItems.pickwareErpReturnOrderLineItem'
            ]);
        return $this->repositoryOrder->search($criteria, $context)->first();
    }

    private function makeReturnLineItems(OrderScope $orderScope, OrderEntity $order): void
    {
        $collection = new LineItemScopeCollection();
        /** @var OrderLineItemEntity $lineItem */
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getExtension('pickwareErpReturnOrderLineItem')) {
                $collection->addReference($lineItem->getId(), new ReturnedStatus());
            }
        }
        if (!$collection->count()) {
            throw new \Exception('can not find return items');
        }
        $orderScope->setLineItemScopeCollection($collection);
        $orderScope->generate();
    }
}