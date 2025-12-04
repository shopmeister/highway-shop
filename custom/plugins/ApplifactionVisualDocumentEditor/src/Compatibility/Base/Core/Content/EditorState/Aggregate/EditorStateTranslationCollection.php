<?php 
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */
 
declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EditorStateTranslationCollection extends EntityCollection
{
    /**
     * @method void                               add(EditorStateTranslationEntity $entity)
     * @method void                               set(string $key, EditorStateTranslationEntity $entity)
     * @method EditorStateTranslationEntity[]     getIterator()
     * @method EditorStateTranslationEntity[]     getElements()
     * @method EditorStateTranslationEntity|null  get(string $key)
     * @method EditorStateTranslationEntity|null  first()
     * @method EditorStateTranslationEntity|null  last()
     */

    protected function getExpectedClass(): string
    {
        return EditorStateTranslationEntity::class;
    }
}
