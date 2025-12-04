<?php

namespace ShopmasterZalandoConnectorSix\Struct\Export\Product\OffersPrice;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class OffersPriceStruct extends Struct
{
    /**
     * @var string
     */
    protected string $ean;
    /**
     * @var string
     */
    protected string $sales_channel_id;
    /**
     * @var PriceStruct
     */
    protected PriceStruct $regular_price;
    /**
     * @var PriceStruct|null
     */
    protected ?PriceStruct $promotional_price = null;
    /**
     * @var bool
     */
    protected bool $ignore_warnings = false;

    /**
     * @var string
     */
    private string $articleNumber;

    /**
     * @return string
     */
    public function getEan(): string
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     * @return self
     */
    public function setEan(string $ean): self
    {
        $this->ean = $ean;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->sales_channel_id;
    }

    /**
     * @param string $sales_channel_id
     * @return self
     */
    public function setSalesChannelId(string $sales_channel_id): self
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @return PriceStruct
     */
    public function getRegularPrice(): PriceStruct
    {
        return $this->regular_price;
    }

    /**
     * @param PriceStruct $regular_price
     * @return self
     */
    public function setRegularPrice(PriceStruct $regular_price): self
    {
        $this->regular_price = $regular_price;
        return $this;
    }

    /**
     * @return PriceStruct|null
     */
    public function getPromotionalPrice(): ?PriceStruct
    {
        return $this->promotional_price;
    }

    /**
     * @param PriceStruct|null $promotional_price
     * @return self
     */
    public function setPromotionalPrice(?PriceStruct $promotional_price): self
    {
        $this->promotional_price = $promotional_price;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreWarnings(): bool
    {
        return $this->ignore_warnings;
    }

    /**
     * @param bool $ignore_warnings
     * @return self
     */
    public function setIgnoreWarnings(bool $ignore_warnings): self
    {
        $this->ignore_warnings = $ignore_warnings;
        return $this;
    }

    /**
     * @return string
     */
    public function getArticleNumber(): string
    {
        return $this->articleNumber;
    }

    /**
     * @param string $articleNumber
     * @return self
     */
    public function setArticleNumber(string $articleNumber): self
    {
        $this->articleNumber = $articleNumber;
        return $this;
    }

}
