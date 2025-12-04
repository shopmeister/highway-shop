<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */
 
declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\CustomPreset;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * Class EditorStateCollection
 * @method void                         add(CustomPresetEntity $entity)
 * @method void                         set(string $key, CustomPresetEntity $entity)
 * @method CustomPresetEntity[]          getIterator()
 * @method CustomPresetEntity[]          getElements()
 * @method CustomPresetEntity|null       get(string $key)
 * @method CustomPresetEntity|null       first()
 * @method CustomPresetEntity|null       last()
 */
class CustomPresetCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CustomPresetEntity::class;
    }
}
