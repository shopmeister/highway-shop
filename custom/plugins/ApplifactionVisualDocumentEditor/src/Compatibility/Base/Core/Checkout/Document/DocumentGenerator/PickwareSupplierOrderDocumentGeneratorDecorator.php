<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentGenerator;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\CompleteDocumentConfigurationFactory;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\PdfRenderer;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Framework\Context;
use Pickware\PickwareErpStarter\SupplierOrder\Document\SupplierOrderDocumentGenerator;

class PickwareSupplierOrderDocumentGeneratorDecorator extends SupplierOrderDocumentGenerator
{
    public const DOCUMENT_TEMPLATE_FILE = '@PickwareErpStarter/documents/supplier-order.html.twig';

    protected string $rootDir;

    private SupplierOrderDocumentGenerator $decoratedService;
    private DocumentEditorHelperInterface $documentEditorHelper;
    private PdfRenderer $pdfRenderer;

    public function __construct(
        string                         $rootDir,
        SupplierOrderDocumentGenerator $decoratedService,
        DocumentEditorHelperInterface  $documentEditorHelper,
        PdfRenderer                    $pdfRenderer
    )
    {
        $this->rootDir = $rootDir;
        $this->decoratedService = $decoratedService;
        $this->documentEditorHelper = $documentEditorHelper;
        $this->pdfRenderer = $pdfRenderer;
    }

    public function supports(): string
    {
        if (!class_exists('Pickware\PickwareErpStarter\SupplierOrder\SupplierOrderDocumentType')) return '';
        return 'pickware_erp_supplier_order';
    }

    public function generate(array $templateData, string $languageId, Context $context): RenderedDocument
    {
        $config = CompleteDocumentConfigurationFactory::createConfiguration($templateData['config']->getVars());

        $enrichedTemplateData = array_merge(
            $templateData,
            [
                'rootDir' => $this->rootDir,
                'context' => $context
            ]
        );

        $editorState = $this->documentEditorHelper->loadEditorState($config->id, $context, $languageId);
        if ($editorState) {
            $config->setFilenamePrefix($this->getFileName($templateData['localeCode'], $context));
            $config->setFilenameSuffix('.' . FileTypes::PDF);
            $renderedDocument = new RenderedDocument(
                $this->documentEditorHelper->renderHtml($editorState, $enrichedTemplateData),
                '',
                $config->buildName(),
                FileTypes::PDF,
                $config->jsonSerialize(),
            );
            $dompdfExtensions = [];
            $dompdfExtensions['dompdfOptions'] = $this->documentEditorHelper->getDomPdfOptions($editorState) ?? [];
            $renderedDocument->setExtensions(array_merge(
                $renderedDocument->getExtensions(),
                $dompdfExtensions
            ));
            $renderedDocument->setContent($this->pdfRenderer->render($renderedDocument));
            return $renderedDocument;
        } else {
            return $this->decoratedService->generate(
                $enrichedTemplateData,
                $languageId,
                $context
            );
        }
    }

    public function getFileName(string $localeCode, Context $context): string
    {
        return $this->decoratedService->getFileName($localeCode, $context);
    }

}
