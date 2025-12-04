<?php

namespace ShopmasterZalandoConnectorSix\Struct\Product\Psr\Offer;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class PsrProductOfferStruct extends Struct
{
    /**
     * @var int|null
     */
    protected ?int $stock;
    /**
     * @var string
     */
    protected string $countryCode;
    /**
     * @var float|null
     */
    protected ?float $regularPrice;
    /**
     * @var float|null
     */
    protected ?float $discountedPrice;

    /**
     * @return int|null
     */
    public function getStock(): ?int
    {
        return $this->stock;
    }

    /**
     * @param int|null $stock
     * @return $this
     */
    public function setStock(?int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getDiscountedPrice(): ?float
    {
        return $this->discountedPrice;
    }

    /**
     * @param float|null $discountedPrice
     * @return $this
     */
    public function setDiscountedPrice(?float $discountedPrice): self
    {
        $this->discountedPrice = $discountedPrice;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getRegularPrice(): ?float
    {
        return $this->regularPrice;
    }

    /**
     * @param float|null $regularPrice
     * @return $this
     */
    public function setRegularPrice(?float $regularPrice): self
    {
        $this->regularPrice = $regularPrice;
        return $this;
    }
}