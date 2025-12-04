<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\Line\OrderLineStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;

class OrderItemStruct extends ApiStruct
{
    const ORDER_ITEM = 'OrderItem';

    protected string $id;
    protected string $orderId;
    protected string $type;
    protected string $articleId;
    protected string $externalId;
    protected string $description;
    protected int $quantityInitial;
    protected int $quantityReserved;
    protected int $quantityShipped;
    protected int $quantityReturned;
    protected int $quantityCanceled;
    protected string $createdBy;
    protected string $createdAt;
    protected string $modifiedBy;
    protected string $modifiedAt;
    protected OrderLineCollection $orderLines;
    private OrderStruct $orderStruct;

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
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @param string $articleId
     */
    public function setArticleId(string $articleId): void
    {
        $this->articleId = $articleId;
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     */
    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getQuantityInitial(): int
    {
        return $this->quantityInitial;
    }

    /**
     * @param int $quantityInitial
     */
    public function setQuantityInitial(int $quantityInitial): void
    {
        $this->quantityInitial = $quantityInitial;
    }

    /**
     * @return int
     */
    public function getQuantityReserved(): int
    {
        return $this->quantityReserved;
    }

    /**
     * @param int $quantityReserved
     */
    public function setQuantityReserved(int $quantityReserved): void
    {
        $this->quantityReserved = $quantityReserved;
    }

    /**
     * @return int
     */
    public function getQuantityShipped(): int
    {
        return $this->quantityShipped;
    }

    /**
     * @param int $quantityShipped
     */
    public function setQuantityShipped(int $quantityShipped): void
    {
        $this->quantityShipped = $quantityShipped;
    }

    /**
     * @return int
     */
    public function getQuantityReturned(): int
    {
        return $this->quantityReturned;
    }

    /**
     * @param int $quantityReturned
     */
    public function setQuantityReturned(int $quantityReturned): void
    {
        $this->quantityReturned = $quantityReturned;
    }

    /**
     * @return int
     */
    public function getQuantityCanceled(): int
    {
        return $this->quantityCanceled;
    }

    /**
     * @param int $quantityCanceled
     */
    public function setQuantityCanceled(int $quantityCanceled): void
    {
        $this->quantityCanceled = $quantityCanceled;
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
     * @return OrderLineCollection
     */
    public function getOrderLines(): OrderLineCollection
    {
        return $this->orderLines;
    }

    /**
     * @param OrderLineCollection $orderLines
     */
    public function setOrderLines(OrderLineCollection $orderLines): void
    {
        /** @var OrderLineStruct $orderLine */
        foreach ($orderLines as $orderLine) {
            $orderLine->setOrderItemStruct($this);
        }
        $this->orderLines = $orderLines;
    }

    /**
     * @return OrderStruct
     */
    public function getOrderStruct(): OrderStruct
    {
        return $this->orderStruct;
    }

    /**
     * @param OrderStruct $orderStruct
     * @return self
     */
    public function setOrderStruct(OrderStruct $orderStruct): self
    {
        $this->orderStruct = $orderStruct;
        return $this;
    }

}