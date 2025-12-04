<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiStruct;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemCollection;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\Item\OrderItemStruct;


class OrderStruct extends ApiStruct
{
    const ORDER_TYPE = 'Order';

    const APPROVED_STATUS = 'approved';
    const FULFILLED_STATUS = 'fulfilled';
    const INITIAL_STATUS = 'initial';


    protected string $id;
    protected string $type;
    protected string $merchantId;
    protected string $orderNumber;
    protected string $orderDate;
    protected string $merchantOrderId;
    protected string $salesChannelId;
    protected string $locale;
    protected string $status;
    protected string $shipmentNumber;
    protected string $trackingNumber;
    protected string $returnTrackingNumber;
    protected int $orderLinesCount;
    protected float $orderLinesPriceAmount;
    protected string $orderLinesPriceCurrency;
    protected string $deliveryEndDate;
    protected bool $exported;
    protected string $createdBy;
    protected string $createdAt;
    protected string $modifiedBy;
    protected string $modifiedAt;
    protected string $orderType;
    protected string $stockLocationId;
    protected string $customerEmail;
    protected string $customerNumber;
    protected OrderCustomerStruct $customer;
    protected OrderAddressStruct $shippingAddress;
    protected OrderAddressStruct $billingAddress;
    protected OrderItemCollection $orderItems;

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
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    /**
     * @param string $orderDate
     */
    public function setOrderDate(string $orderDate): void
    {
        $this->orderDate = $orderDate;
    }

    /**
     * @return string
     */
    public function getMerchantOrderId(): string
    {
        return $this->merchantOrderId;
    }

    /**
     * @param string $merchantOrderId
     */
    public function setMerchantOrderId(string $merchantOrderId): void
    {
        $this->merchantOrderId = $merchantOrderId;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
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
    public function getShipmentNumber(): string
    {
        return $this->shipmentNumber;
    }

    /**
     * @param string $shipmentNumber
     */
    public function setShipmentNumber(string $shipmentNumber): void
    {
        $this->shipmentNumber = $shipmentNumber;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string
     */
    public function getReturnTrackingNumber(): string
    {
        return $this->returnTrackingNumber;
    }

    /**
     * @param string $returnTrackingNumber
     */
    public function setReturnTrackingNumber(string $returnTrackingNumber): void
    {
        $this->returnTrackingNumber = $returnTrackingNumber;
    }

    /**
     * @return int
     */
    public function getOrderLinesCount(): int
    {
        return $this->orderLinesCount;
    }

    /**
     * @param int $orderLinesCount
     */
    public function setOrderLinesCount(int $orderLinesCount): void
    {
        $this->orderLinesCount = $orderLinesCount;
    }

    /**
     * @return float
     */
    public function getOrderLinesPriceAmount(): float
    {
        return $this->orderLinesPriceAmount;
    }

    /**
     * @param float $orderLinesPriceAmount
     */
    public function setOrderLinesPriceAmount(float $orderLinesPriceAmount): void
    {
        $this->orderLinesPriceAmount = $orderLinesPriceAmount;
    }

    /**
     * @return string
     */
    public function getOrderLinesPriceCurrency(): string
    {
        return $this->orderLinesPriceCurrency;
    }

    /**
     * @param string $orderLinesPriceCurrency
     */
    public function setOrderLinesPriceCurrency(string $orderLinesPriceCurrency): void
    {
        $this->orderLinesPriceCurrency = $orderLinesPriceCurrency;
    }

    /**
     * @return string
     */
    public function getDeliveryEndDate(): string
    {
        return $this->deliveryEndDate;
    }

    /**
     * @param string $deliveryEndDate
     */
    public function setDeliveryEndDate(string $deliveryEndDate): void
    {
        $this->deliveryEndDate = $deliveryEndDate;
    }

    /**
     * @return bool
     */
    public function isExported(): bool
    {
        return $this->exported;
    }

    /**
     * @param bool $exported
     */
    public function setExported(bool $exported): void
    {
        $this->exported = $exported;
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
     * @return string
     */
    public function getOrderType(): string
    {
        return $this->orderType;
    }

    /**
     * @param string $orderType
     */
    public function setOrderType(string $orderType): void
    {
        $this->orderType = $orderType;
    }

    /**
     * @return string
     */
    public function getStockLocationId(): string
    {
        return $this->stockLocationId;
    }

    /**
     * @param string $stockLocationId
     */
    public function setStockLocationId(string $stockLocationId): void
    {
        $this->stockLocationId = $stockLocationId;
    }

    /**
     * @return OrderCustomerStruct
     */
    public function getCustomer(): OrderCustomerStruct
    {
        return $this->customer;
    }

    /**
     * @param OrderCustomerStruct $customer
     */
    public function setCustomer(OrderCustomerStruct $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return OrderAddressStruct
     */
    public function getShippingAddress(): OrderAddressStruct
    {
        return $this->shippingAddress;
    }

    /**
     * @param OrderAddressStruct $shippingAddress
     */
    public function setShippingAddress(OrderAddressStruct $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return OrderAddressStruct
     */
    public function getBillingAddress(): OrderAddressStruct
    {
        return $this->billingAddress;
    }

    /**
     * @param OrderAddressStruct $billingAddress
     */
    public function setBillingAddress(OrderAddressStruct $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return OrderItemCollection
     */
    public function getOrderItems(): OrderItemCollection
    {
        return $this->orderItems;
    }

    /**
     * @param OrderItemCollection $orderItems
     */
    public function setOrderItems(OrderItemCollection $orderItems): void
    {
        /** @var OrderItemStruct $orderItem */
        foreach ($orderItems as $orderItem) {
            $orderItem->setOrderStruct($this);
        }
        $this->orderItems = $orderItems;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED_STATUS;
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


    public function isValideForImport(): bool
    {
        return ($this->type === self::ORDER_TYPE && in_array($this->status, [self::APPROVED_STATUS, self::FULFILLED_STATUS]));
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * @param string $customerEmail
     */
    public function setCustomerEmail(string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    /**
     * @return string
     */
    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    /**
     * @param string $customerNumber
     * @return self
     */
    public function setCustomerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

}