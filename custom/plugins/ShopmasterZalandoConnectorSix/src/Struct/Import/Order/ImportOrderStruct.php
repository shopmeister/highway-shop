<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order;

use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order\OrderStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Address\ImportOrderAddressCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Customer\ImportOrderCustomerStruct;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Delivery\ImportOrderDeliveryCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Item\ImportOrderItemCollection;
use ShopmasterZalandoConnectorSix\Struct\Import\Order\Transaction\ImportOrderTransactionCollection;
use ShopmasterZalandoConnectorSix\Struct\Interfaces\CustomFieldsInterface;
use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;

class ImportOrderStruct extends Struct implements CustomFieldsInterface
{
    /**
     * @var string
     */
    protected string $id;
    /**
     * @var string
     */
    protected string $stateId;
    /**
     * @var string
     */
    protected string $orderNumber;
    /**
     * @var string
     */
    protected string $currencyId;
    /**
     * @var string
     */
    protected string $languageId;
    /**
     * @var string
     */
    protected string $salesChannelId;
    /**
     * @var string
     */
    protected string $orderDateTime;
    /**
     * @var string
     */
    protected string $orderDate;
    /**
     * @var string
     */
    protected string $customerComment;
    /**
     * @var string
     */
    protected string $billingAddressId;
    /**
     * @var float
     */
    protected float $currencyFactor = 1.0;
    /**
     * @var CartPrice
     */
    protected CartPrice $price;
    /**
     * @var ImportOrderItemCollection
     */
    protected ImportOrderItemCollection $lineItems;
    /**
     * @var CalculatedPrice
     */
    protected CalculatedPrice $shippingCosts;
    /**
     * @var ImportOrderDeliveryCollection
     */
    protected ImportOrderDeliveryCollection $deliveries;
    /**
     * @var ImportOrderTransactionCollection
     */
    protected ImportOrderTransactionCollection $transactions;
    /**
     * @var ImportOrderAddressCollection
     */
    protected ImportOrderAddressCollection $addresses;
    /**
     * @var ImportOrderCustomerStruct
     */
    protected ImportOrderCustomerStruct $orderCustomer;
    protected array $itemRounding;
    protected array $totalRounding;
    /**
     * @var CalculatedPrice
     */
    private CalculatedPrice $paymentPrice;
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
    public function getStateId(): string
    {
        return $this->stateId;
    }

