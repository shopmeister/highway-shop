<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Delivery;

use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;

class ImportOrderDeliveryStruct extends Struct
{
    /**
     * @var string
     */
    protected string $stateId;
    /**
     * @var string
     */
    protected string $shippingMethodId;
    /**
     * @var CalculatedPrice
     */
    protected CalculatedPrice $shippingCosts;
    /**
     * @var \DateTimeInterface
     */
    protected \DateTimeInterface $shippingDateEarliest;
    /**
     * @var \DateTimeInterface
     */
    protected \DateTimeInterface $shippingDateLatest;

    /**
     * @return string
     */
    public function getStateId(): string
    {
        return $this->stateId;
    }

    /**
     * @param string $stateId
     * @return self
     */
    public function setStateId(string $stateId): self
    {
        $this->stateId = $stateId;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingMethodId(): string
    {
        return $this->shippingMethodId;
    }

    /**
     * @param string $shippingMethodId
     * @return self
     */
    public function setShippingMethodId(string $shippingMethodId): self
    {
        $this->shippingMethodId = $shippingMethodId;
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
     * @return self
     */
    public function setShippingCosts(CalculatedPrice $shippingCosts): self
    {
        $this->shippingCosts = $shippingCosts;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getShippingDateEarliest(): \DateTimeInterface
    {
        return $this->shippingDateEarliest;
    }

    /**
     * @param \DateTimeInterface $shippingDateEarliest
     * @return void
     */
    public function setShippingDateEarliest(\DateTimeInterface $shippingDateEarliest): void
    {
        $this->shippingDateEarliest = $shippingDateEarliest;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getShippingDateLatest(): \DateTimeInterface
    {
        return $this->shippingDateLatest;
    }

    /**
     * @param \DateTimeInterface $shippingDateLatest
     * @return void
     */
    public function setShippingDateLatest(\DateTimeInterface $shippingDateLatest): void
    {
        $this->shippingDateLatest = $shippingDateLatest;
    }
}