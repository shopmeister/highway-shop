<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperBase;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\TwigRenderer;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Shopware\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigEntity;
use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Doctrine\DBAL\Connection;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Content\EditorState\EditorStateEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\Language\LanguageEntity;

class DocumentEditorHelper extends DocumentEditorHelperBase
{

    protected EntityRepository $editorStateRepository;

    protected EntityRepository $languageRepository;

    protected EntityRepository $documentConfigRepository;

    protected TwigRenderer $twigRenderer;

    protected Connection $connection;

    protected OrderDocumentResolverInterface $orderDocumentResolver;

    public function __construct(
        EntityRepository                   $editorStateRepository,
        EntityRepository                   $languageRepository,
        EntityRepository                   $documentConfigRepository,
        Connection                         $connection,
        TwigRenderer                       $twigRenderer,
        OrderDocumentResolverInterface     $orderDocumentResolver
    )
    {
        $this->editorStateRepository = $editorStateRepository;
        $this->languageRepository = $languageRepository;
        $this->documentConfigRepository = $documentConfigRepository;
        $this->connection = $connection;
        $this->twigRenderer = $twigRenderer;

        parent::__construct($connection, $twigRenderer, $orderDocumentResolver);
    }

    /**
     * @return EditorStateEntity
     */
    public function loadEditorState($documentBaseConfigId, Context $context, $languageId = null): ?EditorStateEntity
    {
        if ($languageId) {
            $languageContext = new Context(
                $context->getSource(),
                $context->getRuleIds(),
                $context->getCurrencyId(),
                [$languageId],
                $context->getVersionId(),
                $context->getCurrencyFactor(),
                $context->considerInheritance(),
                $context->getTaxState(),
                $context->getRounding()
            );
        } else {
            $languageContext = $context;
        }
        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('documentBaseConfigId', $documentBaseConfigId))
            ->addFilter(new EqualsFilter('isEditorEnabled', true))
            ->setLimit(1);
        $searchResult = $this->editorStateRepository->search($criteria, $languageContext);
        if ($searchResult->count() > 0) {
            return $searchResult->first();
        }
        return null;
    }

    public function upsertEditorStates(array $editorStateData, $languageId, Context $context)
    {
        $languageContext = new Context(
            $context->getSource(),
            $context->getRuleIds(),
            $context->getCurrencyId(),
            [$languageId],
            $context->getVersionId(),
            $context->getCurrencyFactor(),
            $context->considerInheritance(),
            $context->getTaxState(),
            $context->getRounding()
        );
        $this->editorStateRepository->upsert($editorStateData, $languageContext);
    }

    public function getTranslatedTwigTemplateHashes($documentBaseConfigId, Context $context)
    {
        $hashes = [];
        $languages = $this->languageRepository->search(new Criteria(), $context);
        /** @var LanguageEntity $language */
        foreach ($languages as $language) {
            // Load base editor state in specific language
            /** @var EditorStateEntity $editorState */
            $editorState = $this->loadEditorState($documentBaseConfigId, $context, $language->getId());
            if ($editorState) {
                $translated = $editorState->getTranslated();
                if (isset($translated['twigTemplate'])) {
                    $hashes[$language->getId()] = md5($translated['twigTemplate']);
                }
            }
        }
        return $hashes;
    }

    /**
     * @param array<string, int|string>|null $specificConfiguration
     */
    public function getConfiguration(Context $context, string $documentTypeId, string $orderId, ?array $specificConfiguration): DocumentConfiguration
    {
        $specificConfiguration = $specificConfiguration ?? [];
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('documentTypeId', $documentTypeId));
        $criteria->addAssociation('logo');
        $criteria->addFilter(new EqualsFilter('global', true));

        /** @var DocumentBaseConfigEntity $globalConfig */
        $globalConfig = $this->documentConfigRepository->search($criteria, $context)->first();

        $order = $this->orderDocumentResolver->getOrderById($orderId, $context);
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('documentTypeId', $documentTypeId));
        $criteria->addAssociation('logo');
        $criteria->addFilter(new EqualsFilter('salesChannels.salesChannelId', $order->getSalesChannelId()));
        $criteria->addFilter(new EqualsFilter('salesChannels.documentTypeId', $documentTypeId));

        $salesChannelConfig = $this->documentConfigRepository->search($criteria, $context)->first();

        return DocumentConfigurationFactory::createConfiguration($specificConfiguration, $globalConfig, $salesChannelConfig);
    }

}
