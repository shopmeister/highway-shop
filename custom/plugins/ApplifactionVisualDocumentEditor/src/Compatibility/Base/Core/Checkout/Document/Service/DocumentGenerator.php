<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Document\Renderer\RendererResult;
use Shopware\Core\Checkout\Document\Service\HtmlRenderer;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Checkout\Document\DocumentGenerationResult;
use Shopware\Core\Checkout\Document\DocumentIdStruct;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Framework\Context;
use Shopware\Core\Checkout\Document\Exception\DocumentNumberAlreadyExistsException;
use Shopware\Core\Checkout\Document\Exception\InvalidDocumentRendererException;
use Shopware\Core\Checkout\Document\Renderer\DocumentRendererConfig;
use Shopware\Core\Checkout\Document\Renderer\DocumentRendererRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DocumentGenerator
{


    /**
     * @var string
     */
    protected $rootDir;

    protected DocumentEditorHelperInterface $documentEditorHelper;

    /**
     * @var MediaService
     */
    protected MediaService $mediaService;

    /**
     * @var PdfRenderer
     */
    protected PdfRenderer $pdfRenderer;

    /**
     * @var EntityRepository
     */
    protected EntityRepository $documentRepository;

    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * @var ?DocumentRendererRegistry
     */
    protected ?DocumentRendererRegistry $rendererRegistry;

    protected OrderDocumentResolverInterface $orderDocumentResolver;
    private ?EventDispatcherInterface $dispatcher;

    /**
     * @internal
     */
    public function __construct(
        string                         $rootDir,
        DocumentEditorHelperInterface  $documentEditorHelper,
        MediaService                   $mediaService,
        PdfRenderer                    $pdfRenderer,
        EntityRepository               $documentRepository,
        ?DocumentRendererRegistry      $rendererRegistry,
        OrderDocumentResolverInterface $orderDocumentResolver,
        Connection                     $connection,
        ?EventDispatcherInterface      $dispatcher = null
    )
    {
        $this->rootDir = $rootDir;
        $this->documentEditorHelper = $documentEditorHelper;
        $this->mediaService = $mediaService;
        $this->pdfRenderer = $pdfRenderer;
        $this->documentRepository = $documentRepository;
        $this->rendererRegistry = $rendererRegistry;
        $this->orderDocumentResolver = $orderDocumentResolver;
        $this->connection = $connection;
        $this->dispatcher = $dispatcher;
    }

    public function generate(string $documentType, array $operations, Context $context): DocumentGenerationResult
    {
        $documentTypeId = $this->documentEditorHelper->getDocumentTypeByName($documentType);
        if ($documentTypeId === null) {
            throw new InvalidDocumentRendererException($documentType);
        }

        $rendered = $this->rendererRegistry->render($documentType, $operations, $context, new DocumentRendererConfig());

        $result = new DocumentGenerationResult();

        foreach ($rendered->getErrors() as $orderId => $error) {
            $result->addError($orderId, $error);
        }
        $records = [];
        $success = $rendered->getSuccess();
        foreach ($operations as $orderId => $operation) {
            try {
                /** @var RenderedDocument $document */
                $document = $success[$orderId] ?? null;
                if ($document === null) {
                    continue;
                }
                $this->checkDocumentNumberAlreadyExits($documentType, $document->getNumber(), $context, $operation->getDocumentId());
                $deepLinkCode = Random::getAlphanumericString(32);
                $id = $operation->getDocumentId() ?? Uuid::randomHex();
                $languageId = $this->documentEditorHelper->getOrderLanguageId($orderId);

                $editorState = $this->documentEditorHelper->loadEditorState($document->getConfig()["id"], $context, $languageId);
                if ($editorState) {
                    $dompdfExtensions = [];
                    $dompdfExtensions['dompdfOptions'] = $this->documentEditorHelper->getDomPdfOptions($editorState) ?? [];
                    $document->setExtensions(array_merge(
                        $document->getExtensions(),
                        $dompdfExtensions
                    ));
                }
                $mediaId = $this->resolveMediaId($operation, $context, $document);
                $mediaIdForHtmlA11y = $this->resolveMediaIdForA11y($operation, $context, $document);
                $records[] = [
                    'id' => $id,
                    'documentTypeId' => $documentTypeId,
                    'fileType' => $operation->getFileType(),
                    'orderId' => $orderId,
                    'orderVersionId' => $operation->getOrderVersionId(),
                    'static' => $operation->isStatic(),
                    'documentMediaFileId' => $mediaId,
                    'documentA11yMediaFileId' => $mediaIdForHtmlA11y,
                    'config' => $document->getConfig(),
                    'deepLinkCode' => $deepLinkCode,
                    'referencedDocumentId' => $operation->getReferencedDocumentId(),
                ];
                $result->addSuccess(new DocumentIdStruct($id, $deepLinkCode, $mediaId, $mediaIdForHtmlA11y));
            } catch (\Throwable $exception) {
                $result->addError($orderId, $exception);
            }
        }
        $this->writeRecords($records, $context);
        return $result;
    }

    /**
     * Generate the PDF with Shopware Core Methods in case the Document Editor is not activated for the Document Type
     */
    private function resolveMediaId(DocumentGenerateOperation $operation, Context $context, RenderedDocument $document, ?string $documentType = null, ?RendererResult $result = null): ?string
    {
        if ($operation->isStatic()) {
            return null;
        }

        try {
            $document->setContent($this->pdfRenderer->render($document));

            if (!Feature::isActive('v6.7.0.0')) {
                if ($documentType && $result) {
                    $this->rendererRegistry->finalize($documentType, $operation, $context, new DocumentRendererConfig(), $result);
                }
            }
        } catch (\Throwable) {
            return null;
        }

        if ($document->getContent() === '') {
            return null;
        }

        return $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($document): string {
            return $this->mediaService->saveFile(
                $document->getContent(),
                $document->getFileExtension(),
                $document->getContentType(),
                $document->getName(),
                $context,
                'document'
            );
        });
    }

    private function resolveMediaIdForA11y(DocumentGenerateOperation $operation, Context $context, RenderedDocument $document): ?string
    {
        if ($operation->isStatic() || !class_exists(HtmlRenderer::class)) {
            return null;
        }

        $content = $document->getHtml() !== '' ? $document->getHtml() : ($document->getContent() !== '' ? $document->getContent() : '');

        if ($content === '') {
            return null;
        }

        return $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($document, $content): string {
            return $this->mediaService->saveFile(
                $content,
                HtmlRenderer::FILE_EXTENSION,
                HtmlRenderer::FILE_CONTENT_TYPE,
                $document->getName(),
                $context,
                'document'
            );
        });
    }

    /**
     * This is a copy of the Shopware Core method because it is private
     *
     * @param mixed[][] $records
     */
    private function writeRecords(array $records, Context $context): void
    {
        if (empty($records)) {
            return;
        }
        $this->documentRepository->upsert($records, $context);
    }

    /**
     * This is a copy of the Shopware Core method because it is private
     */
    private function checkDocumentNumberAlreadyExits(
        string  $documentTypeName,
        string  $documentNumber,
        Context $context,
        ?string $documentId = null
    ): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('documentType.technicalName', $documentTypeName));
        $criteria->addFilter(new EqualsFilter('config.documentNumber', $documentNumber));

        if ($documentId !== null) {
            $criteria->addFilter(new NotFilter(
                NotFilter::CONNECTION_AND,
                [new EqualsFilter('id', $documentId)]
            ));
        }

        $criteria->setLimit(1);

        $result = $this->documentRepository->searchIds($criteria, $context);

        if ($result->getTotal() !== 0) {
            throw new DocumentNumberAlreadyExistsException($documentNumber);
        }
    }
}
