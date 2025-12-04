<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Core\Content\Product\DataAbstractionLayer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Content\Product\Events\ProductIndexerEvent;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductAvailabilityUpdater implements EventSubscriberInterface
{
    /**
     * @param EntityRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $productRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly SystemConfigService $config,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_INDEXER_EVENT => 'onProductIndexed',
            CheckoutOrderPlacedEvent::class => ['onOrderPlaced', -255],
            StateMachineTransitionEvent::class => ['onStateChanged', -255],
        ];
    }

    public function onProductIndexed(ProductIndexerEvent $event): void
    {
        $this->update($event->getIds(), $event->getContext());
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $lineItems = $event->getOrder()->getLineItems();
        if ($lineItems === null) {
            return;
        }

        $ids = [];
        foreach ($lineItems as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE || $lineItem->getReferencedId() === null) {
                continue;
            }

            $ids[] = $lineItem->getReferencedId();
        }

        $this->update($ids, $event->getContext());
    }

    public function onStateChanged(StateMachineTransitionEvent $event): void
    {
        if ($event->getEntityName() !== OrderDefinition::ENTITY_NAME
            || $event->getFromPlace()->getTechnicalName() !== OrderStates::STATE_COMPLETED
                && $event->getToPlace()->getTechnicalName() !== OrderStates::STATE_COMPLETED) {
            return;
        }

        $this->updateByOrder($event->getEntityId(), $event->getContext());
    }

    /**
     * @param string[] $ids
     */
    public function update(array $ids, Context $context): void
    {
        $ids = array_filter(array_unique($ids));

        if ($ids === []) {
            return;
        }

        foreach ($this->fetchSalesChannelIds() as $salesChannelId) {
            $minStock = max(0, $this->config->getInt('SwkwebHideSoldoutProducts.config.minStock', $salesChannelId));
            $exemptionIds = $this->getExemptionIds($ids, $salesChannelId);

            RetryableQuery::retryable(
                $this->connection,
                function () use ($ids, $minStock, $exemptionIds, $context, $salesChannelId): void {
                    $this->connection->executeStatement(
                        $this->getUpdateQuerySql($salesChannelId, $exemptionIds !== []),
                        [
                            'ids' => Uuid::fromHexToBytesList($ids),
                            'version' => Uuid::fromHexToBytes($context->getVersionId()),
                            'salesChannelId' => Uuid::fromHexToBytes($salesChannelId),
                            'exemptionIds' => Uuid::fromHexToBytesList($exemptionIds),
                            'minStock' => $minStock,
                        ],
                        [
                            'ids' => ArrayParameterType::STRING,
                            'exemptionIds' => ArrayParameterType::STRING,
                        ],
                    );
                },
            );
        }
    }

    public function updateByOrder(string $orderId, Context $context): void
    {
        $this->update(
            $this->fetchOrderProductIds($orderId, $context),
            $context,
        );
    }

    /**
     * @return string[]
     */
    private function fetchSalesChannelIds(): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('LOWER(HEX(id))')
            ->from('sales_channel')
        ;

        $ids = $qb->executeQuery()->fetchFirstColumn();

        assert($ids === array_filter($ids, 'is_string'));

        return $ids;
    }

    /**
     * @return string[]
     */
    private function fetchOrderProductIds(string $orderId, Context $context): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('referenced_id')
            ->from('order_line_item')
            ->where($qb->expr()->and(
                $qb->expr()->eq(
                    'type',
                    $qb->createPositionalParameter(LineItem::PRODUCT_LINE_ITEM_TYPE),
                ),
                $qb->expr()->eq(
                    'order_id',
                    $qb->createPositionalParameter(Uuid::fromHexToBytes($orderId)),
                ),
                $qb->expr()->eq(
                    'version_id',
                    $qb->createPositionalParameter(Uuid::fromHexToBytes($context->getVersionId())),
                ),
            ))
        ;

        $ids = $qb->executeQuery()->fetchFirstColumn();

        assert($ids === array_filter($ids, 'is_string'));

        return $ids;
    }

    /**
     * @param string[] $ids
     *
     * @return string[]
     */
    private function getExemptionIds(array $ids, string $salesChannelId): array
    {
        $productStreamId = $this->config->getString(
            'SwkwebHideSoldoutProducts.config.exemptionsProductStream',
            $salesChannelId,
        );

        if ($productStreamId === '') {
            return [];
        }

        // Create default context, as the version ID might be different when an order is updated
        $context = Context::createDefaultContext();

        $criteria = new Criteria($ids);
        $criteria->addFilter(
            ...$this->productStreamBuilder->buildFilters($productStreamId, $context),
        );

        $ids = $this->productRepository->searchIds($criteria, $context)->getIds();
        assert($ids === array_filter($ids, 'is_string'));

        return $ids;
    }

    private function getUpdateQuerySql(string $salesChannelId, bool $hasExemptions): string
    {
        $soldoutExpressionSql = $this->getSoldoutExpressionSql($salesChannelId, $hasExemptions);

        return <<<SQL
            INSERT INTO
                swkweb_hide_soldout_products_product_availability (
                    product_id,
                    product_version_id,
                    sales_channel_id,
                    soldout
                )
                SELECT
                    product.id,
                    product.version_id,
                    :salesChannelId,
                    {$soldoutExpressionSql}
                FROM
                    product
                    LEFT JOIN
                        product parent
                        ON parent.id = product.parent_id AND parent.version_id = product.version_id
                WHERE
                    product.id IN (:ids) AND product.version_id = :version
                GROUP BY
                    product.id
            ON DUPLICATE KEY UPDATE
                soldout = VALUES(soldout)
            SQL;
    }

    private function getSoldoutExpressionSql(string $salesChannelId, bool $hasExemptions): string
    {
        $expr = $this->connection->createExpressionBuilder();

        $soldoutExpression = $expr->and(
            $expr->lt(
                'product.available_stock - :minStock',
                'COALESCE(product.min_purchase, parent.min_purchase, 1)',
            ),
        );

        if (!$this->config->getBool('SwkwebHideSoldoutProducts.config.ignoreCloseout', $salesChannelId)) {
            $soldoutExpression = $soldoutExpression->with('IFNULL(product.is_closeout, parent.is_closeout)');
        }

        if ($hasExemptions) {
            $soldoutExpression = $soldoutExpression->with($expr->notIn('product.id', ':exemptionIds'));
        }

        return (string) $soldoutExpression;
    }
}
