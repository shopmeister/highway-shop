<?php

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\SwagCommercial;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\PreviewDataResolverContextInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Service\DataResolver\Resolver\OrderDocumentResolverInterface;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\QuoteDocumentCriteriaFactory;
use Shopware\Commercial\B2B\QuoteManagement\Domain\Document\QuoteDocumentRenderer;
use Shopware\Commercial\B2B\QuoteManagement\Entity\Quote\QuoteEntity;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Feature;

class QuoteResolverDecorator implements OrderDocumentResolverInterface
{
    private OrderDocumentResolverInterface $decoratedResolver;
    private ?EntityRepository $quoteRepository;

    public function __construct(
        OrderDocumentResolverInterface $decoratedResolver,
        private readonly Logger        $logger,
        ?EntityRepository              $quoteRepository
    )
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->quoteRepository = $quoteRepository;
    }

    public function getTemplateData(Context $context, $orderNumber = null, $documentType = null): array
    {
        $templateData = $this->decoratedResolver->getTemplateData($context, $orderNumber, $documentType);

        $this->logger->logExecutionDuration(function () use ($documentType, $context, &$templateData) {

            if (class_exists(QuoteDocumentRenderer::class) && $documentType === QuoteDocumentRenderer::TYPE && $this->quoteRepository) {
                $quoteId = $this->getLatestQuoteId($context);

                if ($quoteId) {
                    $criteria = $this->getQuoteCriteria($quoteId);
                    $criteria->addAssociations([
                        'lineItems.product',
                        'lineItems.product.unit',
                        'lineItems.product.visibilities',
                        'lineItems.product.cover',
                        'lineItems.product.deliveryTime',
                    ]);

                    /** @var QuoteEntity|null $quote */
                    $quote = $this->quoteRepository->search($criteria, $context)->get($quoteId);

                    if (class_exists(QuoteEntity::class) && $quote instanceof QuoteEntity) {
                        if (isset($templateData['order'])) unset($templateData['order']);
                        $templateData = array_merge($templateData, [
                            'quote' => $quote
                        ]);
                    }
                }
            }

        }, "Quote data resolution duration: %s ms");

        return $templateData;
    }

    private function getLatestQuoteId(Context $context): ?string
    {
        if ($this->quoteRepository === null) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        /** @var QuoteEntity|null $quote */
        $quote = $this->quoteRepository->search($criteria, $context)->first();

        return $quote?->getId();
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

    private function getQuoteCriteria(string $id): Criteria
    {
        $criteria = new Criteria([$id]);

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

        if (!Feature::isActive('v6.7.0.0')) {
            $criteria->addAssociation('customer.defaultPaymentMethod');
        }

        return $criteria;
    }

}

