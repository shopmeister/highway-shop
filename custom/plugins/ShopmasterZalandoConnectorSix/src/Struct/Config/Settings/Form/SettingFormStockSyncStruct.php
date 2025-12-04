<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form;

use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;

class SettingFormStockSyncStruct extends SettingsFormStruct
{

    protected bool $active = false;
    protected string $salesChannelId = '';
    protected bool $isHoliday = false;
    protected bool $isIndividualStock = false;
    protected int $stockCache = 0;

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        
        // Ensure stockCache is properly typed as integer
        if (isset($this->stockCache)) {
            $this->stockCache = (int) $this->stockCache;
        }
    }


    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * @return bool
     */
    public function isHoliday(): bool
    {
        return $this->isHoliday;
    }

    /**
     * @param bool $isHoliday
     * @return self
     */
    public function setHoliday(bool $isHoliday): self
    {
        $this->isHoliday = $isHoliday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIndividualStock(): bool
    {
        return $this->isIndividualStock;
    }

    /**
     * @param bool $isIndividualStock
     * @return self
     */
    public function setIndividualStock(bool $isIndividualStock): self
    {
        $this->isIndividualStock = $isIndividualStock;
        return $this;
    }

    /**
     * @return int
     */
    public function getStockCache(): int
    {
        return ($this->stockCache < 0) ? 0 : $this->stockCache;
    }

    /**
     * @param int|null $stockCache
     * @return self
     */
    public function setStockCache(int $stockCache): self
    {
        $this->stockCache = $stockCache;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return self
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

}