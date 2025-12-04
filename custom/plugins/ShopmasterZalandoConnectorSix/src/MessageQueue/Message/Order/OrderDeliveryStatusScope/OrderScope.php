<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\LineItem\LineItemScopeCollection;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\LineItem\OrderLineItemScope;
use Shopware\Core\Checkout\Order\OrderEntity;

class OrderScope extends OrderDeliveryStatusScope
{
    protected ?LineItemScopeCollection $lineItemScopeCollection = null; // if is null in process automatically add all order items

    public function __construct(string $orderId, ?OrderEntity $orderEntity = null)
    {
        $this->setOrderId($orderId);
        $this->setOrderEntity($orderEntity);
    }

    /**
     * @return LineItemScopeCollection|null
     */
    public function getLineItemScopeCollection(): ?LineItemScopeCollection
    {
        return $this->lineItemScopeCollection;
    }

    /**
     * @param LineItemScopeCollection|null $lineItemScopeCollection
     * @return self
     */
    public function setLineItemScopeCollection(?LineItemScopeCollection $lineItemScopeCollection): self
    {
        $this->lineItemScopeCollection = $lineItemScopeCollection;
        return $this;
    }

    public function generate()
    {
        $this->generateLineItemScopeCollection();
        $this->generateLineItemScope();
    }

    private function generateLineItemScopeCollection()
    {
        if ($this->getLineItemScopeCollection()) {
            return;
        }
        $collection = new LineItemScopeCollection();
        foreach ($this->getOrderEntity()->getLineItems()->getIds() as $id) {
            $collection->addReference($id);
        }
        $this->setLineItemScopeCollection($collection);
    }

    private function generateLineItemScope()
    {
        /** @var OrderLineItemScope $item */
        foreach ($this->getLineItemScopeCollection() as $item) {
            if (!$item->getOrderScope()) {
                $item->setOrderScope($this);
            }
            if (!$item->getStatus()) {
                $item->setStatus($this->getStatus());
            }
            if (!$item->getLineItemEntity()) {
                $item->setLineItemEntity($this->getOrderEntity()->getLineItems()->get($item->getOrderLineItemId()));
            }
        }
    }
}