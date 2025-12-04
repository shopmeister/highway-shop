<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ChatGptService
{
    private const CHATGPT_URL = 'https://chatgpt.com/';
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 5; // seconds

    private ?string $openAiApiKey;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly SystemConfigService $systemConfigService
    ) {
        // Get API key from system configuration
        $this->openAiApiKey = $this->systemConfigService->get('ShmCustomTasks.config.openaiApiKey', null);
    }

    public function optimizeProduct(array $analysis, array $researchData, string $optimizationType = 'title'): array
    {
        $this->logger->info('Starting comprehensive ChatGPT product optimization', [
            'productId' => $analysis['basic']['id'],
            'optimizationType' => $optimizationType,
            'isVariant' => $analysis['basic']['isVariant'],
            'isParent' => $analysis['basic']['isParent']
        ]);

        $results = [];

        try {
            // Determine optimization strategy
            if ($analysis['basic']['isVariant']) {
                $results = $this->optimizeVariantProduct($analysis, $researchData);
            } elseif ($analysis['basic']['isParent']) {
                $results = $this->optimizeParentProduct($analysis, $researchData);
            } else {
                $results = $this->optimizeStandaloneProduct($analysis, $researchData);
            }

            $this->logger->info('Product optimization completed', [
                'productId' => $analysis['basic']['id'],
                'optimizationsGenerated' => count($results)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Product optimization failed', [
                'productId' => $analysis['basic']['id'],
                'error' => $e->getMessage()
            ]);
            $results = $this->generateFallbackOptimization($analysis);
        }

        return $results;
    }

    public function optimizeTitle(string $prompt): ?string
    {
        $this->logger->info('Starting legacy ChatGPT title optimization', [
            'promptLength' => strlen($prompt)
        ]);

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $result = $this->sendPromptToChatGPT($prompt);

                if ($result) {
                    $this->logger->info('ChatGPT optimization successful', [
                        'attempt' => $attempt,
                        'resultLength' => strlen($result)
                    ]);
                    return $result;
                }

            } catch (\Exception $e) {
                $this->logger->warning('ChatGPT request failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt < self::MAX_RETRIES) {
                    sleep(self::RETRY_DELAY * $attempt);
                }
            }
        }

        $this->logger->error('All ChatGPT optimization attempts failed');
        return null;
    }

    private function sendPromptToChatGPT(string $prompt): ?string
    {
        // Since we can't use the official API, we'll implement a fallback approach
        // This could be extended to use browser automation tools like Selenium or Puppeteer

        $this->logger->info('Simulating ChatGPT optimization (replace with actual implementation)');

        // For demonstration, we'll implement a basic SEO optimization algorithm
        return $this->simulateOptimization($prompt);
    }

    private function simulateOptimization(string $prompt): ?string
    {
        // Extract the current title from the prompt
        if (!preg_match('/Aktueller Titel:\s*(.+)$/m', $prompt, $matches)) {
            return null;
        }

        $currentTitle = trim($matches[1]);

        // Extract product properties and categories
        $properties = $this->extractFromPrompt($prompt, 'Eigenschaften:');
        $categories = $this->extractFromPrompt($prompt, 'Kategorien:');
        $productNumber = $this->extractFromPrompt($prompt, 'Produktnummer:');

        // Basic SEO optimization rules
        $optimizedTitle = $this->applySeoOptimization($currentTitle, $properties, $categories, $productNumber);

        $this->logger->debug('Title optimization simulation', [
            'original' => $currentTitle,
            'optimized' => $optimizedTitle,
            'properties' => $properties,
            'categories' => $categories
        ]);

        return $optimizedTitle;
    }

    private function extractFromPrompt(string $prompt, string $field): string
    {
        if (preg_match("/{$field}\s*(.+)$/m", $prompt, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    private function applySeoOptimization(string $title, string $properties, string $categories, string $productNumber): string
    {
        // Step 1: Clean up title
        $optimized = $this->cleanTitle($title);

        // Step 2: Add key properties if missing
        $optimized = $this->addMissingKeywords($optimized, $properties);

        // Step 3: Optimize for length (max 60 chars)
        $optimized = $this->optimizeLength($optimized, 60);

        // Step 4: Improve readability
        $optimized = $this->improveReadability($optimized);

        return $optimized;
    }

    private function cleanTitle(string $title): string
    {
        // Remove excessive whitespace
        $title = preg_replace('/\s+/', ' ', $title);

        // Remove redundant words
        $redundantWords = ['Artikel', 'Produkt', 'Item', 'Art.', 'Nr.'];
        foreach ($redundantWords as $word) {
            $title = str_ireplace($word, '', $title);
        }

        return trim($title);
    }

    private function cleanCorruptedTitle(string $title): string
    {
        // Remove corrupted patterns from previous optimizations
        $patterns = [
            '/^Unbekannt\s+/',                    // "Unbekannt " at start
            '/\bUnbekannt\s+Parent-Produkt:\s*/', // "Unbekannt Parent-Produkt: "
            '/\bParent-Produkt:\s*/',             // "Parent-Produkt: "
            '/\s*-\s*Premium\s*$/',               // " - Premium" at end
            '/\s*-\s*Premium\s+Qualität\s*$/',   // " - Premium Qualität" at end
        ];

        foreach ($patterns as $pattern) {
            $title = preg_replace($pattern, '', $title);
        }

        return trim($title);
    }

    private function addMissingKeywords(string $title, string $properties): string
    {
        if (empty($properties)) {
            return $title;
        }

        $propertyList = array_map('trim', explode(',', $properties));
        $importantProperties = ['Größe', 'Farbe', 'Material', 'Marke', 'Modell'];

        foreach ($importantProperties as $important) {
            foreach ($propertyList as $property) {
                if (stripos($property, $important) !== false && stripos($title, $property) === false) {
                    // Check if we have space to add it
                    $potential = $title . ' ' . $property;
                    if (strlen($potential) <= 55) { // Leave room for final optimization
                        $title = $potential;
                        break;
                    }
                }
            }
        }

        return $title;
    }

    private function optimizeLength(string $title, int $maxLength): string
    {
        if (strlen($title) <= $maxLength) {
            return $title;
        }

        // Try to trim less important words first
        $lessImportant = ['und', 'für', 'mit', 'aus', 'in', 'auf', 'bei', 'von', 'zu', 'an'];

        foreach ($lessImportant as $word) {
            if (strlen($title) <= $maxLength) {
                break;
            }
            $title = preg_replace('/\s+' . preg_quote($word) . '\s+/i', ' ', $title);
            $title = trim($title);
        }

        // If still too long, truncate at word boundary
        if (strlen($title) > $maxLength) {
            $title = substr($title, 0, $maxLength);
            $lastSpace = strrpos($title, ' ');
            if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
                $title = substr($title, 0, $lastSpace);
            }
        }

        return trim($title);
    }

    private function improveReadability(string $title): string
    {
        // Capitalize first letter of each word (proper case)
        $title = ucwords(strtolower($title));

        // Fix common issues
        $title = str_replace(' Und ', ' und ', $title);
        $title = str_replace(' Mit ', ' mit ', $title);
        $title = str_replace(' Für ', ' für ', $title);

        // Remove double spaces
        $title = preg_replace('/\s+/', ' ', $title);

        return trim($title);
    }

    private function callRealChatGPT(string $prompt): ?string
    {
        if (!$this->openAiApiKey) {
            return null;
        }

        // Check if real ChatGPT is enabled
        $enableRealChatGpt = $this->systemConfigService->get('ShmCustomTasks.config.enableRealChatgpt', null) ?? false;
        if (!$enableRealChatGpt) {
            $this->logger->info('Real ChatGPT is disabled in configuration, using simulation');
            return null;
        }

        try {
            $this->logger->info('Calling real ChatGPT API');

            // Get configuration values
            $model = $this->systemConfigService->get('ShmCustomTasks.config.chatgptModel', null) ?? 'gpt-3.5-turbo';
            $maxTokens = $this->systemConfigService->get('ShmCustomTasks.config.maxTokens', null) ?? 300;
            $temperature = $this->systemConfigService->get('ShmCustomTasks.config.temperature', null) ?? 0.7;

            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Du bist ein professioneller SEO-Experte für E-Commerce mit Fokus auf deutsche Online-Shops. Antworte präzise im gewünschten Format.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => (int)$maxTokens,
                    'temperature' => (float)$temperature,
                    'top_p' => 0.9,
                ]
            ]);

            $data = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if ($content) {
                $this->logger->info('ChatGPT API call successful', [
                    'model' => $model,
                    'maxTokens' => $maxTokens,
                    'temperature' => $temperature,
                    'promptLength' => strlen($prompt),
                    'responseLength' => strlen($content),
                    'tokensUsed' => $data['usage']['total_tokens'] ?? 'unknown'
                ]);
                return $content;
            }

            return null;

        } catch (TransportExceptionInterface $e) {
            $this->logger->error('OpenAI API request failed', ['error' => $e->getMessage()]);
            return null;
        } catch (\Exception $e) {
            $this->logger->error('ChatGPT API call failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function optimizeParentProduct(array $analysis, array $researchData): array
    {
        $this->logger->debug('Optimizing parent product', ['productId' => $analysis['basic']['id']]);

        $prompt = $this->buildParentProductPrompt($analysis, $researchData);
        $optimization = $this->callAdvancedChatGPT($prompt);

        return [
            'title' => $optimization['title'] ?? $this->optimizeParentTitle($analysis),
            'meta_title' => $optimization['meta_title'] ?? $this->generateMetaTitle($analysis, 'parent'),
            'meta_description' => $optimization['meta_description'] ?? $this->generateMetaDescription($analysis, $researchData, 'parent'),
            'short_description' => $optimization['short_description'] ?? $this->generateShortDescription($analysis, 'parent'),
            'keywords' => $optimization['keywords'] ?? $this->generateKeywords($analysis, $researchData),
            'optimization_type' => 'parent'
        ];
    }

    private function optimizeVariantProduct(array $analysis, array $researchData): array
    {
        $this->logger->info('Optimizing variant product', [
            'productId' => $analysis['basic']['id'],
            'originalTitle' => $analysis['content']['name'] ?? 'none',
            'manufacturerName' => $analysis['manufacturer']['name'] ?? 'none'
        ]);

        $prompt = $this->buildVariantProductPrompt($analysis, $researchData);
        $optimization = $this->callAdvancedChatGPT($prompt);

        return [
            'title' => $optimization['title'] ?? $this->optimizeVariantTitle($analysis),
            'meta_title' => $optimization['meta_title'] ?? $this->generateMetaTitle($analysis, 'variant'),
            'meta_description' => $optimization['meta_description'] ?? $this->generateMetaDescription($analysis, $researchData, 'variant'),
            'short_description' => $optimization['short_description'] ?? $this->generateShortDescription($analysis, 'variant'),
            'keywords' => $optimization['keywords'] ?? $this->generateKeywords($analysis, $researchData),
            'optimization_type' => 'variant'
        ];
    }

    private function optimizeStandaloneProduct(array $analysis, array $researchData): array
    {
        $this->logger->debug('Optimizing standalone product', ['productId' => $analysis['basic']['id']]);

        $prompt = $this->buildStandaloneProductPrompt($analysis, $researchData);
        $optimization = $this->callAdvancedChatGPT($prompt);

        return [
            'title' => $optimization['title'] ?? $this->optimizeStandaloneTitle($analysis),
            'meta_title' => $optimization['meta_title'] ?? $this->generateMetaTitle($analysis, 'standalone'),
            'meta_description' => $optimization['meta_description'] ?? $this->generateMetaDescription($analysis, $researchData, 'standalone'),
            'short_description' => $optimization['short_description'] ?? $this->generateShortDescription($analysis, 'standalone'),
            'keywords' => $optimization['keywords'] ?? $this->generateKeywords($analysis, $researchData),
            'optimization_type' => 'standalone'
        ];
    }

    private function buildParentProductPrompt(array $analysis, array $researchData): string
    {
        $properties = $this->formatPropertiesForPrompt($analysis['properties']);
        $categories = implode(', ', array_column($analysis['categories'], 'name'));
        $benefits = implode(', ', $researchData['suggested_benefits'] ?? []);
        $semanticKeywords = implode(', ', array_slice($researchData['semantic_keywords'] ?? [], 0, 10));

        return sprintf(
            "Du bist ein SEO-Experte für E-Commerce. Optimiere dieses PARENT-PRODUKT für bessere Suchmaschinenrankings:\n\n" .
            "=== PRODUKT-DATEN ===\n" .
            "Aktueller Titel: %s\n" .
            "Produktnummer: %s\n" .
            "Marke: %s\n" .
            "Eigenschaften: %s\n" .
            "Kategorien: %s\n" .
            "Beschreibung: %s\n" .
            "Hat Varianten: JA (%d Varianten)\n\n" .
            "=== SEO-KONTEXT ===\n" .
            "Semantische Keywords: %s\n" .
            "Vorteile/Benefits: %s\n" .
            "Trending Terms: %s\n\n" .
            "=== ANFORDERUNGEN ===\n" .
            "Als PARENT-PRODUKT soll der Titel:\n" .
            "- Semantisch reichhaltig sein (Frontend, lesbar, mit Haupt-Keywords)\n" .
            "- Klar strukturiert die Produktdaten enthalten\n" .
            "- Marke, Produkttyp und Hauptkategorie erwähnen\n" .
            "- 45-60 Zeichen lang sein\n" .
            "- Suchmaschinenoptimiert mit relevanten Keywords\n" .
            "- NICHT spezifische Variant-Eigenschaften erwähnen\n" .
            "- Als Grundlage für Varianten dienen\n" .
            "- Keyword-Stuffing vermeiden, trotzdem leserfreundlich\n\n" .
            "Erstelle:\n" .
            "1. TITLE: Optimierter Produkttitel\n" .
            "2. META_TITLE: SEO-Title (max. 60 Zeichen)\n" .
            "3. META_DESCRIPTION: SEO-Description (max. 155 Zeichen)\n" .
            "4. SHORT_DESCRIPTION: Kurzbeschreibung (2-3 Sätze)\n" .
            "5. KEYWORDS: Kommagetrennte SEO-Keywords\n",
            $analysis['content']['name'] ?? '',
            $analysis['basic']['productNumber'] ?? '',
            $analysis['manufacturer']['name'] ?? '',
            $properties,
            $categories,
            substr($analysis['content']['description'] ?? '', 0, 200),
            count($analysis['variants']),
            $semanticKeywords,
            $benefits,
            implode(', ', array_slice($researchData['trending_terms'] ?? [], 0, 5))
        );
    }

    private function buildVariantProductPrompt(array $analysis, array $researchData): string
    {
        $properties = $this->formatPropertiesForPrompt($analysis['properties']);
        $categories = implode(', ', array_column($analysis['categories'], 'name'));
        $benefits = implode(', ', $researchData['suggested_benefits'] ?? []);
        $semanticKeywords = implode(', ', array_slice($researchData['semantic_keywords'] ?? [], 0, 10));
        $parentTitle = $analysis['parent']['name'] ?? '';

        return sprintf(
            "Du bist ein SEO-Experte für E-Commerce. Optimiere diese PRODUKT-VARIANTE für bessere Shop-Performance:\n\n" .
            "=== VARIANTEN-DATEN ===\n" .
            "Aktueller Titel: %s\n" .
            "Parent-Produkt: %s\n" .
            "Produktnummer: %s\n" .
            "Marke: %s\n" .
            "Spezifische Eigenschaften: %s\n" .
            "Kategorien: %s\n" .
            "Beschreibung: %s\n\n" .
            "=== SEO-KONTEXT ===\n" .
            "Semantische Keywords: %s\n" .
            "Vorteile/Benefits: %s\n" .
            "Trending Terms: %s\n\n" .
            "=== ANFORDERUNGEN ===\n" .
            "Als PRODUKTVARIANTE soll der Titel:\n" .
            "- Semantisch reichhaltig sein (Frontend, lesbar, mit Haupt-Keywords)\n" .
            "- Für den Shop optimiert mit Benefits und Vorteilen\n" .
            "- Marke und spezifische Property-Infos enthalten\n" .
            "- 50-70 Zeichen lang sein\n" .
            "- Kaufanreize und Verkaufsargumente enthalten\n" .
            "- Variant-spezifische Eigenschaften hervorheben\n" .
            "- Emotional ansprechend und verkaufsfördernd\n" .
            "- Keyword-Stuffing vermeiden, trotzdem leserfreundlich\n" .
            "- Keine Call-to-Actions in Produkttiteln\n\n" .
            "Erstelle:\n" .
            "1. TITLE: Shop-optimierter Varianten-Titel\n" .
            "2. META_TITLE: SEO-Title (max. 60 Zeichen)\n" .
            "3. META_DESCRIPTION: Verkaufsfördernde Description (max. 155 Zeichen)\n" .
            "4. SHORT_DESCRIPTION: Benefit-fokussierte Kurzbeschreibung\n" .
            "5. KEYWORDS: Variant-spezifische SEO-Keywords\n",
            $analysis['content']['name'] ?? '',
            $parentTitle,
            $analysis['basic']['productNumber'] ?? '',
            $analysis['manufacturer']['name'] ?? '',
            $properties,
            $categories,
            substr($analysis['content']['description'] ?? '', 0, 200),
            $semanticKeywords,
            $benefits,
            implode(', ', array_slice($researchData['trending_terms'] ?? [], 0, 5))
        );
    }

    private function buildStandaloneProductPrompt(array $analysis, array $researchData): string
    {
        $properties = $this->formatPropertiesForPrompt($analysis['properties']);
        $categories = implode(', ', array_column($analysis['categories'], 'name'));
        $benefits = implode(', ', $researchData['suggested_benefits'] ?? []);
        $semanticKeywords = implode(', ', array_slice($researchData['semantic_keywords'] ?? [], 0, 10));

        return sprintf(
            "Du bist ein SEO-Experte für E-Commerce. Optimiere dieses STANDALONE-PRODUKT für maximale Sichtbarkeit:\n\n" .
            "=== PRODUKT-DATEN ===\n" .
            "Aktueller Titel: %s\n" .
            "Produktnummer: %s\n" .
            "Marke: %s\n" .
            "Eigenschaften: %s\n" .
            "Kategorien: %s\n" .
            "Beschreibung: %s\n\n" .
            "=== SEO-KONTEXT ===\n" .
            "Semantische Keywords: %s\n" .
            "Vorteile/Benefits: %s\n" .
            "Trending Terms: %s\n\n" .
            "=== ANFORDERUNGEN ===\n" .
            "Als STANDALONE-PRODUKT soll der Titel:\n" .
            "- Semantisch reichhaltig sein (Frontend, lesbar, mit Haupt-Keywords)\n" .
            "- Optimal für Suchmaschinen und Shop optimiert\n" .
            "- Marke, Produkttyp und Hauptmerkmale enthalten\n" .
            "- 45-65 Zeichen lang sein\n" .
            "- Sowohl SEO- als auch Conversion-optimiert\n" .
            "- Alle wichtigen Keywords integrieren\n" .
            "- Einzigartig und differenzierend\n" .
            "- Keyword-Stuffing vermeiden, trotzdem leserfreundlich\n" .
            "- Keine Call-to-Actions in Produkttiteln\n\n" .
            "Erstelle:\n" .
            "1. TITLE: Volloptimierter Produkttitel\n" .
            "2. META_TITLE: SEO-Title (max. 60 Zeichen)\n" .
            "3. META_DESCRIPTION: Umfassende Description (max. 155 Zeichen)\n" .
            "4. SHORT_DESCRIPTION: Überzeugende Kurzbeschreibung\n" .
            "5. KEYWORDS: Vollständige SEO-Keywords\n",
            $analysis['content']['name'] ?? '',
            $analysis['basic']['productNumber'] ?? '',
            $analysis['manufacturer']['name'] ?? '',
            $properties,
            $categories,
            substr($analysis['content']['description'] ?? '', 0, 200),
            $semanticKeywords,
            $benefits,
            implode(', ', array_slice($researchData['trending_terms'] ?? [], 0, 5))
        );
    }

    private function formatPropertiesForPrompt(array $properties): string
    {
        $formatted = [];
        foreach ($properties as $group) {
            $options = array_column($group['options'], 'name');
            $formatted[] = $group['group_name'] . ': ' . implode(', ', $options);
        }
        return implode(' | ', $formatted);
    }

    private function callAdvancedChatGPT(string $prompt): array
    {
        // Try real ChatGPT API first, fallback to simulation
        if ($this->openAiApiKey) {
            $realResponse = $this->callRealChatGPT($prompt);
            if ($realResponse) {
                return $this->parseAdvancedResponse($realResponse);
            }
        }

        // Fallback to simulation
        $this->logger->info('Using ChatGPT simulation (no API key available)');
        return $this->parseAdvancedResponse($this->simulateAdvancedOptimization($prompt));
    }

    private function simulateAdvancedOptimization(string $prompt): string
    {
        // Extract product type and properties from prompt
        $isVariant = strpos($prompt, 'PRODUKT-VARIANTE') !== false;
        $isParent = strpos($prompt, 'PARENT-PRODUKT') !== false;

        // Extract current title
        preg_match('/Aktueller Titel:\s*(.+)$/m', $prompt, $titleMatches);
        $currentTitle = trim($titleMatches[1] ?? '');

        // Extract brand
        preg_match('/Marke:\s*(.+)$/m', $prompt, $brandMatches);
        $brand = trim($brandMatches[1] ?? '');

        // Extract properties
        preg_match('/Eigenschaften:\s*(.+)$/m', $prompt, $propMatches);
        $properties = trim($propMatches[1] ?? '');

        // Generate optimized content based on type
        if ($isVariant) {
            return $this->generateVariantOptimization($currentTitle, $brand, $properties);
        } elseif ($isParent) {
            return $this->generateParentOptimization($currentTitle, $brand, $properties);
        } else {
            return $this->generateStandaloneOptimization($currentTitle, $brand, $properties);
        }
    }

    private function generateParentOptimization(string $title, string $brand, string $properties): string
    {
        $optimizedTitle = $this->optimizeParentTitle(['content' => ['name' => $title], 'manufacturer' => ['name' => $brand]]);

        return sprintf(
            "TITLE: %s\n" .
            "META_TITLE: %s | Premium Qualität Online\n" .
            "META_DESCRIPTION: %s - Hochwertige Qualität ✓ Schnelle Lieferung ✓ 30 Tage Rückgabe ✓ Jetzt online bestellen!\n" .
            "SHORT_DESCRIPTION: Entdecken Sie %s in verschiedenen Ausführungen. Premium Qualität von %s mit ausgezeichnetem Kundenservice.\n" .
            "KEYWORDS: %s, %s online kaufen, %s shop, premium qualität",
            $optimizedTitle,
            substr($optimizedTitle, 0, 45),
            substr($optimizedTitle, 0, 100),
            strtolower($optimizedTitle),
            $brand,
            strtolower($optimizedTitle),
            strtolower($optimizedTitle),
            strtolower($brand)
        );
    }

    private function generateVariantOptimization(string $title, string $brand, string $properties): string
    {
        $optimizedTitle = $this->optimizeVariantTitle(['content' => ['name' => $title], 'manufacturer' => ['name' => $brand], 'properties' => []]);

        return sprintf(
            "TITLE: %s\n" .
            "META_TITLE: %s | %s\n" .
            "META_DESCRIPTION: %s von %s - Exklusive Auswahl ✓ Top Bewertungen ✓ Kostenloser Versand ab 50€ ✓ Bestellen Sie jetzt!\n" .
            "SHORT_DESCRIPTION: %s überzeugt mit erstklassiger Qualität und modernem Design. Perfekt für anspruchsvolle Kunden.\n" .
            "KEYWORDS: %s, %s kaufen, %s %s, sofort lieferbar",
            $optimizedTitle,
            substr($optimizedTitle, 0, 40),
            $brand,
            substr($optimizedTitle, 0, 90),
            $brand,
            $optimizedTitle,
            strtolower($optimizedTitle),
            strtolower($optimizedTitle),
            strtolower($brand),
            strtolower($optimizedTitle)
        );
    }

    private function generateStandaloneOptimization(string $title, string $brand, string $properties): string
    {
        $optimizedTitle = $this->optimizeStandaloneTitle(['content' => ['name' => $title], 'manufacturer' => ['name' => $brand]]);

        return sprintf(
            "TITLE: %s\n" .
            "META_TITLE: %s | %s Premium Shop\n" .
            "META_DESCRIPTION: %s von %s online kaufen. Beste Preise ✓ Premium Service ✓ Schnelle Lieferung ✓ Trusted Shop Garantie!\n" .
            "SHORT_DESCRIPTION: %s - Die perfekte Wahl für höchste Ansprüche. Erstklassige Verarbeitung und zeitloses Design.\n" .
            "KEYWORDS: %s, %s online, %s %s, premium qualität, beste preise",
            $optimizedTitle,
            substr($optimizedTitle, 0, 40),
            $brand,
            substr($optimizedTitle, 0, 95),
            $brand,
            $optimizedTitle,
            strtolower($optimizedTitle),
            strtolower($optimizedTitle),
            strtolower($brand),
            strtolower($optimizedTitle)
        );
    }

    private function parseAdvancedResponse(string $response): array
    {
        $parsed = [];

        // Parse structured response
        if (preg_match('/TITLE:\s*(.+)$/m', $response, $matches)) {
            $parsed['title'] = trim($matches[1]);
        }

        if (preg_match('/META_TITLE:\s*(.+)$/m', $response, $matches)) {
            $parsed['meta_title'] = trim($matches[1]);
        }

        if (preg_match('/META_DESCRIPTION:\s*(.+)$/m', $response, $matches)) {
            $parsed['meta_description'] = trim($matches[1]);
        }

        if (preg_match('/SHORT_DESCRIPTION:\s*(.+)$/m', $response, $matches)) {
            $parsed['short_description'] = trim($matches[1]);
        }

        if (preg_match('/KEYWORDS:\s*(.+)$/m', $response, $matches)) {
            $parsed['keywords'] = trim($matches[1]);
        }

        return $parsed;
    }

    private function optimizeParentTitle(array $analysis): string
    {
        $brand = $analysis['manufacturer']['name'] ?? '';
        $currentTitle = $analysis['content']['name'] ?? '';

        // Clean up corrupted titles from previous optimizations
        $currentTitle = $this->cleanCorruptedTitle($currentTitle);

        // Structure: Brand | Product Type | Category
        $optimized = trim($brand . ' ' . $currentTitle);
        return $this->optimizeLength(ucwords($optimized), 60);
    }

    private function optimizeVariantTitle(array $analysis): string
    {
        $brand = $analysis['manufacturer']['name'] ?? '';
        $currentTitle = $analysis['content']['name'] ?? '';

        // Clean up corrupted titles from previous optimizations
        $currentTitle = $this->cleanCorruptedTitle($currentTitle);

        // Structure: Product | Key Properties (NO Call-to-Action in title)
        $optimized = trim($currentTitle);
        if (!empty($brand) && stripos($optimized, $brand) === false) {
            $optimized = trim($brand . ' ' . $optimized);
        }
        return $this->optimizeLength($optimized, 65);
    }

    private function optimizeStandaloneTitle(array $analysis): string
    {
        $brand = $analysis['manufacturer']['name'] ?? '';
        $currentTitle = $analysis['content']['name'] ?? '';

        // Clean up corrupted titles from previous optimizations
        $currentTitle = $this->cleanCorruptedTitle($currentTitle);

        // Structure: Brand | Product | Key Benefit
        $optimized = trim($brand . ' ' . $currentTitle . ' Online');
        return $this->optimizeLength($optimized, 60);
    }

    private function generateMetaTitle(array $analysis, string $type): string
    {
        $title = $analysis['content']['name'] ?? '';
        $brand = $analysis['manufacturer']['name'] ?? '';

        switch ($type) {
            case 'variant':
                return $this->optimizeLength($title . ' | ' . $brand, 60);
            case 'parent':
                return $this->optimizeLength($brand . ' ' . $title . ' Shop', 60);
            default:
                return $this->optimizeLength($title . ' - ' . $brand . ' Online', 60);
        }
    }

    private function generateMetaDescription(array $analysis, array $researchData, string $type): string
    {
        $title = $analysis['content']['name'] ?? '';
        $brand = $analysis['manufacturer']['name'] ?? '';
        $benefits = array_slice($researchData['suggested_benefits'] ?? ['Premium Qualität'], 0, 2);

        $description = sprintf(
            '%s von %s online kaufen. %s ✓ Schnelle Lieferung ✓ Jetzt bestellen!',
            $title,
            $brand,
            implode(' ✓ ', $benefits)
        );

        return $this->optimizeLength($description, 160);
    }

    private function generateShortDescription(array $analysis, string $type): string
    {
        $title = $analysis['content']['name'] ?? '';
        $brand = $analysis['manufacturer']['name'] ?? '';

        return sprintf(
            '%s von %s überzeugt mit erstklassiger Qualität und modernem Design. Perfekt für anspruchsvolle Kunden.',
            $title,
            $brand
        );
    }

    private function generateKeywords(array $analysis, array $researchData): string
    {
        $keywords = [];

        // Base keywords
        if (!empty($analysis['content']['name'])) {
            $keywords[] = strtolower($analysis['content']['name']);
        }

        // Brand keywords
        if (!empty($analysis['manufacturer']['name'])) {
            $keywords[] = strtolower($analysis['manufacturer']['name']);
        }

        // Add research keywords
        $semanticKeywords = array_slice($researchData['semantic_keywords'] ?? [], 0, 5);
        $keywords = array_merge($keywords, $semanticKeywords);

        return implode(', ', array_unique($keywords));
    }

    private function generateFallbackOptimization(array $analysis): array
    {
        return [
            'title' => $this->optimizeStandaloneTitle($analysis),
            'meta_title' => $this->generateMetaTitle($analysis, 'standalone'),
            'meta_description' => $this->generateMetaDescription($analysis, ['suggested_benefits' => ['Premium Qualität']], 'standalone'),
            'short_description' => $this->generateShortDescription($analysis, 'standalone'),
            'keywords' => $this->generateKeywords($analysis, []),
            'optimization_type' => 'fallback'
        ];
    }
}