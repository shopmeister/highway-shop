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

class Migration1592817834CreateDDETables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1592817834;
    }

    public function update(Connection $connection): void
    {
        $createEditorStateTableSql = '
            CREATE TABLE IF NOT EXISTS `dde_editor_state` (
                `id`            BINARY(16)  NOT NULL,
                `document_base_config_id`   BINARY(16)  NOT NULL,
                `is_editor_enabled`   BOOLEAN NOT NULL DEFAULT FALSE,
                `created_at`    DATETIME(3) NOT NULL,
                `updated_at`    DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `uk.dde_editor_state.document_base_config_id` UNIQUE (`document_base_config_id`),
                CONSTRAINT `fk.dde_editor_state.document_base_config_id`
                    FOREIGN KEY (`document_base_config_id`)
                    REFERENCES `document_base_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';

        $createEditorStateTranslationTableSql = '
            CREATE TABLE IF NOT EXISTS `dde_editor_state_translation` (
                `dde_editor_state_id`  BINARY(16)  NOT NULL,
                `language_id`               BINARY(16)  NOT NULL,
                `data`                      JSON        NULL,
                `twig_template`             LONGTEXT    NULL,
                `default_styles`            JSON    NULL,
                `embedded_google_fonts`     JSON    NULL,
                `created_at`                DATETIME(3) NOT NULL,
                `updated_at`                DATETIME(3) NULL,
                PRIMARY KEY (`dde_editor_state_id`, `language_id`),
                CONSTRAINT `json.dde_editor_state_translation.data`
                    CHECK(JSON_VALID(`data`)),
                CONSTRAINT `fk.dde_editor_state_translation.dde_editor_state_id` FOREIGN KEY (`dde_editor_state_id`)
                    REFERENCES `dde_editor_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.dde_editor_state_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';

        $connection->executeUpdate($createEditorStateTableSql);
        $connection->executeUpdate($createEditorStateTranslationTableSql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
