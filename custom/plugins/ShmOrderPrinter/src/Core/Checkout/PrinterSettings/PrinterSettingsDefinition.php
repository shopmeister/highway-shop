<?php declare(strict_types=1);

namespace Shm\OrderPrinter\Core\Checkout\PrinterSettings;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PrinterSettingsDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'shm_printer_settings';

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return PrinterSettingsEntity::class;
    }

    /**
     * @return string
     */
    public function getCollectionClass(): string
    {
        return PrinterSettingsCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new JsonField('setting', 'setting'))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}
