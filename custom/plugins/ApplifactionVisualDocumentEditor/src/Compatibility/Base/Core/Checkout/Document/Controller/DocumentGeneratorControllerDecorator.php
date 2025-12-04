<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Controller;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\DocumentGenerator as ServiceDocumentGenerator;
use Shopware\Core\Checkout\Document\DocumentException;
use Shopware\Core\Checkout\Document\DocumentGeneratorController;
use Shopware\Core\Checkout\Document\Service\DocumentGenerator;
use Shopware\Core\Checkout\Document\Service\PdfRenderer;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\Constraint\Uuid;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

#[Route(defaults: ['_routeScope' => ['api']])]
class DocumentGeneratorControllerDecorator extends DocumentGeneratorController
{

    public function __construct(
        private readonly DocumentGenerator        $documentGenerator,
        private readonly DecoderInterface         $serializer,
        private readonly DataValidator            $dataValidator,
        private readonly ServiceDocumentGenerator $serviceDocumentGenerator
    )
    {
        parent::__construct($documentGenerator, $serializer, $dataValidator);
    }

    #[Route(path: '/api/_action/order/document/{documentTypeName}/create', name: 'api.action.document.bulk.create', methods: ['POST'], defaults: ['_acl' => ['document:create']])]
    public function createDocuments(Request $request, string $documentTypeName, Context $context): JsonResponse
    {
        $documents = $this->serializer->decode($request->getContent(), 'json');

        if (empty($documents) || !\is_array($documents)) {
            throw DocumentException::invalidRequestParameter('Request parameters must be an array of documents object');
        }

        $operations = [];

        $definition = new DataValidationDefinition();
        $definition->addList(
            'documents',
            (new DataValidationDefinition())
                ->add('orderId', new NotBlank())
                ->add('fileType', new Choice([PdfRenderer::FILE_EXTENSION]))
                ->add('config', new Type('array'))
                ->add('static', new Type('bool'))
                ->add('referencedDocumentId', new Uuid())
        );

        $this->dataValidator->validate($documents, $definition);

        foreach ($documents as $operation) {
            $operations[(string)$operation['orderId']] = new DocumentGenerateOperation(
                $operation['orderId'],
                $operation['fileType'] ?? PdfRenderer::FILE_EXTENSION,
                $operation['config'] ?? [],
                $operation['referencedDocumentId'] ?? null,
                $operation['static'] ?? false
            );
        }

        if (in_array($documentTypeName, ['zugferd_invoice'])) {
            return new JsonResponse($this->documentGenerator->generate($documentTypeName, $operations, $context));
        } else {
            return new JsonResponse($this->serviceDocumentGenerator->generate($documentTypeName, $operations, $context));
        }
    }

    #[Route(path: '/api/_action/document/{documentId}/upload', name: 'api.action.document.upload', methods: ['POST'], defaults: ['_acl' => ['document:update']])]
    public function uploadToDocument(Request $request, string $documentId, Context $context): JsonResponse
    {
        return parent::uploadToDocument($request, $documentId, $context);
    }

}
