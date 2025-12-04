<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\Api;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentPreviewServiceInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use RuntimeException;
use Shopware\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeEntity;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Framework\Adapter\Twig\Exception\StringTemplateRenderingException;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\Framework\Plugin\PluginService;
use Shopware\Core\Framework\Plugin\Exception\PluginNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['administration']])]
class DocumentEditorActionController extends AbstractController
{

    const PREVIEW = "preview";

    public function __construct(
        protected PreviewDataResolverInterface    $previewDataResolver,
        protected OrderDocumentResolverInterface  $orderDocumentResolver,
        protected DocumentPreviewServiceInterface $documentService,
        protected DocumentEditorHelperInterface   $documentEditorHelper,
        protected StringTemplateRenderer          $templateRenderer,
        protected PluginService                   $pluginService
    )
    {
    }

    /**
     * Renders an PDF document for preview in the editor
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     * @throws StringTemplateRenderingException
     */
    #[Route(path: '/api/_action/dde/generate-preview-pdf', name: 'api.action.dde.preview.pdf', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function previewPdf(RequestDataBag $post, Context $context): JsonResponse
    {
        $technicalName = $post->get('documentType', self::PREVIEW);
        $config = $post->get('config', '')->all();
        $contentHtml = $post->get('contentHtml');
        $orderNumber = $post->get('orderNumber', null);
        $dompdfOptions = $post->get('dompdfOptions', [])->all() ?? [];

        if ($localeCode = $post->get('localeCode')) {
            // If a localeCode is given we change the languageIdChain of the context which will cause to load data from the database in the correct translation
            $context->assign([
                'languageIdChain' => array_values(array_filter(array_unique(array_merge([$this->documentEditorHelper->getLanguageIdByLocaleCode($localeCode)], $context->getLanguageIdChain())))),
                'isPreview' => true // this flag will prevent the order from overwriting the language again
            ]);
        }

        $documentType = new DocumentTypeEntity();
        $documentType->setTechnicalName($technicalName);
        $document = new DocumentEntity();
        $document->setConfig(array_merge($config, [
            'twigTemplate' => $contentHtml,
            'dompdfOptions' => $dompdfOptions,
            'filenamePrefix' => 'ApplifactionVisualDocumentEditor_DocumentPreview_',
            'filenameSuffix' => Uuid::randomHex(),
            'orderNumber' => $orderNumber
        ]));
        $document->setStatic(false);
        $document->setFileType(FileTypes::PDF);
        $document->setDocumentType($documentType);
        $document->setOrderVersionId(self::PREVIEW);
        $document->setOrderId(self::PREVIEW);
        $pdfString = $this->documentService->getPdfDocument($document, $context);
        return new JsonResponse(['base64PdfFile' => base64_encode($pdfString)]);
    }

    /**
     * Renders an HTML document for preview in the editor
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     * @throws StringTemplateRenderingException
     */
    #[Route(path: '/api/_action/dde/generate-preview-html', name: 'api.action.dde.preview.html', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function previewHtml(RequestDataBag $post, Context $context): JsonResponse
    {
        $technicalName = $post->get('documentType', self::PREVIEW);
        $config = $post->get('config', '')->all();
        $contentHtml = $post->get('contentHtml');
        $orderNumber = $post->get('orderNumber', null);

        if ($localeCode = $post->get('localeCode')) {
            // If a localeCode is given we change the languageIdChain of the context which will cause to load data from the database in the correct translation
            $context->assign([
                'languageIdChain' => array_values(array_filter(array_unique(array_merge([$this->documentEditorHelper->getLanguageIdByLocaleCode($localeCode)], $context->getLanguageIdChain())))),
                'isPreview' => true // this flag will prevent the order from overwriting the language again
            ]);
        }

        $documentType = new DocumentTypeEntity();
        $documentType->setTechnicalName($technicalName);
        $document = new DocumentEntity();
        $document->setConfig(array_merge($config, [
            'twigTemplate' => $contentHtml,
            'filenamePrefix' => 'ApplifactionVisualDocumentEditor_DocumentPreview_',
            'filenameSuffix' => Uuid::randomHex(),
            'orderNumber' => $orderNumber
        ]));
        $document->setStatic(false);
        $document->setFileType(FileTypes::PDF);
        $document->setDocumentType($documentType);
        $document->setOrderVersionId(self::PREVIEW);
        $document->setOrderId(self::PREVIEW);
        return new JsonResponse(['html' => $this->documentService->getHtmlDocument($document, $context)]);
    }

