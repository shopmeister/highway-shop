<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\ShopwareExtensionsBundle\StateTransitioning;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Defines the target state field
 */
#[Exclude]
class EntityStateDefinition
{
    /**
     * @param class-string<EntityDefinition> $entityDefinitionClassName The definition class name of the entity
     *     containing the state
     * @param string $stateIdFieldName The name of the field in the entity that contains the state ID
     */
    public function __construct(
        readonly private string $entityDefinitionClassName,
        readonly private string $stateIdFieldName,
    ) {}

    public static function order(): self
    {
        return new self(OrderDefinition::class, 'stateId');
    }

    public function getEntityDefinitionClassName(): string
    {
        return $this->entityDefinitionClassName;
    }

    public function getStateIdFieldName(): string
    {
        return $this->stateIdFieldName;
    }
}
