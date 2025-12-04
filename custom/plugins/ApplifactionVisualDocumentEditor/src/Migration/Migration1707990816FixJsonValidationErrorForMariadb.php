<?php declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1707990816FixJsonValidationErrorForMariadb extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1707990816;
    }

    public function update(Connection $connection): void
    {

        $hasConstraintSql = "SELECT COUNT(*) as hasConstraint
                FROM information_schema.table_constraints
                WHERE table_name = 'dde_editor_state_translation'
                AND constraint_name = 'json.dde_editor_state_translation.data';";
        $hasConstraintResult = $connection->fetchOne($hasConstraintSql);

        if ($hasConstraintResult === "1") {
            $dropConstraintSql = "ALTER TABLE `dde_editor_state_translation` DROP CONSTRAINT `json.dde_editor_state_translation.data`;";
            $connection->executeStatement($dropConstraintSql);
        }

        $modifyDataToLongtextSql = "ALTER TABLE `dde_editor_state_translation` MODIFY `data` LONGTEXT;";
        $connection->executeStatement($modifyDataToLongtextSql);

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
