<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WebResearchService
{
    private const SEARCH_ENGINES = [
        'google' => 'https://www.google.com/search?q=',
        'bing' => 'https://www.bing.com/search?q='
    ];

    private const REQUEST_TIMEOUT = 10;
    private const MAX_RETRIES = 2;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger
    ) {
    }

    public function researchProductContext(array $analysis): array
    {
        $this->logger->info('Starting web research for product context', [
            'productId' => $analysis['basic']['id'],
            'productName' => $analysis['content']['name']
        ]);

        $researchData = [
            'semantic_keywords' => [],
            'competitor_insights' => [],
            'market_context' => [],
            'suggested_benefits' => [],
            'trending_terms' => []
        ];

        try {
            // Build search queries
            $searchQueries = $this->buildSearchQueries($analysis);

            // Research semantic keywords
            $researchData['semantic_keywords'] = $this->researchSemanticKeywords($searchQueries);

            // Research competitor context
            $researchData['competitor_insights'] = $this->researchCompetitorContext($searchQueries);

            // Research benefits and features
            $researchData['suggested_benefits'] = $this->researchProductBenefits($analysis);

            // Trending terms (simplified approach)
            $researchData['trending_terms'] = $this->identifyTrendingTerms($analysis);

        } catch (\Exception $e) {
            $this->logger->error('Web research failed', [
                'error' => $e->getMessage(),
                'productId' => $analysis['basic']['id']
            ]);

            // Return fallback research data
            $researchData = $this->generateFallbackResearch($analysis);
        }

        $this->logger->debug('Web research completed', [
            'productId' => $analysis['basic']['id'],
            'semanticKeywordsCount' => count($researchData['semantic_keywords']),
            'benefitsCount' => count($researchData['suggested_benefits'])
        ]);

        return $researchData;
    }

    private function buildSearchQueries(array $analysis): array
    {
        $queries = [];

        // Base product query
        $productName = $analysis['content']['name'] ?? '';
        if ($productName) {
            $queries['product_base'] = $productName;
        }

        // Category-specific queries
        foreach ($analysis['categories'] as $category) {
            if (!empty($category['name'])) {
                $queries['category_' . $category['id']] = $category['name'] . ' kaufen';
            }
        }

        // Manufacturer + product queries
        if (!empty($analysis['manufacturer']['name']) && $productName) {
            $queries['brand_product'] = $analysis['manufacturer']['name'] . ' ' . $productName;
        }

        // Property-specific queries
        foreach ($analysis['properties'] as $propertyGroup) {
            if (in_array($propertyGroup['group_name'], ['Farbe', 'Größe', 'Material', 'Stil'])) {
                foreach ($propertyGroup['options'] as $option) {
                    $queries['property_' . $propertyGroup['group_name']] =
                        $productName . ' ' . $propertyGroup['group_name'] . ' ' . $option['name'];
                }
                break; // Limit to first option per group
            }
        }

        return array_slice($queries, 0, 5); // Limit queries to avoid rate limiting
    }

    private function researchSemanticKeywords(array $queries): array
    {
        $semanticKeywords = [];

        // For now, we'll simulate web research with intelligent keyword expansion
        // In production, this could use actual web scraping or SEO APIs

        foreach ($queries as $type => $query) {
            $expandedKeywords = $this->expandKeywordsSemantically($query);
            $semanticKeywords = array_merge($semanticKeywords, $expandedKeywords);
        }

        return array_unique($semanticKeywords);
    }

    private function expandKeywordsSemantically(string $baseKeyword): array
    {
        $expansions = [];

        // Common semantic expansions for German e-commerce
        $semanticPatterns = [
            'action_words' => ['kaufen', 'bestellen', 'online kaufen', 'günstig kaufen', 'sale'],
            'quality_terms' => ['hochwertig', 'premium', 'qualität', 'beste', 'top'],
            'descriptive_terms' => ['modern', 'stylish', 'elegant', 'praktisch', 'komfortabel'],
            'comparison_terms' => ['vergleich', 'test', 'bewertung', 'erfahrung'],
            'seasonal_terms' => ['sommer', 'winter', 'herbst', 'frühling', '2024'],
            'benefit_terms' => ['vorteile', 'nutzen', 'eigenschaften', 'features']
        ];

        // Extract base words from the keyword
        $baseWords = preg_split('/\s+/', strtolower(trim($baseKeyword)));

        foreach ($semanticPatterns as $category => $patterns) {
            foreach ($patterns as $pattern) {
                // Combine with base words
                foreach ($baseWords as $baseWord) {
                    if (strlen($baseWord) > 3) {
                        $expansions[] = $baseWord . ' ' . $pattern;
                        $expansions[] = $pattern . ' ' . $baseWord;
                    }
                }
            }
        }

        // Add original keyword variations
        $expansions[] = $baseKeyword . ' online';
        $expansions[] = $baseKeyword . ' shop';
        $expansions[] = $baseKeyword . ' kaufen';

        return array_slice(array_unique($expansions), 0, 10);
    }

    private function researchCompetitorContext(array $queries): array
    {
        // Simulate competitor research
        // In production, this would analyze competitor product listings

        return [
            'common_title_patterns' => [
                'Brand + Product + Key Features',
                'Product + Benefits + Call-to-Action',
                'Category + Brand + Specific Model'
            ],
            'frequently_used_terms' => [
                'kostenloser versand',
                'schnelle lieferung',
                'premium qualität',
                'beste preise',
                '30 tage rückgaberecht'
            ],
            'title_length_analysis' => [
                'average_length' => 55,
                'optimal_range' => '45-65 characters',
                'common_structure' => 'Brand | Product Name | Key Feature'
            ]
        ];
    }

    private function researchProductBenefits(array $analysis): array
    {
        $benefits = [];

        // Extract benefits from properties
        foreach ($analysis['properties'] as $propertyGroup) {
            $benefits = array_merge($benefits, $this->mapPropertiesToBenefits($propertyGroup));
        }

        // Extract benefits from categories
        foreach ($analysis['categories'] as $category) {
            $benefits = array_merge($benefits, $this->mapCategoriesToBenefits($category));
        }

        // Add generic e-commerce benefits
        $genericBenefits = [
            'Schnelle Lieferung',
            'Kostenloser Versand ab 50€',
            'Premium Qualität',
            '30 Tage Rückgaberecht',
            'Kundenservice Support',
            'Sichere Zahlung'
        ];

        $benefits = array_merge($benefits, $genericBenefits);

        return array_unique(array_slice($benefits, 0, 8));
    }

    private function mapPropertiesToBenefits(array $propertyGroup): array
    {
        $benefits = [];
        $groupName = strtolower($propertyGroup['group_name']);

        $benefitMappings = [
            'material' => ['Hochwertige Materialien', 'Langlebige Qualität', 'Nachhaltige Produktion'],
            'farbe' => ['Viele Farbvarianten', 'Trendige Farben', 'Individuelle Farbauswahl'],
            'größe' => ['Verschiedene Größen', 'Perfekte Passform', 'Größenberatung'],
            'stil' => ['Modernes Design', 'Zeitloses Design', 'Stilvolle Optik'],
            'marke' => ['Markenqualität', 'Bekannte Marke', 'Vertrauenswürdiger Hersteller']
        ];

        foreach ($benefitMappings as $property => $propertyBenefits) {
            if (stripos($groupName, $property) !== false) {
                $benefits = array_merge($benefits, $propertyBenefits);
                break;
            }
        }

        return $benefits;
    }

    private function mapCategoriesToBenefits(array $category): array
    {
        $benefits = [];
        $categoryName = strtolower($category['name'] ?? '');

        $categoryBenefits = [
            'elektronik' => ['Neueste Technologie', 'Hohe Leistung', 'Energieeffizient'],
            'kleidung' => ['Komfortabler Tragekomfort', 'Modische Styles', 'Pflegeleicht'],
            'möbel' => ['Funktionales Design', 'Platzsparend', 'Einfache Montage'],
            'sport' => ['Professionelle Qualität', 'Leistungssteigernd', 'Langlebig'],
            'beauty' => ['Dermatologisch getestet', 'Natürliche Inhaltsstoffe', 'Für alle Hauttypen']
        ];

        foreach ($categoryBenefits as $catKey => $catBenefits) {
            if (stripos($categoryName, $catKey) !== false) {
                $benefits = array_merge($benefits, $catBenefits);
                break;
            }
        }

        return $benefits;
    }

    private function identifyTrendingTerms(array $analysis): array
    {
        // Simulate trending terms identification
        // In production, this could use Google Trends API or similar

        $currentYear = date('Y');
        $currentSeason = $this->getCurrentSeason();

        $trendingTerms = [
            'seasonal' => [
                $currentSeason . ' ' . $currentYear,
                'neu ' . $currentYear,
                'trend ' . $currentYear
            ],
            'sustainability' => [
                'nachhaltig',
                'umweltfreundlich',
                'recyclebar',
                'eco-friendly'
            ],
            'digital' => [
                'smart',
                'digital',
                'app-steuerung',
                'iot',
                'connected'
            ],
            'lifestyle' => [
                'minimalistisch',
                'skandinavisch',
                'hygge',
                'wellness',
                'self-care'
            ]
        ];

        // Filter relevant trending terms based on product context
        $relevantTerms = [];
        foreach ($trendingTerms as $category => $terms) {
            // Simple relevance check based on product categories and properties
            if ($this->isRelevantTrendCategory($category, $analysis)) {
                $relevantTerms = array_merge($relevantTerms, array_slice($terms, 0, 2));
            }
        }

        return $relevantTerms;
    }

    private function getCurrentSeason(): string
    {
        $month = (int)date('n');

        if ($month >= 3 && $month <= 5) {
            return 'Frühling';
        } elseif ($month >= 6 && $month <= 8) {
            return 'Sommer';
        } elseif ($month >= 9 && $month <= 11) {
            return 'Herbst';
        } else {
            return 'Winter';
        }
    }

    private function isRelevantTrendCategory(string $category, array $analysis): bool
    {
        $productName = strtolower($analysis['content']['name'] ?? '');
        $categories = array_column($analysis['categories'], 'name');
        $categoriesText = strtolower(implode(' ', $categories));

        $relevanceMap = [
            'seasonal' => true, // Always relevant
            'sustainability' => stripos($productName . ' ' . $categoriesText, 'bio') !== false ||
                              stripos($productName . ' ' . $categoriesText, 'natur') !== false,
            'digital' => stripos($productName . ' ' . $categoriesText, 'elektronik') !== false ||
                        stripos($productName . ' ' . $categoriesText, 'smart') !== false,
            'lifestyle' => stripos($categoriesText, 'wohnen') !== false ||
                          stripos($categoriesText, 'lifestyle') !== false
        ];

        return $relevanceMap[$category] ?? false;
    }

    private function generateFallbackResearch(array $analysis): array
    {
        // Generate basic research data when web research fails
        return [
            'semantic_keywords' => $this->generateBasicKeywords($analysis),
            'competitor_insights' => [
                'common_title_patterns' => ['Brand + Product + Features'],
                'title_length_analysis' => ['optimal_range' => '45-60 characters']
            ],
            'market_context' => ['note' => 'Fallback data - web research unavailable'],
            'suggested_benefits' => $this->researchProductBenefits($analysis),
            'trending_terms' => ['neu', 'premium', 'qualität']
        ];
    }

    private function generateBasicKeywords(array $analysis): array
    {
        $keywords = [];

        // Extract from product name
        if (!empty($analysis['content']['name'])) {
            $keywords[] = $analysis['content']['name'] . ' kaufen';
            $keywords[] = $analysis['content']['name'] . ' online';
        }

        // Extract from manufacturer
        if (!empty($analysis['manufacturer']['name'])) {
            $keywords[] = $analysis['manufacturer']['name'] . ' shop';
        }

        return $keywords;
    }

    /**
     * Future implementation for real web scraping
     * This would require careful implementation to respect robots.txt and rate limits
     */
    private function performRealWebSearch(string $query): array
    {
        // TODO: Implement real web scraping
        // Options:
        // 1. Use SEO APIs like SEMrush, Ahrefs
        // 2. Use Google Custom Search API
        // 3. Implement careful web scraping with respect to terms of service

        return [];
    }
}