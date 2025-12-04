<?php declare(strict_types=1);

namespace Swkweb\HideSoldoutProducts\Elasticsearch\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;
use Shopware\Elasticsearch\Product\ElasticsearchProductDefinition;

class HideSoldoutProductDefinition extends AbstractElasticsearchDefinition
{
    public function __construct(
        private readonly AbstractElasticsearchDefinition $productDefinition,
        private readonly Connection $connection,
    ) {}

    public function getEntityDefinition(): EntityDefinition
    {
        return $this->productDefinition->getEntityDefinition();
    }

    public function getMapping(Context $context): array
    {
        $mapping = $this->productDefinition->getMapping($context);

        $mapping['properties']['swkwebHideSoldoutProductsAvailability'] = [
            'type' => 'nested',
            'properties' => [
                'productId' => ElasticsearchProductDefinition::KEYWORD_FIELD,
                'soldout' => ElasticsearchProductDefinition::BOOLEAN_FIELD,
                'salesChannelId' => ElasticsearchProductDefinition::KEYWORD_FIELD,
            ],
        ];

        return $mapping;
    }

    public function fetch(array $ids, Context $context): array
    {
        $availability = $this->fetchProductAvailability($ids);
        $documents = $this->productDefinition->fetch($ids, $context);
        foreach ($documents as &$document) {
            $document['swkwebHideSoldoutProductsAvailability'] = $availability[$document['id']] ?? null;
        }

        return $documents;
    }

    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
        return $this->productDefinition->buildTermQuery($context, $criteria);
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, list<array{product_id: string, soldout: bool, salesChannelId: string, _count: int}>>
     */
    private function fetchProductAvailability(array $ids): array
    {
        $query = <<<'SQL'
                SELECT LOWER(HEX(product_id)) as product_id, soldout, LOWER(HEX(sales_channel_id)) as sales_channel_id
                FROM swkweb_hide_soldout_products_product_availability
                WHERE product_id IN (:ids)
            SQL;

        $result = [];

        $rows = $this->connection->fetchAllAssociative($query, ['ids' => $ids], ['ids' => ArrayParameterType::STRING]);
        foreach ($rows as $row) {
            if (!is_string($row['product_id']) || !is_string($row['sales_channel_id'])) {
                continue;
            }

            $productId = $row['product_id'];

            $result[$productId] ??= [];

            $result[$productId][] = [
                'product_id' => $productId,
                'soldout' => (bool) $row['soldout'],
                'salesChannelId' => $row['sales_channel_id'],
                '_count' => 1,
            ];
        }

        return $result;
    }
}
