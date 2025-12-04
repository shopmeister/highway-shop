<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductAnalysisService
{
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
    }

    public function analyzeProduct(string $productId, string $languageId): array
    {
        $this->logger->info('Starting comprehensive product analysis', [
            'productId' => $productId,
            'languageId' => $languageId
        ]);

        // Get base product data
        $productData = $this->getProductBaseData($productId, $languageId);

        if (!$productData) {
            throw new \RuntimeException(sprintf('Product not found: %s', $productId));
        }

        // Determine if this is a parent or variant
        $isVariant = !empty($productData['parent_id']);
        $isParent = $this->hasVariants($productId);

        // Get parent data if this is a variant
        $parentData = null;
        if ($isVariant) {
            $parentData = $this->getProductBaseData($productData['parent_id'], $languageId);
        }

        // Get variants if this is a parent
        $variants = [];
        if ($isParent) {
            $variants = $this->getProductVariants($productId, $languageId);
        }

        // Get comprehensive product properties
        $properties = $this->getProductProperties($productId, $languageId);

        // Get category context
        $categories = $this->getProductCategories($productId, $languageId);

        // Analyze missing content
        $missingContent = $this->analyzeMissingContent($productData, $parentData);

        // Get manufacturer info - inherit from parent if variant has none
        $manufacturerId = $productData['manufacturer_id'] ?? null;
        $this->logger->debug('Manufacturer inheritance check', [
            'productId' => $productId,
            'isVariant' => $isVariant,
            'variantManufacturerId' => $manufacturerId,
            'parentManufacturerId' => $parentData['manufacturer_id'] ?? 'none'
        ]);

        if (!$manufacturerId && $isVariant && $parentData) {
            $manufacturerId = $parentData['manufacturer_id'] ?? null;
            $this->logger->info('Inheriting manufacturer from parent', [
                'variantId' => $productId,
                'inheritedManufacturerId' => $manufacturerId
            ]);
        }
        $manufacturer = $this->getManufacturerInfo($manufacturerId, $languageId);

        // Build comprehensive analysis
        $analysis = [
            'basic' => [
                'id' => $productId,
                'productNumber' => $productData['product_number'],
                'isVariant' => $isVariant,
                'isParent' => $isParent,
                'active' => (bool)$productData['active'],
                'ean' => $productData['ean'],
                'manufacturerNumber' => $productData['manufacturer_number']
            ],
            'content' => [
                'name' => $productData['name'],
                'description' => $productData['description'],
                'shortDescription' => $productData['short_description'],
                'metaTitle' => $productData['meta_title'],
                'metaDescription' => $productData['meta_description'],
                'keywords' => $productData['keywords']
            ],
            'parent' => $parentData ? [
                'id' => $parentData['id'],
                'name' => $parentData['name'],
                'description' => $parentData['description'],
                'shortDescription' => $parentData['short_description']
            ] : null,
            'variants' => $variants,
            'properties' => $properties,
            'categories' => $categories,
            'manufacturer' => $manufacturer,
            'missingContent' => $missingContent,
            'seoOpportunities' => $this->identifySeoOpportunities($productData, $parentData, $properties, $categories)
        ];

        $this->logger->info('Product analysis completed', [
            'productId' => $productId,
            'isVariant' => $isVariant,
            'isParent' => $isParent,
            'manufacturerResult' => $manufacturer,
            'finalManufacturerId' => $manufacturerId,
            'propertiesCount' => count($properties),
            'categoriesCount' => count($categories),
            'missingContentCount' => count($missingContent)
        ]);

        return $analysis;
    }

    private function getProductBaseData(string $productId, string $languageId): ?array
    {
        $sql = '
            SELECT
                HEX(p.id) as id,
                p.product_number,
                p.active,
                p.ean,
                p.manufacturer_number,
                HEX(p.parent_id) as parent_id,
                HEX(p.product_manufacturer_id) as manufacturer_id,
                pt.name,
                pt.description,
                NULL as short_description,
                pt.meta_title,
                pt.meta_description,
                pt.keywords,
                pt.custom_search_keywords
            FROM product p
            LEFT JOIN product_translation pt ON p.id = pt.product_id AND pt.language_id = UNHEX(?)
            WHERE p.id = UNHEX(?)
        ';

        return $this->connection->fetchAssociative($sql, [$languageId, $productId]) ?: null;
    }

    private function hasVariants(string $productId): bool
    {
        $count = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM product WHERE parent_id = UNHEX(?)',
            [$productId]
        );

        return (int)$count > 0;
    }

    private function getProductVariants(string $parentId, string $languageId): array
    {
        $sql = '
            SELECT
                HEX(p.id) as id,
                p.product_number,
                p.active,
                pt.name,
                pt.description,
                NULL as short_description
            FROM product p
            LEFT JOIN product_translation pt ON p.id = pt.product_id AND pt.language_id = UNHEX(?)
            WHERE p.parent_id = UNHEX(?)
            ORDER BY p.product_number
        ';

        return $this->connection->fetchAllAssociative($sql, [$languageId, $parentId]);
    }

    private function getProductProperties(string $productId, string $languageId): array
    {
        $sql = '
            SELECT
                HEX(pgo.id) as option_id,
                HEX(pg.id) as group_id,
                pgt.name as group_name,
                pgot.name as option_name,
                pg.sorting_type,
                pg.display_type,
                pgo.color_hex_code,
                pgo.media_id
            FROM product_property pp
            INNER JOIN property_group_option pgo ON pp.property_group_option_id = pgo.id
            INNER JOIN property_group pg ON pgo.property_group_id = pg.id
            LEFT JOIN property_group_translation pgt ON pg.id = pgt.property_group_id AND pgt.language_id = UNHEX(?)
            LEFT JOIN property_group_option_translation pgot ON pgo.id = pgot.property_group_option_id AND pgot.language_id = UNHEX(?)
            WHERE pp.product_id = UNHEX(?)
            ORDER BY pgt.name, pgot.name
        ';

        $results = $this->connection->fetchAllAssociative($sql, [$languageId, $languageId, $productId]);

        // Group by property group
        $grouped = [];
        foreach ($results as $result) {
            $groupName = $result['group_name'] ?: 'Unbekannt';
            if (!isset($grouped[$groupName])) {
                $grouped[$groupName] = [
                    'group_id' => $result['group_id'],
                    'group_name' => $groupName,
                    'display_type' => $result['display_type'],
                    'options' => []
                ];
            }
            $grouped[$groupName]['options'][] = [
                'option_id' => $result['option_id'],
                'name' => $result['option_name'],
                'color' => $result['color_hex_code'],
                'has_media' => !empty($result['media_id'])
            ];
        }

        return array_values($grouped);
    }

    private function getProductCategories(string $productId, string $languageId): array
    {
        $sql = '
            SELECT
                HEX(c.id) as id,
                ct.name,
                c.level,
                c.path,
                ct.meta_title,
                ct.meta_description,
                ct.keywords
            FROM product_category pc
            INNER JOIN category c ON pc.category_id = c.id
            LEFT JOIN category_translation ct ON c.id = ct.category_id AND ct.language_id = UNHEX(?)
            WHERE pc.product_id = UNHEX(?)
            ORDER BY c.level, ct.name
        ';

        return $this->connection->fetchAllAssociative($sql, [$languageId, $productId]);
    }

    private function getManufacturerInfo(?string $manufacturerId, string $languageId): ?array
    {
        if (!$manufacturerId) {
            return null;
        }

        $sql = '
            SELECT
                HEX(pm.id) as id,
                COALESCE(
                    pmt.name,
                    (SELECT name FROM product_manufacturer_translation WHERE product_manufacturer_id = pm.id LIMIT 1),
                    CONCAT("Manufacturer_", HEX(pm.id))
                ) as name,
                COALESCE(pmt.description, pmt_fallback.description) as description,
                pm.link,
                HEX(pm.media_id) as media_id
            FROM product_manufacturer pm
            LEFT JOIN product_manufacturer_translation pmt ON pm.id = pmt.product_manufacturer_id AND pmt.language_id = UNHEX(?)
            LEFT JOIN product_manufacturer_translation pmt_fallback ON pm.id = pmt_fallback.product_manufacturer_id
            WHERE pm.id = UNHEX(?)
            GROUP BY pm.id
        ';

        return $this->connection->fetchAssociative($sql, [$languageId, $manufacturerId]) ?: null;
    }

    private function analyzeMissingContent(array $productData, ?array $parentData): array
    {
        $missing = [];

        // Check required content
        if (empty($productData['name'])) {
            $missing[] = 'name';
        }

        if (empty($productData['description'])) {
            if ($parentData && !empty($parentData['description'])) {
                $missing[] = 'description_can_inherit_from_parent';
            } else {
                $missing[] = 'description';
            }
        }

        if (empty($productData['short_description'])) {
            if ($parentData && !empty($parentData['short_description'])) {
                $missing[] = 'short_description_can_inherit_from_parent';
            } else {
                $missing[] = 'short_description';
            }
        }

        // Check SEO content
        if (empty($productData['meta_title'])) {
            $missing[] = 'meta_title';
        }

        if (empty($productData['meta_description'])) {
            $missing[] = 'meta_description';
        }

        if (empty($productData['keywords'])) {
            $missing[] = 'keywords';
        }

        return $missing;
    }

    private function identifySeoOpportunities(array $productData, ?array $parentData, array $properties, array $categories): array
    {
        $opportunities = [];

        // Title optimization opportunities
        $currentTitle = $productData['name'] ?? '';
        if (strlen($currentTitle) < 30) {
            $opportunities[] = [
                'type' => 'title_too_short',
                'message' => 'Titel könnte länger und beschreibender sein',
                'current_length' => strlen($currentTitle),
                'recommended_length' => '45-60 Zeichen'
            ];
        }

        if (strlen($currentTitle) > 70) {
            $opportunities[] = [
                'type' => 'title_too_long',
                'message' => 'Titel könnte für SEO gekürzt werden',
                'current_length' => strlen($currentTitle),
                'recommended_length' => '45-60 Zeichen'
            ];
        }

        // Check if important properties are mentioned in title
        $importantProperties = ['Größe', 'Farbe', 'Material', 'Marke'];
        foreach ($properties as $propertyGroup) {
            if (in_array($propertyGroup['group_name'], $importantProperties)) {
                $propertyValues = array_column($propertyGroup['options'], 'name');
                $titleContainsProperty = false;
                foreach ($propertyValues as $value) {
                    if (stripos($currentTitle, $value) !== false) {
                        $titleContainsProperty = true;
                        break;
                    }
                }
                if (!$titleContainsProperty) {
                    $opportunities[] = [
                        'type' => 'missing_property_in_title',
                        'message' => sprintf('Eigenschaft "%s" könnte im Titel erwähnt werden', $propertyGroup['group_name']),
                        'property_group' => $propertyGroup['group_name'],
                        'available_values' => $propertyValues
                    ];
                }
            }
        }

        // Meta description opportunities
        $metaDesc = $productData['meta_description'] ?? '';
        if (empty($metaDesc)) {
            $opportunities[] = [
                'type' => 'missing_meta_description',
                'message' => 'Meta-Description fehlt vollständig'
            ];
        } elseif (strlen($metaDesc) < 120) {
            $opportunities[] = [
                'type' => 'meta_description_too_short',
                'message' => 'Meta-Description könnte ausführlicher sein',
                'current_length' => strlen($metaDesc),
                'recommended_length' => '140-160 Zeichen'
            ];
        }

        // Category context opportunities
        if (count($categories) === 0) {
            $opportunities[] = [
                'type' => 'no_categories',
                'message' => 'Produkt ist keiner Kategorie zugeordnet'
            ];
        }

        // Variant-specific opportunities
        if ($parentData) {
            $opportunities[] = [
                'type' => 'variant_optimization',
                'message' => 'Als Variante kann der Titel mit spezifischen Eigenschaften optimiert werden',
                'parent_title' => $parentData['name']
            ];
        }

        return $opportunities;
    }

    public function buildKeywordContext(array $analysis): array
    {
        $keywords = [
            'primary' => [],
            'secondary' => [],
            'long_tail' => []
        ];

        // Extract from product name
        if (!empty($analysis['content']['name'])) {
            $nameWords = $this->extractKeywords($analysis['content']['name']);
            $keywords['primary'] = array_merge($keywords['primary'], $nameWords);
        }

        // Extract from categories
        foreach ($analysis['categories'] as $category) {
            if (!empty($category['name'])) {
                $categoryWords = $this->extractKeywords($category['name']);
                $keywords['secondary'] = array_merge($keywords['secondary'], $categoryWords);
            }
        }

        // Extract from properties
        foreach ($analysis['properties'] as $propertyGroup) {
            foreach ($propertyGroup['options'] as $option) {
                if (!empty($option['name'])) {
                    $propertyWords = $this->extractKeywords($option['name']);
                    $keywords['secondary'] = array_merge($keywords['secondary'], $propertyWords);
                }
            }
        }

        // Extract from manufacturer
        if (!empty($analysis['manufacturer']['name'])) {
            $brandWords = $this->extractKeywords($analysis['manufacturer']['name']);
            $keywords['primary'] = array_merge($keywords['primary'], $brandWords);
        }

        // Create long-tail keywords
        $keywords['long_tail'] = $this->generateLongTailKeywords($keywords['primary'], $keywords['secondary']);

        // Clean and deduplicate
        $keywords['primary'] = array_unique(array_filter($keywords['primary']));
        $keywords['secondary'] = array_unique(array_filter($keywords['secondary']));
        $keywords['long_tail'] = array_unique(array_filter($keywords['long_tail']));

        return $keywords;
    }

    private function extractKeywords(string $text): array
    {
        // Remove special characters and split
        $text = preg_replace('/[^\w\säöüÄÖÜß-]/', ' ', $text);
        $words = preg_split('/\s+/', trim($text));

        // Filter out stop words and short words
        $stopWords = ['der', 'die', 'das', 'und', 'oder', 'mit', 'für', 'auf', 'von', 'zu', 'in', 'an', 'bei', 'aus'];
        $keywords = [];

        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && !in_array(strtolower($word), $stopWords)) {
                $keywords[] = $word;
            }
        }

        return $keywords;
    }

    private function generateLongTailKeywords(array $primary, array $secondary): array
    {
        $longTail = [];

        // Combine primary with secondary
        foreach ($primary as $p) {
            foreach ($secondary as $s) {
                if ($p !== $s) {
                    $longTail[] = $p . ' ' . $s;
                    $longTail[] = $s . ' ' . $p;
                }
            }
        }

        // Limit to reasonable number
        return array_slice($longTail, 0, 20);
    }
}