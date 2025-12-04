<?php declare(strict_types=1);

namespace Swag\AmazonPay\Migration;

use Doctrine\DBAL\Connection;
use Swag\AmazonPay\Framework\Migration\AmazonPayMigrationStep;

class Migration1710946562RemoveFlowTemplate extends AmazonPayMigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1710946562;
    }

    public function update(Connection $connection): void
    {

        $sql = "DELETE FROM `flow_template` WHERE `id` = '620e502c762d4524955231633a319f91'";
        try{
            $connection->executeStatement($sql);
        } catch (\Exception $e) {
            // ignore
        }

    }
}
