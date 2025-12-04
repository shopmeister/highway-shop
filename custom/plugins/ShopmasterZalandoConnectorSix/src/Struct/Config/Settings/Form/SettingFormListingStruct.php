<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form;

use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;

class SettingFormListingStruct extends SettingsFormStruct
{
    protected bool $activeListing = false;
    protected string $salesChannelId = '';
    protected int $listingInterval = 60;

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        
        // Ensure listingInterval is properly typed as integer
        if (isset($this->listingInterval)) {
            $this->listingInterval = (int) $this->listingInterval;
        }
        
        // Ensure activeListing is properly typed as boolean
        if (isset($this->activeListing)) {
            $this->activeListing = (bool) $this->activeListing;
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
    public function isActiveListing(): bool
    {
        return $this->activeListing;
    }

    /**
     * @param bool $activeListing
     * @return self
     */
    public function setActiveListing(bool $activeListing): self
    {
        $this->activeListing = $activeListing;
        return $this;
    }

    /**
     * @return int
     */
    public function getListingInterval(): int
    {
        return ($this->listingInterval < 5) ? 5 : (($this->listingInterval > 1440) ? 1440 : $this->listingInterval);
    }

    /**
     * @param int $listingInterval
     * @return self
     */
    public function setListingInterval(int $listingInterval): self
    {
        $this->listingInterval = $listingInterval;
        return $this;
    }
}