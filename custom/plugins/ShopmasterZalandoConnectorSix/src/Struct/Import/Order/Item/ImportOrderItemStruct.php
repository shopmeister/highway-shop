<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Item;

use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;

class ImportOrderItemStruct extends Struct
{
    const Z_ORDER_LINE_ID = 'zOrderLineId';
    const Z_ORDER_ITEM_ID = 'zOrderItemId';

    protected string $id;
    protected string $identifier;
    protected string $productId;
    protected string $referencedId;
    protected int $quantity;
    protected string $type = 'product';
    protected string $label;
    protected bool $good = true;
    protected bool $removable = true;
    protected bool $stackable = true;
    protected int $position;
    protected CalculatedPrice $price;
    protected QuantityPriceDefinition $priceDefinition;
    protected string $coverId;
    protected array $payload;
    protected array $customFields = [];

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ImportOrderItemStruct
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return ImportOrderItemStruct
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     * @return ImportOrderItemStruct
     */
    public function setProductId(string $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferencedId(): string
    {
        return $this->referencedId;
    }

    /**
     * @param string $referencedId
     * @return ImportOrderItemStruct
     */
    public function setReferencedId(string $referencedId): self
    {
        $this->referencedId = $referencedId;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return ImportOrderItemStruct
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
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
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return ImportOrderItemStruct
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGood(): bool
    {
        return $this->good;
    }

    /**
     * @param bool $good
     * @return ImportOrderItemStruct
     */
    public function setGood(bool $good): self
    {
        $this->good = $good;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRemovable(): bool
    {
        return $this->removable;
    }

    /**
     * @param bool $removable
     * @return ImportOrderItemStruct
     */
    public function setRemovable(bool $removable): self
    {
        $this->removable = $removable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStackable(): bool
    {
        return $this->stackable;
    }

    /**
     * @param bool $stackable
     * @return ImportOrderItemStruct
     */
    public function setStackable(bool $stackable): self
    {
        $this->stackable = $stackable;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return ImportOrderItemStruct
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return CalculatedPrice
     */
    public function getPrice(): CalculatedPrice
    {
        return $this->price;
    }

    /**
     * @param CalculatedPrice $price
     * @return ImportOrderItemStruct
     */
    public function setPrice(CalculatedPrice $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return QuantityPriceDefinition
     */
    public function getPriceDefinition(): QuantityPriceDefinition
    {
        return $this->priceDefinition;
    }

    /**
     * @param QuantityPriceDefinition $priceDefinition
     * @return ImportOrderItemStruct
     */
    public function setPriceDefinition(QuantityPriceDefinition $priceDefinition): self
    {
        $this->priceDefinition = $priceDefinition;
        return $this;
    }

    /**
     * @return string
     */
    public function getCoverId(): string
    {
        return $this->coverId;
    }

    /**
     * @param string $coverId
     * @return ImportOrderItemStruct
     */
    public function setCoverId(string $coverId): self
    {
        $this->coverId = $coverId;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return ImportOrderItemStruct
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * @param array $customFields
     * @return self
     */
    public function setCustomFields(array $customFields): self
    {
        $this->customFields = $customFields;
        return $this;
    }

}