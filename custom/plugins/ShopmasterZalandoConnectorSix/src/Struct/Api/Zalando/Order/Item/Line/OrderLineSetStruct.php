<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiSetStruct;

class OrderLineSetStruct extends ApiSetStruct
{
    protected string $id;
    protected string $type = OrderLineStruct::TYPE;
    protected array $attributes;
    private string $orderId;
    private string $orderItemId;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }


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
     * @return string
     */
    public function getOrderItemId(): string
    {
        return $this->orderItemId;
    }

    /**
     * @param string $orderItemId
     * @return self
     */
    public function setOrderItemId(string $orderItemId): self
    {
        $this->orderItemId = $orderItemId;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->attributes['status'] = $status;
        return $this;
    }

    public function setLogisticCenterId(string $logisticCenterId): static
    {
        if (!empty($logisticCenterId)) {
            $this->attributes['outbound_logistic_center_id'] = $logisticCenterId;
        }
        return $this;
    }

    public function __toString(): string
    {
        $data = parent::__toString();
        return '{"data": ' . $data . '}';

    }


}