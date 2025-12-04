<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\Order;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiParamsStruct;

class OrderParamsStruct extends ApiParamsStruct
{
    private ?int $pageNumber = null;
    private ?int $pageSize = null;

    protected ?array $page = null;
    protected ?string $sort = null;
    protected ?\DateTime $created_after = null;
    protected ?\DateTime $created_before = null;
    protected ?\DateTime $last_updated_after = null;
    protected ?\DateTime $last_updated_before = null;
    protected ?string $order_status = null;
    protected ?string $order_type = null;
    protected ?string $order_number = null;
    protected ?string $sales_channel_id = null;
    protected ?string $locale = null;
    protected ?bool $exported = null;

    public function getPageNumber(): ?int
    {
        return $this->pageNumber;
    }

    public function setPageNumber(?int $pageNumber): self
    {
        $this->pageNumber = $pageNumber;
        return $this;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(?int $pageSize): self
    {
        if ($pageSize > 100) {
            $pageSize = 100;
        }
        $this->pageSize = $pageSize;
        return $this;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function setSort(?string $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    public function getCreatedAfter(): ?\DateTime
    {
        return $this->created_after;
    }

    public function setCreatedAfter(?\DateTime $created_after): self
    {
        $this->created_after = $created_after;
        return $this;
    }

    public function getCreatedBefore(): ?\DateTime
    {
        return $this->created_before;
    }

    public function setCreatedBefore(?\DateTime $created_before): self
    {
        $this->created_before = $created_before;
        return $this;
    }

    public function getLastUpdatedAfter(): ?\DateTime
    {
        return $this->last_updated_after;
    }

    public function setLastUpdatedAfter(?\DateTime $last_updated_after): self
    {
        $this->last_updated_after = $last_updated_after;
        return $this;
    }

    public function getLastUpdatedBefore(): ?\DateTime
    {
        return $this->last_updated_before;
    }

    public function setLastUpdatedBefore(?\DateTime $last_updated_before): self
    {
        $this->last_updated_before = $last_updated_before;
        return $this;
    }

    public function getOrderStatus(): ?string
    {
        return $this->order_status;
    }

    public function setOrderStatus(?string $order_status): self
    {
        $this->order_status = $order_status;
        return $this;
    }

    public function getOrderType(): ?string
    {
        return $this->order_type;
    }

    public function setOrderType(?string $order_type): self
    {
        $this->order_type = $order_type;
        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->order_number;
    }

    public function setOrderNumber(?string $order_number): self
    {
        $this->order_number = $order_number;
        return $this;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->sales_channel_id;
    }

    public function setSalesChannelId(?string $sales_channel_id): self
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getExported(): ?bool
    {
        return $this->exported;
    }

    public function setExported(?bool $exported): self
    {
        $this->exported = $exported;
        return $this;
    }

    public function toArray(): ?array
    {
        if ($this->getPageSize()) {
            $this->page['size'] = $this->getPageSize();
        }
        if ($this->getPageNumber()) {
            $this->page['number'] = $this->getPageNumber();
        }
        return parent::toArray();
    }


}