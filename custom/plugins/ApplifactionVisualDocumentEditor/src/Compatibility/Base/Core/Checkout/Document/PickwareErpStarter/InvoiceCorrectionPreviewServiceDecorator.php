<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\PickwareErpStarter;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentPreviewServiceInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Doctrine\DBAL\Exception;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Framework\Context;

class InvoiceCorrectionPreviewServiceDecorator implements DocumentPreviewServiceInterface
{

    const THEME_DUMMY_ORDER_NUMBER = 'EXAMPLE-2';
    private DocumentPreviewServiceInterface $decoratedService;
    private PickwareErpStarterService $pickwareErpStarterService;

    public function __construct(
        DocumentPreviewServiceInterface $documentPreviewService,
        PickwareErpStarterService       $pickwareErpStarterService
    )
    {
        $this->decoratedService = $documentPreviewService;
        $this->pickwareErpStarterService = $pickwareErpStarterService;
    }

    /**
     * @param DocumentEntity $document
     * @param Context $context
     * @return string
     * @throws Exception
     */
    public function getPdfDocument(DocumentEntity $document, Context $context): string
    {
        $this->prepareInvoiceCorrectionOrderNumber($document);
        return $this->decoratedService->getPdfDocument($document, $context);
    }

    /**
     * @param DocumentEntity $document
     * @param Context $context
     * @return string
     * @throws Exception
     */
    public function getHtmlDocument(DocumentEntity $document, Context $context): string
    {
        $this->prepareInvoiceCorrectionOrderNumber($document);
        return $this->decoratedService->getHtmlDocument($document, $context);
    }

    /**
     * @param DocumentEntity $document
     * @return void
     * @throws Exception
     */
    public function prepareInvoiceCorrectionOrderNumber(DocumentEntity $document): void
    {
        $config = $document->getConfig();
        if ($document->getDocumentType()->getTechnicalName() === PickwareErpStarterService::PICKWARE_ERP_INVOICE_CORRECTION && isset($config['orderNumber']) && $config['orderNumber'] === self::THEME_DUMMY_ORDER_NUMBER) {
            $config['orderNumber'] = $this->pickwareErpStarterService->fetchOrderNumberWithInvoiceCorrection();
            $document->setConfig($config);
        }
    }

    public function getRenderedDocument(EditorStateEntity $editorState, string $documentType, DocumentGenerateOperation $operation, Context $context): RenderedDocument
    {
        return $this->decoratedService->getRenderedDocument($editorState, $documentType, $operation, $context);
    }

}
