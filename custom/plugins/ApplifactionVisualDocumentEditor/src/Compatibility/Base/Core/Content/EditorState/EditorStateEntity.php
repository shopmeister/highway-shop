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

use Shopware\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class EditorStateEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var DocumentBaseConfigEntity
     */
    protected $documentBaseConfig;

    /**
     * @var string
     */
    protected $documentBaseConfigId;

    /**
     * @var bool
     */
    protected $isEditorEnabled;

    /**
     * @return DocumentBaseConfigEntity
     */
    public function getDocumentBaseConfig(): DocumentBaseConfigEntity
    {
        return $this->documentBaseConfig;
    }

    /**
     * @param DocumentBaseConfigEntity $documentBaseConfig
     */
    public function setDocumentBaseConfig(DocumentBaseConfigEntity $documentBaseConfig): void
    {
        $this->documentBaseConfig = $documentBaseConfig;
    }

    /**
     * @return string
     */
    public function getDocumentBaseConfigId(): string
    {
        return $this->documentBaseConfigId;
    }

    /**
     * @param string $documentBaseConfigId
     */
    public function setDocumentBaseConfigId(string $documentBaseConfigId): void
    {
        $this->documentBaseConfigId = $documentBaseConfigId;
    }

    /**
     * @return bool
     */
    public function isEditorEnabled(): bool
    {
        return $this->isEditorEnabled;
    }

    /**
     * @param bool $isEditorEnabled
     */
    public function setIsEditorEnabled(bool $isEditorEnabled): void
    {
        $this->isEditorEnabled = $isEditorEnabled;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getTwigTemplate(): string
    {
        return $this->twigTemplate;
    }

    /**
     * @param string $twigTemplate
     */
    public function setTwigTemplate(string $twigTemplate): void
    {
        $this->twigTemplate = $twigTemplate;
    }
}
