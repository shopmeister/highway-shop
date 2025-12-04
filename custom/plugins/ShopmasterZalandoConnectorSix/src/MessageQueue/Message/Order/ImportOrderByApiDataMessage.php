<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessageInterface;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderCollection;

class ImportOrderByApiDataMessage implements MessageInterface
{
    private OrderCollection $orderCollection;

    /**
     * @return OrderCollection
     */
    public function getOrderCollection(): OrderCollection
    {
        return $this->orderCollection;
    }

    /**
     * @param OrderCollection $orderCollection
     * @return self
     */
    public function setOrderCollection(OrderCollection $orderCollection): self
    {
        $this->orderCollection = $orderCollection;
        return $this;
    }
}