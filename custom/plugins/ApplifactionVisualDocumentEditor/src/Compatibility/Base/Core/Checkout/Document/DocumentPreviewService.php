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
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\PdfRenderer;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\TranslatorService;
use Exception;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\Renderer\DocumentRendererConfig;
use Shopware\Core\Checkout\Document\Renderer\DocumentRendererRegistry;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\AfterRenderHtmlEvent;
use function array_key_exists;

/**
 * Class DocumentPreviewService
 */
class DocumentPreviewService implements DocumentPreviewServiceInterface
{

    private PdfRenderer $pdfRenderer;
    private DocumentEditorHelperInterface $documentEditorHelper;
    private DocumentRendererRegistry $rendererRegistry;
    private TranslatorService $translatorService;
    private OrderDocumentResolverInterface $orderDocumentResolver;
    private ?EventDispatcherInterface $dispatcher;

    public function __construct(
        OrderDocumentResolverInterface $orderDocumentResolver,
        PdfRenderer                    $pdfRenderer,
        DocumentEditorHelperInterface  $documentEditorHelper,
        DocumentRendererRegistry       $rendererRegistry,
        TranslatorService              $translatorService,
        ?EventDispatcherInterface      $dispatcher = null
    )
    {
        $this->orderDocumentResolver = $orderDocumentResolver;
        $this->pdfRenderer = $pdfRenderer;
        $this->documentEditorHelper = $documentEditorHelper;
        $this->rendererRegistry = $rendererRegistry;
        $this->translatorService = $translatorService;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @throws Exception
     */
    public function getRenderedDocument(EditorStateEntity $editorState, string $documentType, DocumentGenerateOperation $operation, Context $context): RenderedDocument
    {
        $config = new DocumentRendererConfig();

        $rendered = $this->rendererRegistry->render($documentType, [$operation->getOrderId() => $operation], $context, $config);

        if (!array_key_exists($operation->getOrderId(), $rendered->getSuccess())) {
            throw new Exception($this->translatorService->trans('dde.exception.document-render-error', $context, [':orderId' => $operation->getOrderId()]));
        }

        $document = $rendered->getSuccess()[$operation->getOrderId()];

        $dompdfExtensions = [];
        $dompdfExtensions['dompdfOptions'] = $this->documentEditorHelper->getDomPdfOptions($editorState) ?? [];
        $document->setExtensions(array_merge(
            $document->getExtensions(),
            $dompdfExtensions
        ));

        $document->setContent($this->pdfRenderer->render($document));

        return $document;
    }

    public function getPdfDocument(DocumentEntity $document, Context $context): string
    {
        $config = CompleteDocumentConfigurationFactory::createConfiguration($document->getConfig());
        return $this->renderPdfDocument($document, $context, $config);
    }

    public function getHtmlDocument(DocumentEntity $document, Context $context): string
    {
        return $this->renderHtmlDocument($document, $context);
    }

    private function renderHtmlDocument(
        DocumentEntity $document,
        Context        $context
    ): string
    {
        $config = $document->getConfig();
        $documentConfig = CompleteDocumentConfigurationFactory::mergeConfiguration(new CompleteDocumentConfiguration(), $config);

        $orderNumber = null;
        if (isset($config['orderNumber']) && $config['orderNumber']) {
            $orderNumber = $config['orderNumber'];
        }

        $templateData = $this->orderDocumentResolver->getTemplateData($context, $orderNumber, $document->getDocumentType()->getTechnicalName());

        // Apply the order documents config first
        foreach ($documentConfig->getVars() as $key => $value) {
            if ($value) {
                $templateData['config'][$key] = $value;
            }
        }

        // Overwrite the order document config with the given base document config for the preview
        foreach ($config as $key => $value) {
            if ($value) {
                $templateData['config'][$key] = $value;
            }
        }

        $documentConfig->assign($templateData['config']);
        $documentConfig->assign($templateData);

        $editorState = new EditorStateEntity();
        $editorState->setTranslated([
            'twigTemplate' => $config['twigTemplate']
        ]);

        $html = $this->documentEditorHelper->renderHtml(
            $editorState,
            array_merge(
                $templateData,
                ['context' => $context]
            )
        );

        return $html;
    }

    private function renderPdfDocument(
        DocumentEntity                $document,
        Context                       $context,
        CompleteDocumentConfiguration $config
    ): string
    {
        $renderedHtml = $this->renderHtmlDocument($document, $context);
        return $this->pdfRenderer->renderPdfByHtml($renderedHtml, $config->getDompdfOptions());
    }

}