<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Core\Content\Flow\Dispatching\Action;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Event\OrderAware;

class ChangeShippingAction extends FlowAction
{

    public function __construct(private readonly EntityRepository $orderDeliveryRepository, private readonly EntityRepository $productRepository)
    {
    }


    public static function getName(): string
    {
        return 'action.change.shipping';
    }

    public function requirements(): array
    {
        return [OrderAware::class];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        $config = $flow->getConfig();

        if (isset($config['shippingMethod'][0]['id']) && isset($config['condition']) && isset($config['weightAmount'])) {
            $shippingMethodId = $config['shippingMethod'][0]['id'];
            $weightAmount     = $config['weightAmount'];
            $condition        = $config['condition'];

            /** @var OrderEntity $order */
            if ($order = $flow->getData('order')) {
                $totalOrderWeight = $this->calculateTotalWeight($order->getLineItems());

                if ($this->criteriaMet($totalOrderWeight, $condition, $weightAmount)) {
                    if ($deliveries = $order->get('deliveries')?->first()) {
                        $this->orderDeliveryRepository->update([[
                            'id'               => $deliveries->id,
                            'shippingMethodId' => $shippingMethodId
                        ]], $flow->getContext());
                    }
                }
            }
        }
    }

    private function criteriaMet($value1, $operator, $value2): bool
    {
        return match ($operator) {
            '<' => $value1 < $value2,
            '<=' => $value1 <= $value2,
            '>' => $value1 > $value2,
            '>=' => $value1 >= $value2,
            '==' => $value1 == $value2,
            '!=' => $value1 != $value2,
            default => false,
        };
    }

    private function calculateTotalWeight(OrderLineItemCollection $lineItems): float
    {
        $totalWeight = 0;
        /** @var OrderLineItemEntity $lineItem */
        foreach ($lineItems as $lineItem) {
            if ($lineItem?->getProduct() == null) {
                $productId = $lineItem->getProductId();
                $criteria  = new Criteria([$productId]);
                $product   = $this->productRepository->search($criteria, Context::createDefaultContext())->first();
            } else {
                $product = $lineItem->getProduct();
            }

            //is variant article/get weight from main product, since weight is null in this case
            if ($product->getWeight() == null && $product->getParentId() !== null) {
                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('id', $product->getParentId()));
                /** @var ProductEntity $product */
                if ($parentProduct = $this->productRepository->search($criteria, Context::createDefaultContext())->first()) {
                    if ($parentProduct->getWeight() !== null) {
                        $totalWeight += ($parentProduct->getWeight() * $lineItem->getQuantity());
                    }
                }
            }

            if ($product?->getWeight() !== null) {
                $totalWeight += ($product->getWeight() * $lineItem->getQuantity());
            }
        }

        return $totalWeight;
    }

}