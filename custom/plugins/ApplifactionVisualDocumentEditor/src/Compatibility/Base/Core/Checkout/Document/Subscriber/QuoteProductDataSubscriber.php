<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Subscriber;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\BeforeRenderTemplateDataEvent;
use Shopware\Commercial\B2B\QuoteManagement\Entity\Quote\QuoteEntity;
use Shopware\Commercial\B2B\QuoteManagement\Entity\QuoteLineItem\QuoteLineItemEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuoteProductDataSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository  $productRepository,
        private readonly ?EntityRepository $quoteRepository = null
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRenderTemplateDataEvent::class => 'onBeforeRender',
        ];
    }

    public function onBeforeRender(BeforeRenderTemplateDataEvent $event): void
    {
        $templateData = $event->getTemplateData();
        $context = $event->getContext() ?? new Context(new SystemSource());

        if (!class_exists(QuoteEntity::class) || !isset($templateData['quote']) || !$templateData['quote'] instanceof QuoteEntity) {
            return;
        }

        $quote = $templateData['quote'];
        $lineItems = $quote->getLineItems();
        if (!$lineItems) {
            return;
        }

        $productIds = [];
        foreach ($lineItems as $lineItem) {
            if ($lineItem->getProductId() && $lineItem->getProduct() === null) {
                $productIds[] = $lineItem->getProductId();
            }
        }

        if (empty($productIds)) {
            return;
        }

        $criteria = new Criteria($productIds);
        $criteria->addAssociations(['unit', 'visibilities', 'cover', 'deliveryTime']);
        $products = $this->productRepository->search($criteria, $context)->getEntities();

        $parentIds = [];
        foreach ($products as $product) {
            if ($product->getParentId() && $product->getParent() === null) {
                $parentIds[] = $product->getParentId();
            }
        }

        if (!empty($parentIds)) {
            $parentCriteria = new Criteria($parentIds);
            $parentCriteria->addAssociations(['unit', 'visibilities', 'cover', 'deliveryTime']);
            $parents = $this->productRepository->search($parentCriteria, $context)->getEntities();

            foreach ($products as $product) {
                if ($product->getParentId() && $parents->has($product->getParentId())) {
                    $product->setParent($parents->get($product->getParentId()));
                }
            }
        }

        foreach ($lineItems as $lineItem) {
            if ($lineItem->getProductId() && $lineItem->getProduct() === null && $products->has($lineItem->getProductId())) {
                $lineItem->setProduct($products->get($lineItem->getProductId()));
            }
        }

        $event->setTemplateData($templateData);
    }
}
