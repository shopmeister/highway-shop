<?php

namespace ShopmasterZalandoConnectorSix\Struct\Api\Zalando\PriceAttempt;

use ShopmasterZalandoConnectorSix\Struct\Api\ApiSetStruct;

class PriceAttemptSetStruct extends ApiSetStruct
{
    /**
     * @var string
     */
    protected string $modified_since;
    /**
     * @var string
     */
    protected string $modified_until;
    /**
     * @var int
     */
    protected int $page_size;
    /**
     * @var string[]
     */
    protected array $sales_channels = [];
    /**
     * @var string[]
     */
    protected array $eans = [];

    /**
     *
     */
    public function __construct()
    {
        $currentTime = 'T' . date('H:i:s') . 'Z';
        $this->setModifiedSince(date('Y-m-d', strtotime('-1 days')) . $currentTime);
        $this->setModifiedUntil(date('Y-m-d') . $currentTime);
    }

    /**
     * @return string
     */
    public function getModifiedSince(): string
    {
        return $this->modified_since;
    }

    /**
     * @param string $modified_since
     * @return self
     */
    public function setModifiedSince(string $modified_since): self
    {
        $this->modified_since = $modified_since;
        return $this;
    }

    /**
     * @return string
     */
    public function getModifiedUntil(): string
    {
        return $this->modified_until;
    }

    /**
     * @param string $modified_until
     * @return self
     */
    public function setModifiedUntil(string $modified_until): self
    {
        $this->modified_until = $modified_until;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->page_size;
    }

    /**
     * @param int $page_size
     * @return self
     */
    public function setPageSize(int $page_size): self
    {
        $this->page_size = $page_size;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSalesChannels(): array
    {
        return $this->sales_channels;
    }

    /**
     * @param string[] $sales_channels
     * @return self
     */
    public function setSalesChannels(array $sales_channels): self
    {
        $this->sales_channels = $sales_channels;
        return $this;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function addSalesChannelId(string $id): self
    {
        $this->sales_channels[] = $id;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getEans(): array
    {
        return $this->eans;
    }

    /**
     * @param string[] $eans
     * @return self
     */
    public function setEans(array $eans): self
    {
        $this->eans = array_values($eans);
        return $this;
    }

    /**
     * @param string $ean
     * @return $this
     */
    public function addEan(string $ean): self
    {
        $this->eans[] = $ean;
        return $this;
    }


}