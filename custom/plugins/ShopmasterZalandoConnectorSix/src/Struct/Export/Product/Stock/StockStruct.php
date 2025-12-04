<?php

namespace ShopmasterZalandoConnectorSix\Struct\Export\Product\Stock;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class StockStruct extends Struct
{
    /**
     * @var string
     */
    protected string $sales_channel_id = '';
    /**
     * @var string
     */
    protected string $ean = '';
    /**
     * @var int
     */
    protected int $quantity = 0;

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->sales_channel_id;
    }

    /**
     * @param string $sales_channel_id
     * @return $this
     */
    public function setSalesChannelId(string $sales_channel_id): self
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEan(): string
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     * @return $this
     */
    public function setEan(string $ean): self
    {
        $this->ean = $ean;
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
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

}