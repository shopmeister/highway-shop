<?php declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Controller;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentPreviewServiceInterface;
use Shopware\Core\Checkout\Document\Controller\DocumentController;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Checkout\Document\Service\DocumentGenerator;
use Shopware\Core\Checkout\Document\Service\DocumentMerger;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_string;
use const JSON_THROW_ON_ERROR;

/**
 * @RouteScope(scopes={"api"})
 */
class DocumentControllerDecorator extends DocumentController
{

    private DocumentGenerator $documentGenerator;
    private DocumentEditorHelperInterface $documentEditorHelper;
    private DocumentPreviewServiceInterface $documentPreviewService;

    public function __construct(
        DocumentGenerator               $documentGenerator,
        DocumentMerger                  $documentMerger,
        DocumentEditorHelperInterface   $documentEditorHelper,
        DocumentPreviewServiceInterface $documentPreviewService
    )
    {
        $this->documentGenerator = $documentGenerator;
        $this->documentEditorHelper = $documentEditorHelper;
        $this->documentPreviewService = $documentPreviewService;
        parent::__construct($documentGenerator, $documentMerger);
    }

    #[Route(path: '/api/_action/document/{documentId}/{deepLinkCode}', name: 'api.action.download.document', defaults: ['_acl' => ['document:read']], methods: ['GET'])]
    public function downloadDocument(Request $request, string $documentId, string $deepLinkCode, Context $context): Response
    {
        return parent::downloadDocument($request, $documentId, $deepLinkCode, $context);
    }

    #[Route(path: '/api/_action/order/{orderId}/{deepLinkCode}/document/{documentTypeName}/preview', name: 'api.action.document.preview', defaults: ['_acl' => ['document:read']], methods: ['GET'])]
    public function previewDocument(Request $request, string $orderId, string $deepLinkCode, string $documentTypeName, Context $context): Response
    {
        $config = $request->query->get('config');
        $config = is_string($config) ? json_decode($config, true, 512, JSON_THROW_ON_ERROR) : [];

        $fileType = $request->query->getAlnum('fileType', FileTypes::PDF);
        $download = $request->query->getBoolean('download');
        $referencedDocumentId = $request->query->getAlnum('referencedDocumentId');

        $operation = new DocumentGenerateOperation($orderId, $fileType, $config, $referencedDocumentId, false, true);

        // Add dompdfOptions to the generatedDocument
        $documentTypeId = $this->documentEditorHelper->getDocumentTypeByName($documentTypeName);
        $documentConfiguration = $this->documentEditorHelper->getConfiguration(
            $context,
            $documentTypeId,
            $orderId,
            $config
        );
        $editorState = $this->documentEditorHelper->loadEditorState($documentConfiguration->jsonSerialize()['id'], $context);
        if ($editorState) {
            $generatedDocument = $this->documentPreviewService->getRenderedDocument($editorState, $documentTypeName, $operation, $context);
        } else {
            $generatedDocument = $this->documentGenerator->preview($documentTypeName, $operation, $deepLinkCode, $context);
        }

        return $this->createResponse(
            $generatedDocument->getName(),
            $generatedDocument->getContent(),
            $download,
            $generatedDocument->getContentType()
        );
    }

    #[Route(path: '/api/_action/order/document/download', name: 'api.action.download.documents', defaults: ['_acl' => ['document:read']], methods: ['POST'])]
    public function downloadDocuments(Request $request, Context $context): Response
    {
        return parent::downloadDocuments($request, $context);
    }

    private function createResponse(string $filename, string $content, bool $forceDownload, string $contentType): Response
    {
        $response = new Response($content);

        $disposition = HeaderUtils::makeDisposition(
            $forceDownload ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
            $filename,
            // only printable ascii
            preg_replace('/[\x00-\x1F\x7F-\xFF]/', '_', $filename) ?? ''
        );

        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

}
