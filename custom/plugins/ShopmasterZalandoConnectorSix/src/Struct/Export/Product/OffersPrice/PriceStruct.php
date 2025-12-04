<?php

namespace ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class PriceStruct extends Struct
{
    const EUR_CURRENCY = 'EUR';

    /**
     * @var float
     */
    protected $amount;
    /**
     * @var string
     */
    protected $currency;

    /**
     * @param float $amount
     * @param string $currency
     */
    public function __construct(float $amount, string $currency = self::EUR_CURRENCY)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
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

}