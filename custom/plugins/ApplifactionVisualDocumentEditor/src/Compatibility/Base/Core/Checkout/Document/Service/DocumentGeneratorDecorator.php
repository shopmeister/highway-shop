<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\Renderer\DocumentRendererRegistry;
use Shopware\Core\Checkout\Document\Service\DocumentFileRendererRegistry;
use Shopware\Core\Checkout\Document\Service\DocumentGenerator;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\DocumentGenerationResult;
use Shopware\Core\Checkout\Document\DocumentIdStruct;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Checkout\Document\Service\PdfRenderer;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\HttpFoundation\Request;
use DOMDocument;

/**
 * Decorates the core DocumentGenerator to adjust HTML documents
 */
class DocumentGeneratorDecorator extends DocumentGenerator
{
    public function __construct(
        private readonly DocumentGenerator $decorated,
        DocumentRendererRegistry           $rendererRegistry,
        ?DocumentFileRendererRegistry      $fileRendererRegistry,
        MediaService                       $mediaService,
        private readonly EntityRepository  $documentRepository,
        Connection                         $connection,
        ?PdfRenderer                       $pdfRenderer = null
    )
    {
        if ($fileRendererRegistry !== null) {
            parent::__construct(
                $rendererRegistry,
                $fileRendererRegistry,
                $mediaService,
                $documentRepository,
                $connection
            );
        } else {
            parent::__construct(
                $rendererRegistry,
                $pdfRenderer,
                $mediaService,
                $documentRepository,
                $connection
            );
        }
    }

    public function readDocument(
        string  $documentId,
        Context $context,
        string  $deepLinkCode = '',
        /* , string $fileType = PdfRenderer::FILE_EXTENSION */
    ): ?RenderedDocument
    {
        $fileType = \func_get_args()[3] ?? null;
        if ($fileType === null) {
            $fileType = $this->getDocumentMediaFileType($documentId, $context) ?? PdfRenderer::FILE_EXTENSION;
        }

        $document = $this->decorated->readDocument($documentId, $context, $deepLinkCode, $fileType);

        if ($document instanceof RenderedDocument && str_contains(strtolower($document->getContentType()), 'html')) {
            $doc = new DOMDocument();
            $doc->loadHTML($document->getContent());

            $html = $doc->getElementsByTagName('html')->item(0);
            if (!$html) {
                $html = $doc->appendChild($doc->createElement('html'));
            }
            $head = $doc->getElementsByTagName('head')->item(0);
            if (!$head) {
                $head = $doc->createElement('head');
                $html->insertBefore($head, $html->firstChild);
            }
            $style = $doc->createElement('style',
                ".mj-body { 
                max-width: 1200px;
                margin: auto;
            }
            .mj-body * {
                line-height: 150% !important;
            }
            .mj-body .is-page-footer {
                position: initial !important;
                margin: 2em 0 !important;
            }
            ");
            $head->appendChild($style);

            $meta = $doc->createElement('meta');
            $meta->setAttribute('http-equiv', 'Content-Security-Policy');
            $meta->setAttribute('content', "script-src 'none'; base-uri 'self';");
            $head->appendChild($meta);

            $document->setContent($doc->saveHTML());
        }

        return $document;
    }

    public function preview(string $documentType, DocumentGenerateOperation $operation, string $deepLinkCode, Context $context): RenderedDocument
    {
        return $this->decorated->preview($documentType, $operation, $deepLinkCode, $context);
    }

    /**
     * @param array<string, DocumentGenerateOperation> $operations
     */
    public function generate(string $documentType, array $operations, Context $context): DocumentGenerationResult
    {
        return $this->decorated->generate($documentType, $operations, $context);
    }

    public function upload(string $documentId, Context $context, Request $uploadedFileRequest): DocumentIdStruct
    {
        return $this->decorated->upload($documentId, $context, $uploadedFileRequest);
    }

    /**
     * @param string $documentId
     * @param Context $context
     * @return string|null
     */
    private function getDocumentMediaFileType(string $documentId, Context $context): ?string
    {
        $criteria = new Criteria([$documentId]);
        $criteria->addAssociations([
            'documentMediaFile',
        ]);
        $document = $this->documentRepository->search($criteria, $context)->get($documentId);
        if ($document instanceof DocumentEntity && $document->getDocumentMediaFile() instanceof MediaEntity) {
            return $document->getDocumentMediaFile()->getFileExtension();
        } else {
            return null;
        }
    }
}
