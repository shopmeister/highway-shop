<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */
 
declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1621866219CreateDDECustomPresetsTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1621866219;
    }

    public function update(Connection $connection): void
    {
        $createCustomPresetsTableSql = '
            CREATE TABLE IF NOT EXISTS `dde_custom_preset` (
                `id`            BINARY(16)      NOT NULL,
                `name`          VARCHAR(255)    NOT NULL,
                `data`          JSON            NULL,
                `created_at`    DATETIME(3)     NOT NULL,
                `updated_at`    DATETIME(3)     NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';

        $connection->executeStatement($createCustomPresetsTableSql);
    }

    public function updateDestructive(Connection $connection): void {}
}
