<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */
 
declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * Class EditorStateCollection
 * @method void                         add(EditorStateEntity $entity)
 * @method void                         set(string $key, EditorStateEntity $entity)
 * @method EditorStateEntity[]          getIterator()
 * @method EditorStateEntity[]          getElements()
 * @method EditorStateEntity|null       get(string $key)
 * @method EditorStateEntity|null       first()
 * @method EditorStateEntity|null       last()
 */
class EditorStateCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EditorStateEntity::class;
    }
}