    /**
     * @param string $stateId
     */
    public function setStateId(string $stateId): self
    {
        $this->stateId = $stateId;
        return $this;
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
     * @return ImportOrderStruct
     */
    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    /**
     * @param string $currencyId
     * @return ImportOrderStruct
     */
    public function setCurrencyId(string $currencyId): self
    {
        $this->currencyId = $currencyId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    /**
     * @param string $languageId
     * @return ImportOrderStruct
     */
    public function setLanguageId(string $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
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
     * @return ImportOrderStruct
     */
    public function setSalesChannelId(string $salesChannelId): self
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderDateTime(): string
    {
        return $this->orderDateTime;
    }

    /**
     * @param string $orderDateTime
     * @return self
     */
    public function setOrderDateTime(string $orderDateTime): self
    {
        $this->orderDateTime = $orderDateTime;
        return $this;
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
    public function getCustomerComment(): string
    {
        return $this->customerComment;
    }

    /**
     * @param string $customerComment
     */
    public function setCustomerComment(string $customerComment): void
    {
        $this->customerComment = $customerComment;
    }

    /**
     * @return string
     */
    public function getBillingAddressId(): string
    {
        return $this->billingAddressId;
    }

    /**
     * @param string $billingAddressId
     */
    public function setBillingAddressId(string $billingAddressId): void
    {
        $this->billingAddressId = $billingAddressId;
    }

    /**
     * @return CartPrice
     */
    public function getPrice(): CartPrice
    {
        return $this->price;
    }

    /**
     * @param CartPrice $price
     * @return ImportOrderStruct
     */
    public function setPrice(CartPrice $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return ImportOrderItemCollection
     */
    public function getLineItems(): ImportOrderItemCollection
    {
        return $this->lineItems;
    }

    /**
     * @param ImportOrderItemCollection $lineItems
     * @return ImportOrderStruct
     */
    public function setLineItems(ImportOrderItemCollection $lineItems): self
    {
        $this->lineItems = $lineItems;
        return $this;
    }

    /**
     * @return CalculatedPrice
     */
    public function getShippingCosts(): CalculatedPrice
    {
        return $this->shippingCosts;
    }

    /**
     * @param CalculatedPrice $shippingCosts
     * @return ImportOrderStruct
     */
    public function setShippingCosts(CalculatedPrice $shippingCosts): self
    {
        $this->shippingCosts = $shippingCosts;
        return $this;
    }

    /**
     * @return float
     */
    public function getCurrencyFactor(): float
    {
        return $this->currencyFactor;
    }

    /**
     * @param float $currencyFactor
     * @return ImportOrderStruct
     */
    public function setCurrencyFactor(float $currencyFactor): self
    {
        $this->currencyFactor = $currencyFactor;
        return $this;
    }

    /**
     * @return ImportOrderDeliveryCollection
     */
    public function getDeliveries(): ImportOrderDeliveryCollection
    {
        return $this->deliveries;
    }

    /**
     * @param ImportOrderDeliveryCollection $deliveries
     * @return self
     */
    public function setDeliveries(ImportOrderDeliveryCollection $deliveries): self
    {
        $this->deliveries = $deliveries;
        return $this;
    }

    /**
     * @return ImportOrderTransactionCollection
     */
    public function getTransactions(): ImportOrderTransactionCollection
    {
        return $this->transactions;
    }

    /**
     * @param ImportOrderTransactionCollection $transactions
     * @return self
     */
    public function setTransactions(ImportOrderTransactionCollection $transactions): self
    {
        $this->transactions = $transactions;
        return $this;
    }

    /**
     * @return ImportOrderAddressCollection
     */
    public function getAddresses(): ImportOrderAddressCollection
    {
        return $this->addresses;
    }

    /**
     * @param ImportOrderAddressCollection $addresses
     * @return self
     */
    public function setAddresses(ImportOrderAddressCollection $addresses): self
    {
        $this->setBillingAddressId($addresses->getBillingAddressId());
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * @return ImportOrderCustomerStruct
     */
    public function getOrderCustomer(): ImportOrderCustomerStruct
    {
        return $this->orderCustomer;
    }

    /**
     * @param ImportOrderCustomerStruct $orderCustomer
     * @return self
     */
    public function setOrderCustomer(ImportOrderCustomerStruct $orderCustomer): self
    {
        $this->orderCustomer = $orderCustomer;
        return $this;
    }

    /**
     * @param CalculatedPrice $paymentPrice
     * @return self
     */
    public function setPaymentPrice(CalculatedPrice $paymentPrice): self
    {
        $this->paymentPrice = $paymentPrice;
        return $this;
    }

    /**
     * @return CalculatedPrice
     */
    public function getPaymentPrice(): CalculatedPrice
    {
        return $this->paymentPrice;
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

    public function customFieldsData(): array
    {
        return [
            OrderCustomFields::CUSTOM_FIELD_CUSTOMERNUMBER => $this->getOrderStruct()->getCustomerNumber(),
            OrderCustomFields::CUSTOM_FIELD_ORDERNUMBER => $this->getOrderStruct()->getOrderNumber(),
            OrderCustomFields::CUSTOM_FIELD_SALES_CHANNEL_ID => $this->getOrderStruct()->getSalesChannelId(),
            OrderCustomFields::CUSTOM_FIELD_ORDER_ID => $this->getOrderStruct()->getId(),
        ];
    }

    public function getItemRounding(): array
    {
        return $this->itemRounding;
    }

    public function setItemRounding(array $itemRounding): self
    {
        $this->itemRounding = $itemRounding;
        return $this;
    }

    public function getTotalRounding(): array
    {
        return $this->totalRounding;
    }

    public function setTotalRounding(array $totalRounding): self
    {
        $this->totalRounding = $totalRounding;
        return $this;
    }

}