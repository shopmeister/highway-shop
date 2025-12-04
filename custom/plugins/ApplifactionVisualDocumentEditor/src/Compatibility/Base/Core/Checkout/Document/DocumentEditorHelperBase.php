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

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Doctrine\DBAL\Exception;
use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Framework\Context;
use Doctrine\DBAL\Connection;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Shopware\Core\Framework\Uuid\Uuid;

abstract class DocumentEditorHelperBase implements DocumentEditorHelperInterface
{

    protected TwigRenderer $twigRenderer;

    protected Connection $connection;

    protected OrderDocumentResolverInterface $orderDocumentResolver;

    public function __construct(
        Connection                         $connection,
        TwigRenderer                       $twigRenderer,
        OrderDocumentResolverInterface     $orderDocumentResolver
    )
    {
        $this->connection = $connection;
        $this->twigRenderer = $twigRenderer;
        $this->orderDocumentResolver = $orderDocumentResolver;
    }

    public abstract function loadEditorState($documentBaseConfigId, Context $context, $languageId = null): ?EditorStateEntity;

    public abstract function upsertEditorStates(array $editorStateData, $languageId, Context $context);

    public function replaceHtmlSpecialChars($twigTemplate): string
    {
        $twigTemplate = preg_replace("/url\(&quot;(.*)&quot;\)/", "url('$1')", $twigTemplate);
        return htmlspecialchars_decode(htmlspecialchars_decode($twigTemplate));
    }

    public abstract function getTranslatedTwigTemplateHashes($documentBaseConfigId, Context $context);

    /**
     * @throws Exception
     */
    public function getOrderLanguageId(string $orderId): string
    {
        return (string)$this->connection->fetchOne(
            'SELECT LOWER(HEX(language_id)) FROM `order` WHERE `id` = :orderId LIMIT 1',
            ['orderId' => Uuid::fromHexToBytes($orderId)],
        );
    }

    /**
     * @throws Exception
     */
    public function getLanguageIdByLocaleCode(string $localeCode): string
    {
        return (string)$this->connection->fetchOne(
            'SELECT LOWER(HEX(la.id)) as language_id FROM `language` la 
                INNER JOIN `locale` lo ON la.locale_id = lo.id AND LOWER(lo.code) = LOWER(:localeCode) 
                LIMIT 1',
            ['localeCode' => $localeCode],
        );
    }
    public function getLocaleCodeByLanguageId(string $languageId): string
    {
        return (string)$this->connection->fetchOne(
            'SELECT LOWER(lo.code) FROM `language` la
                INNER JOIN `locale` lo ON la.locale_id = lo.id
                WHERE LOWER(HEX(la.id)) = :languageId
                LIMIT 1',
            ['languageId' => $languageId],
        );
    }

    /**
     * @throws Exception
     */
    public function getOrderId(string $orderNumber): string
    {
        return (string)$this->connection->fetchOne(
            'SELECT id FROM `order` WHERE `order_number` = :orderNumber LIMIT 1',
            ['orderNumber' => $orderNumber],
        );
    }

    /**
     * @throws Exception
     */
    public function getDocumentTypeByName(string $documentType): ?string
    {
        $id = $this->connection->fetchOne(
            'SELECT LOWER(HEX(id)) as id FROM document_type WHERE technical_name = :technicalName LIMIT 1',
            ['technicalName' => $documentType]
        );

        return $id ?: null;
    }

    /**
     * @throws Exception
     */
    public function getDocumentTypeByDocumentBaseConfigId(string $documentBaseConfigId): ?string
    {
        $documentType = $this->connection->fetchOne(
            "SELECT dt.technical_name AS document_type
                    FROM `document_base_config` dbc
                             INNER JOIN document_type dt ON dbc.document_type_id = dt.id
                    WHERE LOWER(HEX(dbc.id)) = :document_base_config_id LIMIT 1",
            ['document_base_config_id' => $documentBaseConfigId]
        );

        return $documentType ?: null;
    }

    public function renderHtml(EditorStateEntity $editorState, array $templateData): string
    {
        $translated = $editorState->getTranslated();
        $twigTemplate = $this->replaceHtmlSpecialChars($translated['twigTemplate']);
        return $this->twigRenderer->renderHtml($twigTemplate, $templateData);
    }

    public function getDomPdfOptions(EditorStateEntity $editorState)
    {
        $attributes = $editorState->data['attributes'] ?? [];
        return $attributes['dompdfOptions'] ?? [];
    }

    /**
     * @param array<string, int|string>|null $specificConfiguration
     */
    public abstract function getConfiguration(Context $context, string $documentTypeId, string $orderId, ?array $specificConfiguration): DocumentConfiguration;

}
