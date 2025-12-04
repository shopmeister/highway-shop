<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product\Aggregate\ProductAvailability;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ProductAvailabilityEntity extends Entity
{
    /**
     * @var string
     */
    protected $productId;

    /**
     * @var string
     */
    protected $productVersionId;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @var bool|null
     */
    protected $soldout;

    /**
     * @var ProductEntity|null
     */
    protected $product;

    /**
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function isSoldout(): ?bool
    {
        return $this->soldout;
    }

    public function setSoldout(?bool $soldout): void
    {
        $this->soldout = $soldout;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }
}
