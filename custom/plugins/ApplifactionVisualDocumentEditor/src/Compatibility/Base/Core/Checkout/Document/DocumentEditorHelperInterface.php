<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Framework\Context;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;

interface DocumentEditorHelperInterface
{

    public function loadEditorState($documentBaseConfigId, Context $context, $languageId = null): ?EditorStateEntity;

    public function upsertEditorStates(array $editorStateData, $languageId, Context $context);

    public function replaceHtmlSpecialChars($twigTemplate);

    public function getTranslatedTwigTemplateHashes($documentBaseConfigId, Context $context);

    public function getOrderLanguageId(string $orderId): string;

    public function getLanguageIdByLocaleCode(string $localeCode): string;

    public function getOrderId(string $orderNumber): string;

    public function renderHtml(EditorStateEntity $editorState, array $templateData): string;

    public function getDomPdfOptions(EditorStateEntity $editorState);

    public function getLocaleCodeByLanguageId(string $languageId): string;

    public function getDocumentTypeByName(string $documentType): ?string;

    public function getDocumentTypeByDocumentBaseConfigId(string $documentBaseConfigId): ?string;

    public function getConfiguration(Context $context, string $documentTypeId, string $orderId, ?array $specificConfiguration): DocumentConfiguration;

}
