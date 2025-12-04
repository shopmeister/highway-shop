<?php

namespace ShopmasterZalandoConnectorSix\Struct\Import\Product\PriceReport;

use ShopmasterZalandoConnectorSix\Core\Content\Zalando\PriceReport\ZalandoPriceReportEntity;
use ShopmasterZalandoConnectorSix\Struct\Struct;

class PriceReportStruct extends Struct
{
    /**
     * @var string
     */
    protected string $id;
    /**
     * @var string
     */
    protected string $productId;
    /**
     * @var string
     */
    protected string $zSalesChannelId;
    /**
     * @var float
     */
    protected float $baseRegularPriceAmount;
    /**
     * @var string
     */
    protected string $baseRegularPriceCurrency;
    /**
     * @var float|null
     */
    protected ?float $basePromotionalPriceAmount;
    /**
     * @var string|null
     */
    protected ?string $basePromotionalPriceCurrency;
    /**
     * @var int
     */
    protected int $zStatusId;

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     * @return $this
     */
    public function setProductId(string $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return string
     */
    public function getZSalesChannelId(): string
    {
        return $this->zSalesChannelId;
    }

    /**
     * @param string $zSalesChannelId
     * @return $this
     */
    public function setZSalesChannelId(string $zSalesChannelId): self
    {
        $this->zSalesChannelId = $zSalesChannelId;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseRegularPriceAmount(): float
    {
        return $this->baseRegularPriceAmount;
    }

    /**
     * @param float $baseRegularPriceAmount
     * @return $this
     */
    public function setBaseRegularPriceAmount(float $baseRegularPriceAmount): self
    {
        $this->baseRegularPriceAmount = $baseRegularPriceAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseRegularPriceCurrency(): string
    {
        return $this->baseRegularPriceCurrency;
    }

    /**
     * @param string $baseRegularPriceCurrency
     * @return $this
     */
    public function setBaseRegularPriceCurrency(string $baseRegularPriceCurrency): self
    {
        $this->baseRegularPriceCurrency = $baseRegularPriceCurrency;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getBasePromotionalPriceAmount(): ?float
    {
        return $this->basePromotionalPriceAmount;
    }

    /**
     * @param float|null $basePromotionalPriceAmount
     * @return $this
     */
    public function setBasePromotionalPriceAmount(?float $basePromotionalPriceAmount): self
    {
        $this->basePromotionalPriceAmount = $basePromotionalPriceAmount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBasePromotionalPriceCurrency(): ?string
    {
        return $this->basePromotionalPriceCurrency;
    }

    /**
     * @param string|null $basePromotionalPriceCurrency
     * @return $this
     */
    public function setBasePromotionalPriceCurrency(?string $basePromotionalPriceCurrency): self
    {
        $this->basePromotionalPriceCurrency = $basePromotionalPriceCurrency;
        return $this;
    }

    /**
     * @return int
     */
    public function getZStatusId(): int
    {
        return $this->zStatusId;
    }

    /**
     * @param int $zStatusId
     * @return $this
     */
    public function setZStatusId(int $zStatusId): self
    {
        $this->zStatusId = $zStatusId;
        return $this;
    }

    public function setZStatusType(string $type): self
    {
        $this->setZStatusId(ZalandoPriceReportEntity::getStatusIdByType($type));
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}