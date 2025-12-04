<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form;

use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;

class SettingFormPriceReportSyncStruct extends SettingsFormStruct
{
    protected bool $active = false;

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