<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\PremsIndividualOffer;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\TranslatorService;
use Exception;
use Prems\Plugin\PremsIndividualOffer6\Core\Entity\Offer\Aggregate\OfferItem\OfferItemEntity;
use Prems\Plugin\PremsIndividualOffer6\Core\Entity\Offer\OfferEntity;
use Prems\Plugin\PremsIndividualOffer6\Core\Offer\Storefront\OfferService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;

class OfferResolverDecorator implements OrderDocumentResolverInterface
{

    public function __construct(
        private readonly OrderDocumentResolverInterface     $decoratedResolver,
        private readonly TranslatorService                  $translatorService,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly Logger                             $logger,
        private readonly ?OfferService                      $offerService,
        private readonly ?EntityRepository                  $offerRepository,
        private readonly ?EntityRepository                  $offerMessageRepository,
    )
    {
    }

    /**
     * This method is only used when generating previews inside the editor.
     * PROD document compatibility is established in
     * @param Context $context
     * @param $orderNumber
     * @param $documentType
     * @return array
     * @throws Exception
     * @see \Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentGenerator\PremsIndividualOfferPreviewDocumentGenerator
     *
     */
    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($documentType, $context, &$templateData) {

            if (in_array($documentType, ['prems_individual_offer', 'individual_offer']) &&
                !!$this->offerService &&
                !!$this->offerRepository &&
                !!$this->offerMessageRepository) {

                $offerId = $this->getLatestOfferId($context);

                if (!$offerId) {
                    throw new Exception($this->translatorService->trans('dde.exception.premsoft-individual-offer-missing', $context));
                }

                // Sets the offer language for context to fetch the offer with the right language
                if ($languageId = $this->offerService->getOfferLanguageId($offerId, $context)) {
                    $context = $context->assign([
                        'languageIdChain' => array_unique(array_filter([$languageId, $context->getLanguageId()])),
                    ]);
                }

                // Fetch offer
                $offer = $this->getOfferById($offerId, $context);

                // Generate sales channel context
                $salesChannelContext = $this->salesChannelContextFactory->create(
                    Uuid::randomHex(),
                    $offer->getSalesChannelId(),
                    [
                        SalesChannelContextService::CUSTOMER_ID => $offer->getCustomerId()
                    ]
                );

                /** @var OfferItemEntity $offerItem */
                foreach ($offer->getItems() as $offerItem) {
                    if ($offerItem->getLineItem()) {
                        $this->handleCustomProduct($offerItem);
                    }
                    $offerItem->assign(['calculatedPrice' => $offerItem->getNetGrossPrices($salesChannelContext, $this->offerService)]);
                }

                $offerTemplateData = [
                    'order' => $offer,
                    'billingAddress' => $offer->getBillingAddress() ?? $offer->getCustomer()->getActiveBillingAddress()
                ];

                $templateData = array_merge($templateData, $offerTemplateData);

            }

        }, "Premsoft individual offer data resolution duration: %s ms");

        return $templateData;
    }

    /**
     * Get on offer by offer ID
     * @param string $offerId
     * @param Context $context
     * @param CustomerEntity|null $customer
     * @return OfferEntity
     */
    private function getOfferById(string $offerId, Context $context, CustomerEntity $customer = null): OfferEntity
    {
        $criteria = new Criteria([$offerId]);

        if ($customer) {
            $criteria->addFilter(new EqualsFilter('prems_individual_offer.customerId', $customer->getId()));
        }

        $criteria
            ->addAssociation('items')
            ->addAssociation('items.product')
            ->addAssociation('items.product.prices')
            ->addAssociation('items.product.cover')
            ->addAssociation('items.product.options.group')
            ->addAssociation('items.product.unit')
            ->addAssociation('items.product.visibilities')
            ->addAssociation('items.product.deliveryTime')
            ->addAssociation('customer')
            ->addAssociation('customer.addresses')
            ->addAssociation('customer.salutation')
            ->addAssociation('customer.defaultBillingAddress')
            ->addAssociation('customer.activeBillingAddress')
            ->addAssociation('customer.defaultBillingAddress.country')
            ->addAssociation('customer.activeBillingAddress.country')
            ->addAssociation('shippingMethod.tax')
            ->addAssociation('currency')
            ->addAssociation('salesChannel.domains')
            ->addAssociation('salesChannel.mailHeaderFooter')
            ->addAssociation('items.product.deliveryTime')
            ->addAssociation('language.locale')
            ->addAssociation('shippingAddress')
            ->addAssociation('billingAddress');

        $criteria
            ->getAssociation('items')
            ->addSorting(new FieldSorting('position'));

        $context->setConsiderInheritance(true);
        $offer = $this->offerRepository->search($criteria, $context)->first();

        if ($offer && $customer != null) {
            $criteriaMessages = (new Criteria())
                ->addFilter(new EqualsFilter('prems_individual_offer_messages.offerId', $offer->getId()))
                ->addFilter(new EqualsFilter('prems_individual_offer_messages.customerId', $customer->getId()))
                ->addAssociation('user')
                ->addAssociation('customer')
                ->addSorting(new FieldSorting('prems_individual_offer_messages.createdAt', FieldSorting::DESCENDING));

            $messages = $this->offerMessageRepository->search($criteriaMessages, $context)->getEntities();
            $offer->setMessages($messages);
        }

        return $offer;
    }

    private function getLatestOfferId(Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        /** @var OfferEntity $offer */
        if ($offer = $this->offerRepository->search($criteria, $context)->getEntities()->first()) {
            return $offer->getId();
        }

        return null;
    }

    private function handleCustomProduct(OfferItemEntity $offerItem): void
    {
        if ($offerItem->getItemType() === 'swag-customized-products') {

            /** @var LineItem $lineItem */
            if ($lineItem = $offerItem->decodedLineItem) {
                $children = $lineItem->getChildren();

                $lineItem = $children->first();
                $lineItem->additionalInformation = $offerItem->getAdditionalInformation();
                $children->remove($lineItem->getId());
                $lineItem->setChildren($children);

                $offerItem->decodedLineItem = $lineItem;
            }
        }
    }

    public function getAssociations(string $type): array
    {
        return $this->decoratedResolver->getAssociations($type);
    }

    public function getOrderById($orderId, Context $context): ?OrderEntity
    {
        return $this->decoratedResolver->getOrderById($orderId, $context);
    }

    public function getPdfDocument(Context $context, $documentType, OrderEntity $order): ?DocumentEntity
    {
        return $this->decoratedResolver->getPdfDocument($context, $documentType, $order);
    }

    public function canResolveType(string $type): bool
    {
        return $this->decoratedResolver->canResolveType($type);
    }

    public function resolve(PreviewDataResolverContextInterface $context, string $type): array
    {
        return $this->decoratedResolver->resolve($context, $type);
    }

    public function getAdditionalDataTypes(string $type, Context $context): array
    {
        return $this->decoratedResolver->getAdditionalDataTypes($type, $context);
    }

    public function getAvailableEntities(): array
    {
        return $this->decoratedResolver->getAvailableEntities();
    }

}
