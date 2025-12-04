<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\LineItem;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderScope;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

class OrderLineItemScope
{
    protected string $orderLineItemId;
    protected ?Status $status = null; // if is null in order scope generate set order status
    protected ?OrderScope $orderScope = null;
    private ?OrderLineItemEntity $lineItemEntity = null;

    public function __construct(string $orderLineItemId, ?Status $status = null)
    {
        $this->orderLineItemId = $orderLineItemId;
        $this->status = $status;
    }

    /**
     * @return Status|null
     */
    public function getStatus(): ?Status
    {
        return $this->status;
    }

    /**
     * @param Status|null $status
     * @return self
     */
    public function setStatus(?Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return OrderScope|null
     */
    public function getOrderScope(): ?OrderScope
    {
        return $this->orderScope;
    }

    /**
     * @param OrderScope|null $orderScope
     * @return self
     */
    public function setOrderScope(?OrderScope $orderScope): self
    {
        $this->orderScope = $orderScope;
        return $this;
    }

    /**
     * @return OrderLineItemEntity|null
     */
    public function getLineItemEntity(): ?OrderLineItemEntity
    {
        return $this->lineItemEntity;
    }

    /**
     * @param OrderLineItemEntity|null $lineItemEntity
     * @return self
     */
    public function setLineItemEntity(?OrderLineItemEntity $lineItemEntity): self
    {
        $this->lineItemEntity = $lineItemEntity;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderLineItemId(): string
    {
        return $this->orderLineItemId;
    }
}