    /**
     * Saves all translated twig templates for an editor state
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     * @throws StringTemplateRenderingException
     */
    #[Route(path: '/api/_action/dde/save-translated-twig-templates', name: 'api.action.dde.save_translated_twig_templates', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function saveTranslatedTwigTemplates(RequestDataBag $post, Context $context): JsonResponse
    {
        // Fetch parameters from request
        $documentBaseConfigId = $post->get('documentBaseConfigId');
        $data = $post->get('data')->all();
        $translatedTwigTemplates = $post->get('translatedTwigTemplates')->all();

        // Load base editor state
        $editorState = $this->documentEditorHelper->loadEditorState($documentBaseConfigId, $context);

        if ($editorState) {
            // Save different translations
            foreach ($translatedTwigTemplates as $languageId => $twigTemplate) {
                $this->documentEditorHelper->upsertEditorStates([[
                    'id' => $editorState->getId(),
                    'documentBaseConfigId' => $editorState->getDocumentBaseConfigId(),
                    'isEditorEnabled' => $editorState->isEditorEnabled(),
                    'data' => $data,
                    'twigTemplate' => $twigTemplate
                ]], $languageId, $context);
            }
        } else {
            throw new RuntimeException("Editor State could not be loaded");
        }

        return new JsonResponse();
    }

    /**
     * Returns MD5 hashes for all translated twig templates of a document
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     * @throws StringTemplateRenderingException
     */
    #[Route(path: '/api/_action/dde/translated-twig-template-hashes', name: 'api.action.dde.translated-twig-template-hashes', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function getTranslatedTwigTemplateHashes(RequestDataBag $post, Context $context): JsonResponse
    {
        // Fetch parameters from request
        $documentBaseConfigId = $post->get('documentBaseConfigId');
        return new JsonResponse($this->documentEditorHelper->getTranslatedTwigTemplateHashes($documentBaseConfigId, $context));
    }

    /**
     * Retrieves additional type information for available template data
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     */
    #[Route(path: '/api/_action/dde/types', name: 'api.action.dde.types', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function types(RequestDataBag $post, Context $context): JsonResponse
    {
        $documentType = $post->get('documentType');
        $typeInfo = $this->previewDataResolver->getAdditionalTypeInformation($documentType, $context);
        return new JsonResponse($typeInfo);
    }

    /**
     * Retrieves information about the avaialble entities, which can be used in variables for a specified document type
     *
     * @param RequestDataBag $post
     * @return JsonResponse
     */
    #[Route(path: '/api/_action/dde/available-entities', name: 'api.action.dde.available_entities', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function availableEntities(RequestDataBag $post): JsonResponse
    {
        $availableEntities = $this->orderDocumentResolver->getAvailableEntities();
        return new JsonResponse($availableEntities);
    }

    /**
     * Retrieves template data for a specific document type
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     */
    #[Route(path: '/api/_action/dde/template-data', name: 'api.action.dde.template_data', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function templateData(RequestDataBag $post, Context $context): JsonResponse
    {
        $documentType = $post->get('documentType');
        $templateData = $this->orderDocumentResolver->getTemplateData($context, null, $documentType);
        return new JsonResponse($templateData);
    }

    #[Route(path: '/api/_action/dde/plugin-version', name: 'api.action.dde.plugin-version', defaults: ['_routeScope' => ['administration']], methods: ['GET'])]
    public function getVersion(Context $context): JsonResponse
    {
        try {
            $plugin = $this->pluginService->getPluginByName('ApplifactionVisualDocumentEditor', $context);
            $version = $plugin->getVersion() ?? '';
        } catch (PluginNotFoundException $e) {
            $version = '';
        }

        return new JsonResponse(['version' => $version]);
    }

    /**
     * Renders a preview of a custom preset for use in the editor
     *
     * @param RequestDataBag $post
     * @param Context $context
     * @return JsonResponse
     * @throws StringTemplateRenderingException
     */
    #[Route(path: '/api/_action/dde/custom-preset/preview', name: 'api.action.dde.custom-preset.preview', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function preview(RequestDataBag $post, Context $context): JsonResponse
    {
        $documentType = $post->get('documentType');
        $salesChannelId = $post->get('salesChannelId');
        $previewData = $this->previewDataResolver->resolveType($context, $documentType, $salesChannelId);;
        $html = $this->templateRenderer->render($post->get('contentHtml', ''), $previewData, $context);
        return new JsonResponse([
            'html' => $html,
            'templateData' => $previewData,
        ]);
    }

}
