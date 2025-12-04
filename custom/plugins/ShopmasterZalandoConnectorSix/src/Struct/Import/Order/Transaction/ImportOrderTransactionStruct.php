<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Order\Transaction;

use ShopmasterZalandoConnectorSix\Struct\Struct;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;

class ImportOrderTransactionStruct extends Struct
{
    /**
     * @var string
     */
    protected string $paymentMethodId;
    /**
     * @var string
     */
    protected string $stateId;
    /**
     * @var CalculatedPrice
     */
    protected CalculatedPrice $amount;

    /**
     * @return string
     */
    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    /**
     * @param string $paymentMethodId
     * @return self
     */
    public function setPaymentMethodId(string $paymentMethodId): self
    {
        $this->paymentMethodId = $paymentMethodId;
        return $this;
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
     * @return self
     */
    public function setStateId(string $stateId): self
    {
        $this->stateId = $stateId;
        return $this;
    }

    /**
     * @return CalculatedPrice
     */
    public function getAmount(): CalculatedPrice
    {
        return $this->amount;
    }

    /**
     * @param CalculatedPrice $amount
     * @return self
     */
    public function setAmount(CalculatedPrice $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

}