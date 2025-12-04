<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\DalBundle\ExceptionHandling;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class UniqueIndexExceptionMapping
{
    private string $entityName;
    private string $uniqueIndexName;
    private string $errorCodeToAssign;
    private array $fields;

    public function __construct(string $entityName, string $uniqueIndexName, string $errorCodeToAssign, array $fields = [])
    {
        $this->entityName = $entityName;
        $this->uniqueIndexName = $uniqueIndexName;
        $this->errorCodeToAssign = $errorCodeToAssign;
        $this->fields = $fields;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getUniqueIndexName(): string
    {
        return $this->uniqueIndexName;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getErrorCodeToAssign(): string
    {
        return $this->errorCodeToAssign;
    }
}
