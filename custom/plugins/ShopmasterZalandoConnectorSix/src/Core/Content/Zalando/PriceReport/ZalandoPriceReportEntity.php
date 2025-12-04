<?php

namespace ShopmasterZalandoConnectorSix\Core\Content\Zalando\PriceReport;

use ShopmasterZalandoConnectorSix\Struct\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ZalandoPriceReportEntity extends Entity
{
    const STATUS = [
        1 => 'RECEIVED',
        2 => 'ACCEPTED',
        3 => 'REJECTED',
        4 => 'FAILED',
        5 => 'SCHEDULED',
        6 => 'OVERRIDDEN',
        7 => 'SUBMITTED',
        8 => 'AWAITING_ONBOARDING',
    ];

    use EntityIdTrait;

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
     * @return ZalandoPriceReportEntity
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
     * @return ZalandoPriceReportEntity
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
     * @return ZalandoPriceReportEntity
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
     * @return ZalandoPriceReportEntity
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
     * @return ZalandoPriceReportEntity
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
     * @return ZalandoPriceReportEntity
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
     * @return ZalandoPriceReportEntity
     */
    public function setZStatusId(int $zStatusId): self
    {
        $this->zStatusId = $zStatusId;
        return $this;
    }

    public static function getStatusIdByType(string $type): int
    {
        return array_search($type, self::STATUS);
    }

    public static function getStatusTypeById(int $id): string
    {
        return self::STATUS[$id];
    }
}