<?php declare(strict_types=1);

namespace Shm\OrderPrinter\Core\Checkout\PrinterSettings;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(DocumentSettingEntity $entity)
 * @method void              set(string $key, DocumentSettingEntity $entity)
 * @method DocumentSettingEntity[]    getIterator()
 * @method DocumentSettingEntity[]    getElements()
 * @method DocumentSettingEntity|null get(string $key)
 * @method DocumentSettingEntity|null first()
 * @method DocumentSettingEntity|null last()
 */
class PrinterSettingsCollection extends EntityCollection
{
    /**
     * @return string
     */
    protected function getExpectedClass(): string
    {
        return PrinterSettingsEntity::class;
    }
}
