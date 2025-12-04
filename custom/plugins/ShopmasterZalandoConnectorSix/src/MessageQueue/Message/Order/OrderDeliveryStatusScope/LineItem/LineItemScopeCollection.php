<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\LineItem;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use ShopmasterZalandoConnectorSix\Struct\Collection;

class LineItemScopeCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return OrderLineItemScope::class;
    }

    public function addReference(string $orderLineItemId, ?Status $status = null)
    {
        $this->add(new OrderLineItemScope($orderLineItemId, $status));
    }
}