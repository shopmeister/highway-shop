<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Twig;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Framework\Context;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\BeforeRenderTemplateDataEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DocumentTemplateRendererDecorator extends DocumentTemplateRenderer
{

    /**
     * @param DocumentEditorHelperInterface $documentEditorHelper
     * @param OrderDocumentResolverInterface $orderDocumentResolver
     * @param DocumentTemplateRenderer $decoratedRenderer
     */
    public function __construct(
        private DocumentEditorHelperInterface  $documentEditorHelper,
        private OrderDocumentResolverInterface $orderDocumentResolver,
        private DocumentTemplateRenderer       $decoratedRenderer,
        private ?EventDispatcherInterface      $dispatcher = null
    )
    {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(
        string   $view,
        array    $parameters = [],
        ?Context $context = null,
        ?string  $salesChannelId = null,
        ?string  $languageId = null,
        ?string  $locale = null
    ): string
    {

        // Get $documentBaseConfigId
        if (gettype($parameters["config"]) == "object" && isset($parameters["config"]->id)) {
            $documentBaseConfigId = $parameters["config"]->id;
        } elseif (isset($parameters["config"]["id"])) {
            $documentBaseConfigId = $parameters["config"]["id"];
        } else {
            $documentBaseConfigId = null;
        }

        // Fetch editorState if $documentBaseConfigId is available and not we are not in generating a preview
        $isPreview = !preg_match("/@.*\.twig/", $view);
        if (!$isPreview && $documentBaseConfigId) {
            $editorState = $this->documentEditorHelper->loadEditorState($documentBaseConfigId, $context, $languageId);
        } else {
            $editorState = null;
        }

        if ($editorState || $isPreview) {

            if ($isPreview) {
                $editorState = new EditorStateEntity();
                $translated = $editorState->getTranslated();
                $translated['twigTemplate'] = $view;
                $editorState->setTranslated($translated);
            }

            $templateData = $parameters;
            if (isset($templateData["order"]) && method_exists($templateData["order"], 'getOrderNumber')) {

                // Extract the documentType
                /** @var DocumentEntity $document */
                $document = $templateData['config'] && is_array($templateData['config']) ? $templateData['config']['document'] ?? null : null;
                $documentType = $document ? $document->getDocumentType()->getTechnicalName() ?? null : null;
                if (!$documentType && $documentBaseConfigId) { // in case the document type is still null and we have a base document config id, we can load it from the database
                    $documentType = $this->documentEditorHelper->getDocumentTypeByDocumentBaseConfigId($documentBaseConfigId);
                }

                // Make sure the newly fetched order will be used
                $unifiedTemplateData = $this->orderDocumentResolver->getTemplateData($context, $parameters["order"]->getOrderNumber(), $documentType);
                $templateData['order'] = $unifiedTemplateData['order'];

                $templateData = array_merge(
                    $unifiedTemplateData,
                    $templateData,
                );
            }
            if (isset($templateData['document']) && is_a($templateData['document'], "Shopware\Core\Checkout\Document\DocumentEntity") && isset($templateData['config'])) {
                $config = is_array($templateData['config']) ? $templateData['config'] : $templateData['config']->getVars();
                $templateData['document']->setConfig($config);
            }

            if ($this->dispatcher) {
                $event = new BeforeRenderTemplateDataEvent($templateData, $context);
                $this->dispatcher->dispatch($event);
                $templateData = $event->getTemplateData();
            }

            $html = $this->documentEditorHelper->renderHtml($editorState, $templateData);

            return $html;
        } else {
            return $this->decoratedRenderer->render(
                $view,
                $parameters,
                $context,
                $salesChannelId,
                $languageId,
                $locale
            );
        }
    }
}
