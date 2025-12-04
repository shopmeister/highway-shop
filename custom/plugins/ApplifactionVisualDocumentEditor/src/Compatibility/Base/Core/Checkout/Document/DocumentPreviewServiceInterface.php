<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;

interface DocumentPreviewServiceInterface
{

    public function getPdfDocument(DocumentEntity $document, Context $context): string;

    public function getHtmlDocument(DocumentEntity $document, Context $context): string;

    public function getRenderedDocument(EditorStateEntity $editorState, string $documentType, DocumentGenerateOperation $operation, Context $context): RenderedDocument;

}
