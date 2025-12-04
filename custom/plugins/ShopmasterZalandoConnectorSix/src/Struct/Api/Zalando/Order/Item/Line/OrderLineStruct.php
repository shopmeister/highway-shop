<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemStruct;

class OrderLineStruct extends ApiStruct
{
    const TYPE = 'OrderLine';
    protected string $id;
    protected string $type;
    protected string $orderItemId;
    protected string $status;
    protected string $sourceStockLocationId;
    protected float $price;
    protected float $discountedPrice;
    protected string $currency;
    protected string $createdBy;
    protected string $createdAt;
    protected string $modifiedBy;
    protected string $modifiedAt;
    private OrderItemStruct $orderItemStruct;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
     */
    public function setOrderItemId(string $orderItemId): void
    {
        $this->orderItemId = $orderItemId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getSourceStockLocationId(): string
    {
        return $this->sourceStockLocationId;
    }

    /**
     * @param string $sourceStockLocationId
     */
    public function setSourceStockLocationId(string $sourceStockLocationId): void
    {
        $this->sourceStockLocationId = $sourceStockLocationId;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getDiscountedPrice(): float
    {
        return $this->discountedPrice;
    }

    /**
     * @param float $discountedPrice
     */
    public function setDiscountedPrice(float $discountedPrice): void
    {
        $this->discountedPrice = $discountedPrice;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    /**
     * @param string $createdBy
     */
    public function setCreatedBy(string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getModifiedBy(): string
    {
        return $this->modifiedBy;
    }

    /**
     * @param string $modifiedBy
     */
    public function setModifiedBy(string $modifiedBy): void
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * @return string
     */
    public function getModifiedAt(): string
    {
        return $this->modifiedAt;
    }

    /**
     * @param string $modifiedAt
     */
    public function setModifiedAt(string $modifiedAt): void
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return OrderItemStruct
     */
    public function getOrderItemStruct(): OrderItemStruct
    {
        return $this->orderItemStruct;
    }

    /**
     * @param OrderItemStruct $orderItemStruct
     * @return self
     */
    public function setOrderItemStruct(OrderItemStruct $orderItemStruct): self
    {
        $this->orderItemStruct = $orderItemStruct;
        return $this;
    }


}