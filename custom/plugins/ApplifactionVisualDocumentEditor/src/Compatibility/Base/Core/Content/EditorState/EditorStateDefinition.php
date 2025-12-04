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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\Aggregate\EditorStateTranslationDefinition;
use Shopware\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EditorStateDefinition extends EntityDefinition
{
    public function getEntityClass(): string
    {
        return EditorStateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return EditorStateCollection::class;
    }

    public function getEntityName(): string
    {
        return 'dde_editor_state';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))
                ->addFlags(new Required(), new PrimaryKey()),

            new FkField('document_base_config_id', 'documentBaseConfigId', DocumentBaseConfigDefinition::class),

            new OneToOneAssociationField(
                'documentBaseConfig',
                'document_base_config_id',
                'id',
                DocumentBaseConfigDefinition::class
            ),

            new BoolField('is_editor_enabled', 'isEditorEnabled'),
            new TranslatedField('data'),
            new TranslatedField('twigTemplate'),
            new TranslatedField('defaultStyles'),
            new TranslatedField('embeddedGoogleFonts'),

            new TranslationsAssociationField(
                EditorStateTranslationDefinition::class,
                'dde_editor_state_id'
            )
        ]);
    }
}
