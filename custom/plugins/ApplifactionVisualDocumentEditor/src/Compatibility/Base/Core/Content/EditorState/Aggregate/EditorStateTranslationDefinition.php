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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateDefinition;
use Shopware\Core\Framework\App\Manifest\Xml\CustomFieldTypes\TextField;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EditorStateTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'dde_editor_state_translation';
    }

    public function getCollectionClass(): string
    {
        return EditorStateTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return EditorStateTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return EditorStateDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new JsonField('data', 'data'),
            new LongTextField('twig_template', 'twigTemplate'),
            new JsonField('default_styles', 'defaultStyles'),
            new JsonField('embedded_google_fonts', 'embeddedGoogleFonts')
        ]);
    }
}
