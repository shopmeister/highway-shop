<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Backend;

use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;
use Shopware\Core\Checkout\Order\OrderEntity;

abstract class OrderBackendStruct extends OrderStruct
{
    protected ?OrderEntity $orderEntity = null;

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

    final  public function jsonSerialize(): array
    {
        return $this->jsonMapSerialize();
    }

    abstract public function jsonMapSerialize(): array;


}