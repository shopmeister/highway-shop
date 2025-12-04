<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessageInterface;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope\OrderDeliveryStatusScope;
use Shopware\Core\Framework\Context;

class OrderDeliveryStatusMessage implements MessageInterface
{
    private OrderDeliveryStatusScope $scope;
    private string $eventName;
    private Context $context;


    public function __construct(
        OrderDeliveryStatusScope $scope,
        string                   $eventName,
        Context                  $context
    )
    {
        $this->scope = $scope;
        $this->eventName = $eventName;
        $this->context = $context;
    }

    /**
     * @return OrderDeliveryStatusScope
     */
    public function getScope(): OrderDeliveryStatusScope
    {
        return $this->scope;
    }

    /**
     * @param OrderDeliveryStatusScope $scope
     * @return self
     */
    public function setScope(OrderDeliveryStatusScope $scope): self
    {
        $this->scope = $scope;
        return $this;
    }


    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     * @return self
     */
    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;
        return $this;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @param Context $context
     * @return self
     */
    public function setContext(Context $context): self
    {
        $this->context = $context;
        return $this;
    }

}