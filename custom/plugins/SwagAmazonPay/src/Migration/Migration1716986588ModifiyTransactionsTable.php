<?php declare(strict_types=1);

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1716986588ModifiyTransactionsTable extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1716986588;
    }

    public function update(Connection $connection): void
    {

        $sql = <<<SQL
            ALTER TABLE `swag_amazon_pay_transaction` MODIFY COLUMN `status` VARCHAR(32) DEFAULT NULL;
SQL;

        $connection->executeStatement($sql);
    }
}
