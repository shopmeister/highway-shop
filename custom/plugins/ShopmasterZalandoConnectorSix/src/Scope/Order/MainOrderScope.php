<?php

namespace ShopmasterZalandoConnectorSix\Scope\Order;

use ShopmasterZalandoConnectorSix\Struct\Scope;
use Shopware\Core\Checkout\Order\OrderEntity;

class MainOrderScope extends Scope
{
    protected string $orderId;
    protected ?OrderEntity $orderEntity = null;

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return self
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return OrderEntity|null
     */
    public function getOrderEntity(): ?OrderEntity
    {
        return $this->orderEntity;
    }

    /**
     * @param OrderEntity|null $orderEntity
     * @return self
     */
    public function setOrderEntity(?OrderEntity $orderEntity): self
    {
        $this->orderEntity = $orderEntity;
        return $this;
    }
}