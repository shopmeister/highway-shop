<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiSetStruct;

class OrderSetStruct extends ApiSetStruct
{
    protected string $id;
    protected string $type = OrderStruct::ORDER_TYPE;
    protected array $attributes;


    /**
     * @param string $id
     * @return OrderSetStruct
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $merchantOrderId
     * @return OrderSetStruct
     */
    public function setMerchantOrderId(string $merchantOrderId): self
    {
        $this->attributes['merchant_order_id'] = $merchantOrderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantOrderId(): string
    {
        return $this->attributes['merchant_order_id'];
    }

    public function setTrackingNumber(string $trackingNumber): self
    {
        $trackingNumber = trim($trackingNumber);
        if (!empty($trackingNumber)) {
            $this->attributes['tracking_number'] = $trackingNumber;
        }
        return $this;
    }

    public function setReturnTrackingNumber(string $returnTrackingNumber): self
    {
        $returnTrackingNumber = trim($returnTrackingNumber);
        if (!empty($returnTrackingNumber)) {
            $this->attributes['return_tracking_number'] = $returnTrackingNumber;
        }
        return $this;
    }

    public function __toString(): string
    {
        $data = parent::__toString();
        return '{"data": ' . $data . '}';

    }

}