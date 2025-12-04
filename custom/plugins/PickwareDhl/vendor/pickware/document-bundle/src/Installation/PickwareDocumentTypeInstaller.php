<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\DocumentBundle\Installation;

use Doctrine\DBAL\Connection;

class PickwareDocumentTypeInstaller
{
    public function __construct(private readonly Connection $connection) {}

    public function ensureDocumentType(DocumentType $documentType): void
    {
        $sql = '
            INSERT INTO `pickware_document_type`
                (`technical_name`, `singular_description`, `plural_description`, `created_at`)
            VALUES
                (:technicalName, :singularDescription, :pluralDescription, UTC_TIMESTAMP(3))
            ON DUPLICATE KEY UPDATE
                `singular_description` = VALUES(`singular_description`),
                `plural_description` = VALUES(`plural_description`),
                `updated_at` = UTC_TIMESTAMP(3)';

        $this->connection->executeStatement($sql, [
            'technicalName' => $documentType->getTechnicalName(),
            'singularDescription' => json_encode($documentType->getDescriptionSingular()),
            'pluralDescription' => json_encode($documentType->getDescriptionPlural()),
        ]);
    }
}
