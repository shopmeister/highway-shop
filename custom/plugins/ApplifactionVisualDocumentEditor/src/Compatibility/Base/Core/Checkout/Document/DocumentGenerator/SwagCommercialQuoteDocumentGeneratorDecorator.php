<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentGenerator;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\PdfRenderer as DocumentEditorPdfRenderer;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\AbstractQuoteDocumentRenderer;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\QuoteDocumentGenerateOperation;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\QuoteDocumentGenerator;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\Service\HtmlQuoteRenderer;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\Service\QuoteDocumentFileRendererRegistry;
use Shopware\Commercial\B2B\QuoteManagement\Entity\Quote\QuoteEntity;
use Shopware\Commercial\B2B\QuoteManagement\Exception\QuoteDocumentException;
use Shopware\Core\Checkout\Document\DocumentIdStruct;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\Service\DocumentFileRendererRegistry;
use Shopware\Core\Checkout\Document\Service\PdfRenderer;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SwagCommercialQuoteDocumentGeneratorDecorator extends QuoteDocumentGenerator
{

    public function __construct(
        private readonly QuoteDocumentGenerator             $decoratedDocumentGenerator,
        private readonly AbstractQuoteDocumentRenderer      $quoteDocumentRenderer,
        private readonly MediaService                       $mediaService,
        private readonly EntityRepository                   $quoteDocumentRepository,
        private readonly Connection                         $connection,
        private readonly DocumentEditorHelperInterface      $documentEditorHelper,
        private readonly DocumentEditorPdfRenderer          $documentEditorPdfRenderer,
        private readonly ?PdfRenderer                       $pdfRenderer,
        private readonly ?DocumentFileRendererRegistry      $documentFileRendererRegistry = null,
        private readonly ?QuoteDocumentFileRendererRegistry $quoteFileRendererRegistry = null,
        private readonly ?EventDispatcherInterface          $dispatcher = null,
        private readonly ?EntityRepository                  $quoteRepository = null
    )
    {
        // After SwagCommercial Version 6.10.4, it uses its own QuoteDocumentFileRendererRegistry class
        if (!!$quoteFileRendererRegistry) {
            parent::__construct($quoteDocumentRenderer, $quoteFileRendererRegistry, $mediaService, $quoteDocumentRepository, $connection);
        } elseif (!!$documentFileRendererRegistry) {
            parent::__construct($quoteDocumentRenderer, $documentFileRendererRegistry, $mediaService, $quoteDocumentRepository, $connection);
        } elseif (!!$pdfRenderer) {
            parent::__construct($quoteDocumentRenderer, $pdfRenderer, $mediaService, $quoteDocumentRepository, $connection);
        } else {
            throw new \Exception('Decoration of the QuoteDocumentGenerator of the SwagCommercial App failed. Please contact Applifaction (info@applifaction.com) and ask for a compatibility fix.');
        }
    }

    public function generate(QuoteDocumentGenerateOperation $operation, Context $context, string $documentType, bool $isNewDocument = true): DocumentIdStruct
    {

        $documentTypeId = $this->getDocumentTypeByName($documentType);
        if ($documentTypeId === null) {
            throw QuoteDocumentException::invalidDocumentRenderer($documentType);
        }

        $document = $this->quoteDocumentRenderer->render($operation, $context);

        /** @var ?EditorStateEntity $editorState */
        $editorState = null;
        $config = $document->getConfig();
        if (isset($config['id'])) {

            $parameters = $document->getParameters();
            if (isset($parameters['quote']) && $parameters['quote'] instanceof QuoteEntity) {
                $quote = $parameters['quote'];
                $languageId = $quote->getCustomer()?->getLanguageId();
                if ($languageId !== null) {
                    $context = $this->assignLanguageToContext($context, $languageId);
                }
                $translatedQuote = $this->fetchQuote($quote->getId(), $context);
                if ($translatedQuote instanceof QuoteEntity) {
                    $parameters['quote'] = $translatedQuote;
                    $document->setParameters($parameters);
                }
            }

            $editorState = $this->documentEditorHelper->loadEditorState($config['id'], $context, $context->getLanguageId());
            if ($editorState) {
                $dompdfExtensions = [];
                $dompdfExtensions['dompdfOptions'] = $this->documentEditorHelper->getDomPdfOptions($editorState) ?? [];
                $document->setExtensions(array_merge($document->getExtensions(), $dompdfExtensions));
            }
        }

        // If the document editor is deactivated for the quote, use the decorated document generator instead
        if (!$editorState) return $this->decoratedDocumentGenerator->generate($operation, $context, $documentType, $isNewDocument);

        $this->checkDocumentNumberAlreadyExits($documentType, $document->getNumber(), $operation->getDocumentId());

        $deepLinkCode = Random::getAlphanumericString(32);
        $id = $operation->getDocumentId() ?? Uuid::randomHex();

        $mediaId = $this->resolveMediaId($operation, $context, $document, $editorState);
        $mediaIdForHtmlA11y = $this->resolveMediaIdForA11y($operation, $context, $document, $editorState);

        $record = [
            'id' => $id,
            'documentNumber' => $document->getNumber(),
            'documentTypeId' => $documentTypeId,
            'fileType' => $operation->getFileType(),
            'quoteId' => $operation->getQuoteId(),
            'quoteVersionId' => $operation->getQuoteVersionId(),
            'static' => $operation->isStatic(),
            'active' => $operation->isActive(),
            'documentMediaFileId' => $mediaId,
            'config' => $document->getConfig(),
            'deepLinkCode' => $deepLinkCode
        ];

        if ($mediaIdForHtmlA11y !== null) {
            // mediaIdForHtmlA11y is null in older versions of SwagCommercial
            $record['documentA11yMediaFileId'] = $mediaIdForHtmlA11y;
        }

        if ($isNewDocument) {
            $this->unActivePreviousDocuments($operation, $context);
        }

        $this->quoteDocumentRepository->upsert([$record], $context);

        return new DocumentIdStruct($id, $deepLinkCode, $mediaId, $mediaIdForHtmlA11y);
    }

    private function fetchQuote(string $quoteId, Context $context)
    {
        if ($this->quoteRepository === null) return null;

        $criteria = new Criteria([$quoteId]);
        $criteria->addAssociations([
            'lineItems',
            'lineItems.product',
            'lineItems.product.unit',
            'lineItems.product.visibilities',
            'lineItems.product.cover',
            'lineItems.product.deliveryTime',
            'language.locale',
            'currency',
            'deliveries.shippingMethod',
            'transactions.paymentMethod',
            'customer.salutation',
            'customer.addresses.country',
            'customer.defaultShippingAddress',
            'customer.defaultBillingAddress.country',
            'customer.defaultBillingAddress.currency',
            'customer.defaultBillingAddress.salutation',
            'customer.defaultBillingAddress.countryState',
            'customer.activeBillingAddress.country',
            'customer.activeBillingAddress.currency',
            'customer.activeBillingAddress.salutation',
            'customer.activeBillingAddress.countryState',
            'customer.activeShippingAddress.country',
            'customer.activeShippingAddress.currency',
            'customer.activeShippingAddress.salutation',
            'customer.activeShippingAddress.countryState',
        ]);
        return $this->quoteRepository->search($criteria, $context)->get($quoteId);
    }

    public function upload(string $quoteDocumentId, Context $context, Request $uploadedFileRequest): DocumentIdStruct
    {
        return $this->decoratedDocumentGenerator->upload($quoteDocumentId, $context, $uploadedFileRequest);
    }

    private function resolveMediaId(QuoteDocumentGenerateOperation $operation, Context $context, RenderedDocument $document, ?EditorStateEntity $editorState = null): ?string
    {
        if ($operation->isStatic()) {
            return null;
        }

        if ($editorState) {
            if ($this->quoteFileRendererRegistry !== null && class_exists(HtmlQuoteRenderer::class)) {

                // Newer SwagCommercial versions use the quoteFileRendererRegistry, which can provide HTML and PDF documents
                // We have to tell it that we want the HTML document and re-render the document and then convert it into a PDF using our pdfRenderer

                $htmlDocument = clone $document;
                $htmlDocument->setContentType(HtmlQuoteRenderer::FILE_CONTENT_TYPE);
                $htmlDocument->setFileExtension(HtmlQuoteRenderer::FILE_EXTENSION);

                try {
                    $html = $this->quoteFileRendererRegistry->render($htmlDocument);
                } catch (\Throwable) {
                    $html = '';
                }

                $dompdfOptions = $this->documentEditorHelper->getDomPdfOptions($editorState) ?? [];
                $document->setContent($this->documentEditorPdfRenderer->renderPdfByHtml($html, $dompdfOptions));

            } else {

                // Older SwagCommercial versions provide the document HTML only.
                // We just have to convert it into a PDF
                $document->setContent($this->documentEditorPdfRenderer->render($document));

            }
        }

        if ($this->quoteFileRendererRegistry !== null && class_exists(HtmlQuoteRenderer::class)) {
            // This code is for newer versions of SwagCommercial

            if ($document->getContent() === '') {
                return null;
            }

            return $context->scope(Context::SYSTEM_SCOPE, fn(Context $context): string => $this->mediaService->saveFile(
                $document->getContent(),
                $document->getFileExtension(),
                $document->getContentType(),
                $document->getName(),
                $context,
                'document'
            ));

        } else {

            if (!!$this->documentFileRendererRegistry) {
                // This code is for older versions of SwagCommercial
                return $context->scope(Context::SYSTEM_SCOPE, fn(Context $context): string => $this->mediaService->saveFile(
                    $this->documentFileRendererRegistry->render($document),
                    $document->getFileExtension(),
                    $document->getFileExtension(),
                    $document->getName(),
                    $context,
                    'document'
                ));
            } else {
                // Older shopware versions don't have the documentFileRenderRegistry. But luckily the document content is already rendered at this point
                return $context->scope(Context::SYSTEM_SCOPE, fn(Context $context): string => $this->mediaService->saveFile(
                    $document->getContent(),
                    $document->getFileExtension(),
                    $document->getFileExtension(),
                    $document->getName(),
                    $context,
                    'document'
                ));
            }

        }

    }

    private function resolveMediaIdForA11y(QuoteDocumentGenerateOperation $operation, Context $context, RenderedDocument $document, ?EditorStateEntity $editorState = null): ?string
    {
        if ($this->quoteFileRendererRegistry === null || !class_exists(HtmlQuoteRenderer::class)) {
            // Older versions of SwagCommercial don't support A11y
            return null;
        }

        $document = clone $document;
        $document->setContentType(HtmlQuoteRenderer::FILE_CONTENT_TYPE);
        $document->setFileExtension(HtmlQuoteRenderer::FILE_EXTENSION);

        try {
            $content = $this->quoteFileRendererRegistry->render($document);
        } catch (\Throwable) {
            return null;
        }

        $document->setContent($content);

        return $this->resolveMediaId($operation, $context, $document, $editorState);
    }

    private function getDocumentTypeByName(string $documentType): ?string
    {
        /** @var string|null $id */
        $id = $this->connection->fetchOne(
            'SELECT LOWER(HEX(id)) as id FROM document_type WHERE technical_name = :technicalName',
            ['technicalName' => $documentType]
        );

        return $id ?: null;
    }

    private function checkDocumentNumberAlreadyExits(
        string  $documentTypeName,
        string  $documentNumber,
        ?string $quoteDocumentId = null
    ): void
    {
        $sql = '
            SELECT COUNT(id)
            FROM `quote_document`
            WHERE
                document_type_id IN (
                    SELECT id
                    FROM document_type
                    WHERE technical_name = :documentTypeName
                )
                AND document_number = :documentNumber
                AND id ' . ($quoteDocumentId !== null ? '!= :quoteDocumentId' : 'IS NOT NULL') . '
            LIMIT 1
        ';

        $params = [
            'documentTypeName' => $documentTypeName,
            'documentNumber' => $documentNumber,
        ];

        if ($quoteDocumentId !== null) {
            $params['quoteDocumentId'] = Uuid::fromHexToBytes($quoteDocumentId);
        }

        $statement = $this->connection->executeQuery($sql, $params);

        $result = (bool)$statement->fetchOne();

        if ($result) {
            throw QuoteDocumentException::documentNumberAlreadyExists($documentNumber);
        }
    }

    private function unActivePreviousDocuments(QuoteDocumentGenerateOperation $operation, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('quoteId', $operation->getQuoteId()));
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('documentTypeId', $this->getDocumentTypeByName('quotes')));

        /** @var array<string> $documents */
        $documents = $this->quoteDocumentRepository->searchIds($criteria, $context)->getIds();

        if (empty($documents)) {
            return;
        }

        $this->connection->executeStatement(
            'UPDATE quote_document SET active = 0 WHERE id IN (:ids)',
            [
                'ids' => Uuid::fromHexToBytesList($documents),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );
    }

    private function assignLanguageToContext(Context $context, string $languageId): Context
    {
        $languageChain = array_values(array_filter(array_unique([
            $languageId,
            ...$context->getLanguageIdChain(),
        ])));

        return $context->assign([
            'languageIdChain' => $languageChain,
        ]);
    }

}
