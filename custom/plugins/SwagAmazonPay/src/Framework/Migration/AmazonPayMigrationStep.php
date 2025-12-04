<?php declare(strict_types=1);

namespace Swag\AmazonPay\Framework\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

abstract class AmazonPayMigrationStep extends MigrationStep
{
    abstract public function getCreationTimestamp(): int;

    abstract public function update(Connection $connection): void;

    public function updateDestructive(Connection $connection): void
    {
    }
}
