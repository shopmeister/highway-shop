<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\OrderDeliveryStatusScope;

use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\Status\DeliveryStatus\Status;
use ShopmasterZalandoConnectorSix\Scope\Order\MainOrderScope;

abstract class OrderDeliveryStatusScope extends MainOrderScope
{
    protected Status $status;

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @param Status $status
     * @return self
     */
    public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }

}