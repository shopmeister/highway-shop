<?php declare(strict_types=1);

namespace Shm\OrderPrinter\Core\Checkout\PrinterSettings;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PrinterSettingsEntity extends Entity
{
    use EntityIdTrait;

    protected array $setting;

    /**
     * @return array
     */
    public function getSetting(): array
    {
        return $this->setting;
    }

    /**
     * @param array $setting
     */
    public function setSetting(array $setting): void
    {
        $this->setting = $setting;
    }
}
