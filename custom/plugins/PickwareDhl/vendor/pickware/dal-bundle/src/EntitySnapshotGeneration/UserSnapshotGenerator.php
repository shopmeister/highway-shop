<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\DalBundle\EntitySnapshotGeneration;

use Pickware\DalBundle\EntityManager;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\User\UserDefinition;
use Shopware\Core\System\User\UserEntity;

#[AsEntitySnapshotGenerator(entityClass: UserDefinition::class)]
class UserSnapshotGenerator implements EntitySnapshotGenerator
{
    public function __construct(
        private readonly EntityManager $entityManager,
    ) {}

    public function generateSnapshots(array $ids, Context $context): array
    {
        return $this->entityManager->findBy(
            UserDefinition::class,
            ['id' => $ids],
            $context,
        )->map(fn(UserEntity $user) => [
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ]);
    }
}
