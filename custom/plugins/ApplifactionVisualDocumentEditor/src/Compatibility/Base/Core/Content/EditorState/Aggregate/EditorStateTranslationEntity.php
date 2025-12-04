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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class EditorStateTranslationEntity extends TranslationEntity
{
    /**
     * @var string $stateId
     */
    protected $stateId;

    /**
     * @var EditorStateEntity|null $state
     */
    protected $state;

    /**
     * @var array|null $data
     */
    protected $data;

    /**
     * @var string|null $twigTemplate
     */
    protected $twigTemplate;

    /**
     * @var string|null $defaultStyles
     */
    protected $defaultStyles;

    /**
     * @var string|null $embeddedGoogleFonts
     */
    protected $embeddedGoogleFonts;

    /**
     * @return string
     */
    public function getStateId(): string
    {
        return $this->stateId;
    }

    /**
     * @param string $stateId
     */
    public function setStateId(string $stateId): void
    {
        $this->stateId = $stateId;
    }

    /**
     * @return EditorStateEntity|null
     */
    public function getState(): ?EditorStateEntity
    {
        return $this->state;
    }

    /**
     * @param EditorStateEntity|null $state
     */
    public function setState(?EditorStateEntity $state): void
    {
        $this->state = $state;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string|null
     */
    public function getTwigTemplate(): ?string
    {
        return $this->twigTemplate;
    }

    /**
     * @param string|null $twigTemplate
     */
    public function setTwigTemplate(?string $twigTemplate): void
    {
        $this->twigTemplate = $twigTemplate;
    }

    /**
     * @return string|null
     */
    public function getDefaultStyles(): ?string
    {
        return $this->defaultStyles;
    }

    /**
     * @param string|null $defaultStyles
     */
    public function setDefaultStyles(?string $defaultStyles): void
    {
        $this->defaultStyles = $defaultStyles;
    }

    /**
     * @return string|null
     */
    public function getEmbeddedGoogleFonts(): ?string
    {
        return $this->embeddedGoogleFonts;
    }

    /**
     * @param string|null $embeddedGoogleFonts
     */
    public function setEmbeddedGoogleFonts(?string $embeddedGoogleFonts): void
    {
        $this->embeddedGoogleFonts = $embeddedGoogleFonts;
    }
}
