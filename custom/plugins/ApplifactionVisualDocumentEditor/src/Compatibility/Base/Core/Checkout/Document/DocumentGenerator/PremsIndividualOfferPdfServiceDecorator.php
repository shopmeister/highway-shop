<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentGenerator;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\PdfRenderer;
use Prems\Plugin\PremsIndividualOffer6\Core\Checkout\Document\OfferDocumentGenerator;
use Prems\Plugin\PremsIndividualOffer6\Core\Entity\Offer\Aggregate\OfferItem\OfferItemEntity;
use Prems\Plugin\PremsIndividualOffer6\Core\Offer\OfferDocumentService;
use Prems\Plugin\PremsIndividualOffer6\Core\Offer\PdfService;
use Prems\Plugin\PremsIndividualOffer6\Core\Offer\Storefront\OfferService;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PremsIndividualOfferPdfServiceDecorator extends PdfService
{

    private DocumentEditorHelperInterface $documentEditorHelper;
    private OfferDocumentService $offerDocumentService;
    private OfferService $offerService;
    private OfferDocumentGenerator $offerDocumentGenerator;
    private PdfRenderer $pdfRenderer;

    public function __construct(
        DocumentEditorHelperInterface $documentEditorHelper,
        OfferDocumentService $offerDocumentService,
        OfferService $offerService,
        OfferDocumentGenerator $offerDocumentGenerator,
        PdfRenderer $pdfRenderer
    )
    {
        $this->documentEditorHelper = $documentEditorHelper;
        $this->offerDocumentService = $offerDocumentService;
        $this->offerService = $offerService;
        $this->offerDocumentGenerator = $offerDocumentGenerator;
        $this->pdfRenderer = $pdfRenderer;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return RenderedDocument
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createPdfForOffer(string $offerId, SalesChannelContext $salesChannelContext): RenderedDocument
    {
        $documentBasicConfig = $this->offerDocumentService->getOfferDocument($salesChannelContext);
        $config = DocumentConfigurationFactory::createConfiguration($documentBasicConfig->getConfig(), $documentBasicConfig);

        // Sets the offer language for context to fetch the offer with right language
        $context = $salesChannelContext->getContext();
        if ($languageId = $this->offerService->getOfferLanguageId($offerId, $salesChannelContext->getContext())) {
            $context = $context->assign([
                'languageIdChain' => array_unique(array_filter([$languageId, $context->getLanguageId()])),
            ]);
        }

        $offer = $this->offerService->getOfferById($offerId, $context);
        $pluginConfig = $this->offerService->getConfigService()->getConfig($salesChannelContext);

        /** @var OfferItemEntity $offerItem */
        foreach ($offer->getItems() as $offerItem) {
            if ($offerItem->getLineItem()) {
                $this->handleCustomProduct($offerItem, $salesChannelContext, $pluginConfig);
            }
            $offerItem->assign(['calculatedPrice' => $offerItem->getNetGrossPrices($salesChannelContext, $this->offerService)]);
        }

        $documentHtml = $this->offerDocumentGenerator->generate($offer, $config, $salesChannelContext);
        $fileName = $this->offerDocumentGenerator->getFileName($config, $offer) . '.' . FileTypes::PDF;
        $contentType = 'application/' . FileTypes::PDF;

        $renderedDocument = new RenderedDocument($documentHtml);
        $renderedDocument->setName($fileName);
        $renderedDocument->setContentType($contentType);

        // Add dompdfOptions to generatedDocument
        $editorState = $this->documentEditorHelper->loadEditorState($documentBasicConfig->getId(), $salesChannelContext->getContext());
        if ($editorState) {
            $dompdfExtensions = [];
            $dompdfExtensions['dompdfOptions'] = $this->documentEditorHelper->getDomPdfOptions($editorState) ?? [];
            $renderedDocument->setExtensions(array_merge(
                $renderedDocument->getExtensions(),
                $dompdfExtensions
            ));
        }

        $renderedDocument->setContent($this->pdfRenderer->render($renderedDocument));
        return $renderedDocument;
    }
}
