<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form;

use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;

class SettingFormPriceSyncStruct extends SettingsFormStruct
{

    protected string $salesChannelId = '';
    protected ?string $ruleId = null;
    protected bool $ignoreWarnings = false;
    protected bool $activePromotionalPrice = true;
    protected bool $active = false;
    protected bool $autoAdjustPromotionalPriceTo15Percent = false;


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
     * @return string|null
     */
    public function getRuleId(): ?string
    {
        return $this->ruleId;
    }

    /**
     * @param string|null $ruleId
     * @return self
     */
    public function setRuleId(?string $ruleId): self
    {
        $this->ruleId = $ruleId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreWarnings(): bool
    {
        return $this->ignoreWarnings;
    }

    /**
     * @param bool $ignoreWarnings
     * @return self
     */
    public function setIgnoreWarnings(bool $ignoreWarnings): self
    {
        $this->ignoreWarnings = $ignoreWarnings;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivePromotionalPrice(): bool
    {
        return $this->activePromotionalPrice;
    }

    /**
     * @param bool $activePromotionalPrice
     * @return self
     */
    public function setActivePromotionalPrice(bool $activePromotionalPrice): self
    {
        $this->activePromotionalPrice = $activePromotionalPrice;
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

    /**
     * @return bool
     */
    public function isAutoAdjustPromotionalPriceTo15Percent(): bool
    {
        return $this->autoAdjustPromotionalPriceTo15Percent;
    }

    /**
     * @param bool $autoAdjustPromotionalPriceTo15Percent
     * @return self
     */
    public function setAutoAdjustPromotionalPriceTo15Percent(bool $autoAdjustPromotionalPriceTo15Percent): self
    {
        $this->autoAdjustPromotionalPriceTo15Percent = $autoAdjustPromotionalPriceTo15Percent;
        return $this;
    }


}