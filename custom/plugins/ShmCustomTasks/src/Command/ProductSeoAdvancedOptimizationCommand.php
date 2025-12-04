<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Command;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shm\ShmCustomTasks\Service\ChatGptService;
use Shm\ShmCustomTasks\Service\ProductAnalysisService;
use Shm\ShmCustomTasks\Service\WebResearchService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shm:product:seo-advanced-optimize',
    description: 'Advanced SEO optimization with web research, parent/variant analysis, and comprehensive content generation'
)]
class ProductSeoAdvancedOptimizationCommand extends Command
{
    private const DEFAULT_LANGUAGE_ID = '2fbb5fe2e29a4d70aa5854ce7ce3e20b'; // DE
    private const RATE_LIMIT_DELAY = 25; // seconds between requests
    private const BACKUP_DIR = 'var/product_seo_backups';

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $categoryRepository,
        private readonly Connection $connection,
        private readonly ProductAnalysisService $productAnalysisService,
        private readonly WebResearchService $webResearchService,
        private readonly ChatGptService $chatGptService,
        private readonly LoggerInterface $logger,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('language-id', 'l', InputOption::VALUE_OPTIONAL, 'Language ID for translations', self::DEFAULT_LANGUAGE_ID)
            ->addOption('product-numbers', 'p', InputOption::VALUE_OPTIONAL, 'Comma-separated list of product numbers')
            ->addOption('category-id', 'c', InputOption::VALUE_OPTIONAL, 'Category ID to process products from')
            ->addOption('optimization-type', 't', InputOption::VALUE_OPTIONAL, 'Type of optimization: all, title-only, meta-only', 'all')
            ->addOption('include-variants', null, InputOption::VALUE_NONE, 'Include variant products in optimization')
            ->addOption('parent-only', null, InputOption::VALUE_NONE, 'Only optimize parent products')
            ->addOption('variants-only', null, InputOption::VALUE_NONE, 'Only optimize variant products')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate optimization without making changes')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Number of products to process per batch', 5)
            ->addOption('skip-web-research', null, InputOption::VALUE_NONE, 'Skip web research phase (faster processing)')
            ->addOption('manual-review', 'm', InputOption::VALUE_NONE, 'Require manual confirmation for each optimization')
            ->addOption('backup-enabled', null, InputOption::VALUE_NONE, 'Create backup before optimization (default: true)')
            ->addOption('openai-api-key', null, InputOption::VALUE_OPTIONAL, 'OpenAI API Key for real ChatGPT integration')
            ->setHelp(<<<'EOF'
This command provides advanced SEO optimization with comprehensive analysis:

Features:
- Web research for semantic keywords and market context
- Separate optimization strategies for parent vs variant products
- Property-based content structuring
- SEO title and meta description generation
- Benefits and trending terms integration
- Comprehensive backup and rollback system

Examples:
  <info>%command.name% --product-numbers="SW10001,SW10002" --dry-run</info>
  <info>%command.name% --category-id="abc123" --include-variants</info>
  <info>%command.name% --parent-only --optimization-type="title-only"</info>
  <info>%command.name% --variants-only --manual-review</info>

Optimization Types:
- all: Complete optimization (title, meta-title, meta-description, keywords)
- title-only: Only optimize product title
- meta-only: Only optimize meta-title and meta-description

Product Types:
- Parent products: Structured data focus, variant foundation
- Variant products: Benefits-focused, conversion-optimized
- Standalone products: Complete SEO optimization
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();

        $languageId = $input->getOption('language-id');
        $productNumbers = $input->getOption('product-numbers');
        $categoryId = $input->getOption('category-id');
        $optimizationType = $input->getOption('optimization-type');
        $includeVariants = $input->getOption('include-variants');
        $parentOnly = $input->getOption('parent-only');
        $variantsOnly = $input->getOption('variants-only');
        $dryRun = $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');
        $skipWebResearch = $input->getOption('skip-web-research');
        $manualReview = $input->getOption('manual-review');
        $backupEnabled = !$input->getOption('backup-enabled') ? true : $input->getOption('backup-enabled');

        $io->title('Advanced Product SEO Optimization');
        $io->section(sprintf(
            'Language: %s | Type: %s | Batch: %d | Mode: %s',
            $languageId === self::DEFAULT_LANGUAGE_ID ? 'DE' : $languageId,
            $optimizationType,
            $batchSize,
            $dryRun ? 'DRY-RUN' : 'LIVE'
        ));

        try {
            // Phase 1: Product Discovery
            $io->section('Phase 1: Advanced Product Discovery');
            $products = $this->discoverProducts($productNumbers, $categoryId, $languageId, $includeVariants, $parentOnly, $variantsOnly, $io);

            if (empty($products)) {
                $io->warning('No products found for optimization');
                return Command::SUCCESS;
            }

            $io->info(sprintf('Found %d products for optimization', count($products)));
            $this->displayProductTypeAnalysis($products, $io);

            // Phase 2: Create backup if enabled
            $backupFile = null;
            if ($backupEnabled && !$dryRun) {
                $io->section('Phase 2: Creating Comprehensive Backup');
                $backupFile = $this->createAdvancedBackup($products, $languageId, $optimizationType, $io);
                $io->success(sprintf('Backup created: %s', $backupFile));
            }

            // Phase 3: Advanced Analysis and Optimization
            $io->section('Phase 3: Advanced SEO Analysis & Optimization');
            if (!$io->confirm('Proceed with advanced optimization?', !$dryRun)) {
                $io->info('Operation cancelled by user');
                return Command::SUCCESS;
            }

            $results = $this->performAdvancedOptimization(
                $products,
                $languageId,
                $optimizationType,
                $batchSize,
                $skipWebResearch,
                $dryRun,
                $manualReview,
                $context,
                $io
            );

            // Phase 4: Results Analysis
            $this->displayAdvancedResults($results, $io);

            $io->success(sprintf('Advanced optimization completed. Processed %d products.', count($products)));

            if ($backupFile) {
                $io->info(sprintf('Backup file: %s', $backupFile));
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Advanced product SEO optimization failed', [
                'error' => $e->getMessage(),
                'languageId' => $languageId,
                'optimizationType' => $optimizationType
            ]);

            $io->error(sprintf('Optimization failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function discoverProducts(?string $productNumbers, ?string $categoryId, string $languageId, bool $includeVariants, bool $parentOnly, bool $variantsOnly, SymfonyStyle $io): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('translations');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('manufacturer');
        $criteria->addAssociation('children'); // For parent products

        // Filter by product numbers if provided
        if ($productNumbers) {
            $numbers = array_map('trim', explode(',', $productNumbers));
            $criteria->addFilter(new EqualsAnyFilter('productNumber', $numbers));
            $io->info(sprintf('Filtering by product numbers: %s', implode(', ', $numbers)));
        }

        // Filter by category if provided
        if ($categoryId) {
            $criteria->addFilter(new EqualsFilter('categories.id', $categoryId));
            $io->info(sprintf('Filtering by category ID: %s', $categoryId));
        }

        // Filter by product type
        if ($parentOnly) {
            $criteria->addFilter(new EqualsFilter('parentId', null));
            $io->info('Filtering: Parent products only');
        } elseif ($variantsOnly) {
            // Only variants (products WITH parent)
            $criteria->addFilter(new EqualsFilter('parentId', null), 'NOT');
            $io->info('Filtering: Variant products only');
        } elseif (!$includeVariants) {
            // Only parent products (no variants)
            $criteria->addFilter(new EqualsFilter('parentId', null));
            $io->info('Filtering: Excluding variants (parent products only)');
        } else {
            // Include both parents AND variants
            $io->info('Including: Both parent products and variants');
        }

        // Only active products
        $criteria->addFilter(new EqualsFilter('active', true));

        $products = $this->productRepository->search($criteria, Context::createDefaultContext());

        $productData = [];
        $variantIds = [];

        // First, collect all main products
        foreach ($products->getEntities() as $product) {
            $translation = $product->getTranslations()->filterByProperty('languageId', $languageId)->first();
            if ($translation) {
                $isParent = $product->getParentId() === null && $product->getChildren() && $product->getChildren()->count() > 0;

                $productData[] = [
                    'id' => $product->getId(),
                    'productNumber' => $product->getProductNumber(),
                    'parentId' => $product->getParentId(),
                    'isParent' => $isParent,
                    'isVariant' => $product->getParentId() !== null,
                    'manufacturerId' => $product->getManufacturerId(),
                    'currentContent' => [
                        'name' => $translation->getName(),
                        'description' => $translation->getDescription(),
                        'shortDescription' => null,
                        'metaTitle' => $translation->getMetaTitle(),
                        'metaDescription' => $translation->getMetaDescription(),
                        'keywords' => $translation->getKeywords()
                    ]
                ];

                // If this is a parent and we want variants, collect variant IDs
                if ($includeVariants && $isParent && $product->getChildren()) {
                    foreach ($product->getChildren() as $child) {
                        $variantIds[] = $child->getId();
                    }
                }
            }
        }

        // If we need to include variants, load them separately
        if ($includeVariants && !empty($variantIds)) {
            $variantCriteria = new Criteria($variantIds);
            $variantCriteria->addAssociation('translations');
            $variantCriteria->addAssociation('properties.group');
            $variantCriteria->addAssociation('categories');
            $variantCriteria->addAssociation('manufacturer');

            $variants = $this->productRepository->search($variantCriteria, Context::createDefaultContext());

            foreach ($variants->getEntities() as $variant) {
                $variantTranslation = $variant->getTranslations()->filterByProperty('languageId', $languageId)->first();
                if ($variantTranslation) {
                    $productData[] = [
                        'id' => $variant->getId(),
                        'productNumber' => $variant->getProductNumber(),
                        'parentId' => $variant->getParentId(),
                        'isParent' => false,
                        'isVariant' => true,
                        'manufacturerId' => $variant->getManufacturerId(),
                        'currentContent' => [
                            'name' => $variantTranslation->getName(),
                            'description' => $variantTranslation->getDescription(),
                            'shortDescription' => null,
                            'metaTitle' => $variantTranslation->getMetaTitle(),
                            'metaDescription' => $variantTranslation->getMetaDescription(),
                            'keywords' => $variantTranslation->getKeywords()
                        ]
                    ];
                }
            }

            $io->info(sprintf('Loaded %d additional variants', count($variantIds)));
        }

        return $productData;
    }

    private function displayProductTypeAnalysis(array $products, SymfonyStyle $io): void
    {
        $parentCount = count(array_filter($products, fn($p) => $p['isParent']));
        $variantCount = count(array_filter($products, fn($p) => $p['isVariant']));
        $standaloneCount = count($products) - $parentCount - $variantCount;

        $io->definitionList(
            ['Parent Products' => $parentCount],
            ['Variant Products' => $variantCount],
            ['Standalone Products' => $standaloneCount],
            ['Total Products' => count($products)]
        );
    }

    private function createAdvancedBackup(array $products, string $languageId, string $optimizationType, SymfonyStyle $io): string
    {
        $backupDir = $this->projectDir . '/' . self::BACKUP_DIR;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'languageId' => $languageId,
            'optimizationType' => $optimizationType,
            'totalProducts' => count($products),
            'productTypes' => [
                'parents' => count(array_filter($products, fn($p) => $p['isParent'])),
                'variants' => count(array_filter($products, fn($p) => $p['isVariant'])),
                'standalone' => count(array_filter($products, fn($p) => !$p['isParent'] && !$p['isVariant']))
            ],
            'products' => []
        ];

        foreach ($products as $product) {
            $backupData['products'][] = [
                'id' => $product['id'],
                'productNumber' => $product['productNumber'],
                'type' => $product['isParent'] ? 'parent' : ($product['isVariant'] ? 'variant' : 'standalone'),
                'originalContent' => $product['currentContent']
            ];
        }

        $backupFile = $backupDir . '/advanced_seo_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));

        $this->logger->info('Advanced SEO backup created', [
            'backupFile' => $backupFile,
            'productCount' => count($products),
            'optimizationType' => $optimizationType
        ]);

        return $backupFile;
    }

    private function performAdvancedOptimization(array $products, string $languageId, string $optimizationType, int $batchSize, bool $skipWebResearch, bool $dryRun, bool $manualReview, Context $context, SymfonyStyle $io): array
    {
        $results = [
            'optimized' => 0,
            'skipped' => 0,
            'failed' => 0,
            'details' => [],
            'optimization_stats' => [
                'parent_products' => 0,
                'variant_products' => 0,
                'standalone_products' => 0,
                'web_research_performed' => 0,
                'content_types_optimized' => []
            ]
        ];

        $progressBar = new ProgressBar($io, count($products));
        $progressBar->start();

        $batches = array_chunk($products, $batchSize);
        $optimizationsForReview = [];

        foreach ($batches as $batchIndex => $batch) {
            $io->text(sprintf('Processing batch %d/%d', $batchIndex + 1, count($batches)));

            foreach ($batch as $product) {
                try {
                    // Phase 1: Comprehensive Product Analysis
                    $analysis = $this->productAnalysisService->analyzeProduct($product['id'], $languageId);

                    // Skip SEO optimization for parent products (only variants get SEO)
                    if ($product['isParent']) {
                        // Check if parent needs description
                        if (empty($analysis['content']['description'])) {
                            $this->generateParentDescription($product['id'], $analysis, $languageId, $context, $dryRun);
                            $results['details'][] = [
                                'productNumber' => $product['productNumber'],
                                'status' => 'description_added',
                                'type' => 'parent',
                                'reason' => 'Generated missing description for parent product'
                            ];
                        } else {
                            $results['skipped']++;
                            $results['details'][] = [
                                'productNumber' => $product['productNumber'],
                                'status' => 'skipped',
                                'type' => 'parent',
                                'reason' => 'Parent products get no SEO optimization, only variants'
                            ];
                        }
                        $progressBar->advance();
                        continue;
                    }

                    // Only process variants for SEO optimization
                    if (!$product['isVariant']) {
                        $results['skipped']++;
                        $results['details'][] = [
                            'productNumber' => $product['productNumber'],
                            'status' => 'skipped',
                            'reason' => 'Not a variant product - only variants get SEO optimization'
                        ];
                        $progressBar->advance();
                        continue;
                    }

                    // Phase 2: Web Research (only for variants)
                    $researchData = [];
                    if (!$skipWebResearch) {
                        $researchData = $this->webResearchService->researchProductContext($analysis);
                        $results['optimization_stats']['web_research_performed']++;
                    } else {
                        $researchData = ['suggested_benefits' => ['Premium Qualität'], 'semantic_keywords' => []];
                    }

                    // Phase 3: Advanced Optimization (only for variants)
                    $optimization = $this->chatGptService->optimizeProduct($analysis, $researchData, $optimizationType);

                    if (empty($optimization) || empty($optimization['title'])) {
                        $results['skipped']++;
                        $results['details'][] = [
                            'productNumber' => $product['productNumber'],
                            'status' => 'skipped',
                            'reason' => 'No optimization generated'
                        ];
                        $progressBar->advance();
                        continue;
                    }

                    // Store optimization for potential batch review
                    $optimization['productData'] = $product;

                    if ($manualReview) {
                        // Collect for batch review
                        $optimizationsForReview[] = $optimization;
                    } else {
                        // Apply immediately if no review needed
                        if (!$dryRun) {
                            $this->applyOptimization($product['id'], $optimization, $optimizationType, $languageId, $context);
                        }

                        // Track success
                        $results['optimized']++;
                        $this->updateOptimizationStats($results, $optimization);

                        $results['details'][] = [
                            'productNumber' => $product['productNumber'],
                            'status' => 'optimized',
                            'type' => $optimization['optimization_type'],
                            'originalContent' => $product['currentContent'],
                            'optimizedContent' => $optimization,
                            'improvements' => $this->calculateImprovements($product['currentContent'], $optimization)
                        ];
                    }

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['details'][] = [
                        'productNumber' => $product['productNumber'],
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];

                    $this->logger->error('Advanced product optimization failed', [
                        'productNumber' => $product['productNumber'],
                        'error' => $e->getMessage()
                    ]);
                }

                $progressBar->advance();

                // Rate limiting
                if (!$dryRun && !$skipWebResearch) {
                    sleep(self::RATE_LIMIT_DELAY);
                }
            }
        }

        $progressBar->finish();
        $io->newLine(2);

        // Handle batch review if enabled
        if ($manualReview && !empty($optimizationsForReview)) {
            $io->section('Batch Review - Variant Optimizations');
            $io->info(sprintf('Found %d variant optimizations for review', count($optimizationsForReview)));

            // Show summary of all optimizations
            $this->showBatchOptimizationSummary($optimizationsForReview, $io);

            if ($io->confirm('Apply all variant optimizations?', true)) {
                $io->text('Applying all approved optimizations...');
                $applyProgressBar = new ProgressBar($io, count($optimizationsForReview));
                $applyProgressBar->start();

                foreach ($optimizationsForReview as $optimization) {
                    $product = $optimization['productData'];

                    if (!$dryRun) {
                        $this->applyOptimization($product['id'], $optimization, $optimizationType, $languageId, $context);
                    }

                    $results['optimized']++;
                    $this->updateOptimizationStats($results, $optimization);

                    $results['details'][] = [
                        'productNumber' => $product['productNumber'],
                        'status' => 'optimized',
                        'type' => $optimization['optimization_type'],
                        'originalContent' => $product['currentContent'],
                        'optimizedContent' => $optimization,
                        'improvements' => $this->calculateImprovements($product['currentContent'], $optimization)
                    ];

                    $applyProgressBar->advance();
                }

                $applyProgressBar->finish();
                $io->newLine(2);
                $io->success(sprintf('Applied %d variant optimizations', count($optimizationsForReview)));
            } else {
                $results['skipped'] += count($optimizationsForReview);
                foreach ($optimizationsForReview as $optimization) {
                    $product = $optimization['productData'];
                    $results['details'][] = [
                        'productNumber' => $product['productNumber'],
                        'status' => 'skipped',
                        'reason' => 'Rejected in batch review'
                    ];
                }
                $io->info('All variant optimizations rejected by user');
            }
        }

        return $results;
    }

    private function generateParentDescription(string $productId, array $analysis, string $languageId, Context $context, bool $dryRun): void
    {
        // Generate description based on product analysis and web research
        $researchData = $this->webResearchService->researchProductContext($analysis);

        $prompt = $this->buildParentDescriptionPrompt($analysis, $researchData);
        $description = $this->generateDescriptionFromPrompt($prompt);

        if (!$dryRun && !empty($description)) {
            $updateData = [
                'id' => $productId,
                'translations' => [
                    $languageId => [
                        'description' => $description
                    ]
                ]
            ];

            $this->productRepository->update([$updateData], $context);

            $this->logger->info('Generated description for parent product', [
                'productId' => $productId,
                'descriptionLength' => strlen($description)
            ]);
        }
    }

    private function buildParentDescriptionPrompt(array $analysis, array $researchData): string
    {
        $productName = $analysis['content']['name'] ?? '';
        $manufacturer = $analysis['manufacturer']['name'] ?? '';
        $categories = implode(', ', array_column($analysis['categories'], 'name'));
        $properties = $this->formatPropertiesForDescription($analysis['properties']);
        $benefits = implode(', ', array_slice($researchData['suggested_benefits'] ?? [], 0, 5));

        return sprintf(
            "Erstelle eine professionelle Produktbeschreibung für ein E-Commerce Hauptprodukt:\n\n" .
            "Produktname: %s\n" .
            "Hersteller: %s\n" .
            "Kategorien: %s\n" .
            "Eigenschaften: %s\n" .
            "Vorteile: %s\n\n" .
            "Anforderungen:\n" .
            "- Genau 100 Wörter\n" .
            "- 3-4 aussagekräftige Bulletpoints\n" .
            "- Professioneller, verkaufsfördernder Ton\n" .
            "- Hervorhebung der wichtigsten Produktmerkmale\n" .
            "- Zielgruppe ansprechen\n" .
            "- Keine übertriebenen Werbeversprechen\n\n" .
            "Format:\n" .
            "Einleitungstext (2-3 Sätze)\n" .
            "• Bulletpoint 1\n" .
            "• Bulletpoint 2\n" .
            "• Bulletpoint 3\n" .
            "Abschlusstext (1-2 Sätze)",
            $productName,
            $manufacturer,
            $categories,
            $properties,
            $benefits
        );
    }

    private function formatPropertiesForDescription(array $properties): string
    {
        $formatted = [];
        foreach ($properties as $group) {
            $options = array_column($group['options'], 'name');
            $formatted[] = $group['group_name'] . ': ' . implode(', ', array_slice($options, 0, 3));
        }
        return implode(' | ', $formatted);
    }

    private function generateDescriptionFromPrompt(string $prompt): string
    {
        // Simulate AI description generation
        // TODO: Replace with actual ChatGPT call when available

        // Extract product info from prompt
        preg_match('/Produktname:\s*(.+)$/m', $prompt, $nameMatches);
        preg_match('/Hersteller:\s*(.+)$/m', $prompt, $brandMatches);
        preg_match('/Kategorien:\s*(.+)$/m', $prompt, $categoryMatches);

        $productName = trim($nameMatches[1] ?? 'Dieses Produkt');
        $brand = trim($brandMatches[1] ?? 'Unser Hersteller');
        $category = trim($categoryMatches[1] ?? 'hochwertige Produkte');

        // Generate intelligent description
        $description = sprintf(
            "%s von %s vereint erstklassige Qualität mit durchdachtem Design. Als hochwertiges Produkt in der Kategorie %s bietet es vielseitige Einsatzmöglichkeiten für anspruchsvolle Kunden.\n\n" .
            "• Premium Qualität und langlebige Verarbeitung\n" .
            "• Vielseitige Anwendungsmöglichkeiten für verschiedene Anforderungen\n" .
            "• Optimales Preis-Leistungs-Verhältnis\n" .
            "• Zuverlässiger Kundenservice und schnelle Lieferung\n\n" .
            "Entdecken Sie die verschiedenen Varianten und wählen Sie die perfekte Lösung für Ihre individuellen Bedürfnisse. Qualität, auf die Sie sich verlassen können.",
            $productName,
            $brand,
            $category
        );

        return $description;
    }

    private function showBatchOptimizationSummary(array $optimizations, SymfonyStyle $io): void
    {
        $io->text('Variant optimization summary:');

        $table = [];
        foreach ($optimizations as $optimization) {
            $product = $optimization['productData'];
            $table[] = [
                $product['productNumber'],
                substr($product['currentContent']['name'] ?? '', 0, 30) . '...',
                substr($optimization['title'] ?? '', 0, 30) . '...',
                isset($optimization['meta_description']) ? 'Yes' : 'No'
            ];
        }

        $io->table(['Product Number', 'Current Title', 'New Title', 'Meta Desc'], array_slice($table, 0, 10));

        if (count($optimizations) > 10) {
            $io->text(sprintf('... and %d more variants', count($optimizations) - 10));
        }

        $io->newLine();
        $io->text('<comment>This will optimize ALL variant products shown above.</comment>');
        $io->text('<comment>Parent products only get description updates if missing.</comment>');
    }

    private function showOptimizationPreview(array $product, array $optimization, SymfonyStyle $io): bool
    {
        $io->section(sprintf('Optimization Preview: %s', $product['productNumber']));

        $table = [
            ['Field', 'Original', 'Optimized'],
            ['Title', substr($product['currentContent']['name'] ?? '', 0, 50), substr($optimization['title'] ?? '', 0, 50)],
            ['Meta Title', substr($product['currentContent']['metaTitle'] ?? '', 0, 50), substr($optimization['meta_title'] ?? '', 0, 50)],
            ['Meta Desc', substr($product['currentContent']['metaDescription'] ?? '', 0, 50), substr($optimization['meta_description'] ?? '', 0, 50)]
        ];

        $io->table([], $table);

        return $io->confirm('Apply this optimization?');
    }

    private function applyOptimization(string $productId, array $optimization, string $optimizationType, string $languageId, Context $context): void
    {
        $updateData = [
            'id' => $productId,
            'translations' => [
                $languageId => []
            ]
        ];

        // Apply optimization based on type
        switch ($optimizationType) {
            case 'title-only':
                if (!empty($optimization['title'])) {
                    $updateData['translations'][$languageId]['name'] = $optimization['title'];
                }
                break;

            case 'meta-only':
                if (!empty($optimization['meta_title'])) {
                    $updateData['translations'][$languageId]['metaTitle'] = $optimization['meta_title'];
                }
                if (!empty($optimization['meta_description'])) {
                    $updateData['translations'][$languageId]['metaDescription'] = $optimization['meta_description'];
                }
                break;

            case 'all':
            default:
                if (!empty($optimization['title'])) {
                    $updateData['translations'][$languageId]['name'] = $optimization['title'];
                }
                if (!empty($optimization['meta_title'])) {
                    $updateData['translations'][$languageId]['metaTitle'] = $optimization['meta_title'];
                }
                if (!empty($optimization['meta_description'])) {
                    $updateData['translations'][$languageId]['metaDescription'] = $optimization['meta_description'];
                }
                // Note: Shopware 6 doesn't have shortDescription in ProductTranslation
                // if (!empty($optimization['short_description'])) {
                //     $updateData['translations'][$languageId]['shortDescription'] = $optimization['short_description'];
                // }
                if (!empty($optimization['keywords'])) {
                    $updateData['translations'][$languageId]['keywords'] = $optimization['keywords'];
                }
                break;
        }

        $this->productRepository->update([$updateData], $context);

        $this->logger->info('Advanced product optimization applied', [
            'productId' => $productId,
            'optimizationType' => $optimizationType,
            'optimizedFields' => array_keys($updateData['translations'][$languageId])
        ]);
    }

    private function updateOptimizationStats(array &$results, array $optimization): void
    {
        $type = $optimization['optimization_type'] ?? 'unknown';

        switch ($type) {
            case 'parent':
                $results['optimization_stats']['parent_products']++;
                break;
            case 'variant':
                $results['optimization_stats']['variant_products']++;
                break;
            default:
                $results['optimization_stats']['standalone_products']++;
        }

        // Track content types optimized
        foreach (['title', 'meta_title', 'meta_description', 'short_description', 'keywords'] as $field) {
            if (!empty($optimization[$field])) {
                if (!isset($results['optimization_stats']['content_types_optimized'][$field])) {
                    $results['optimization_stats']['content_types_optimized'][$field] = 0;
                }
                $results['optimization_stats']['content_types_optimized'][$field]++;
            }
        }
    }

    private function calculateImprovements(array $original, array $optimized): array
    {
        $improvements = [];

        // Title length improvement
        $originalTitleLength = strlen($original['name'] ?? '');
        $optimizedTitleLength = strlen($optimized['title'] ?? '');
        if ($originalTitleLength !== $optimizedTitleLength) {
            $improvements['title_length'] = [
                'from' => $originalTitleLength,
                'to' => $optimizedTitleLength,
                'change' => $optimizedTitleLength - $originalTitleLength
            ];
        }

        // Meta description added/improved
        if (empty($original['metaDescription']) && !empty($optimized['meta_description'])) {
            $improvements['meta_description'] = 'added';
        } elseif (!empty($original['metaDescription']) && !empty($optimized['meta_description'])) {
            $improvements['meta_description'] = 'improved';
        }

        // Keywords added/improved
        if (empty($original['keywords']) && !empty($optimized['keywords'])) {
            $improvements['keywords'] = 'added';
        } elseif (!empty($original['keywords']) && !empty($optimized['keywords'])) {
            $improvements['keywords'] = 'improved';
        }

        return $improvements;
    }

    private function displayAdvancedResults(array $results, SymfonyStyle $io): void
    {
        $io->section('Advanced Optimization Results');

        $io->definitionList(
            ['Total Optimized' => $results['optimized']],
            ['Skipped' => $results['skipped']],
            ['Failed' => $results['failed']]
        );

        $io->section('Optimization Statistics');
        $stats = $results['optimization_stats'];
        $io->definitionList(
            ['Parent Products' => $stats['parent_products']],
            ['Variant Products' => $stats['variant_products']],
            ['Standalone Products' => $stats['standalone_products']],
            ['Web Research Performed' => $stats['web_research_performed']]
        );

        if (!empty($stats['content_types_optimized'])) {
            $io->section('Content Types Optimized');
            foreach ($stats['content_types_optimized'] as $type => $count) {
                $io->text(sprintf('  %s: %d products', ucfirst(str_replace('_', ' ', $type)), $count));
            }
        }

        // Show sample optimizations
        $optimizedProducts = array_filter($results['details'], fn($d) => $d['status'] === 'optimized');
        if (!empty($optimizedProducts)) {
            $sampleCount = min(3, count($optimizedProducts));
            $io->section(sprintf('Sample Optimizations (first %d)', $sampleCount));

            foreach (array_slice($optimizedProducts, 0, $sampleCount) as $detail) {
                $io->text(sprintf('<info>%s</info> (%s)', $detail['productNumber'], $detail['type']));
                $io->text(sprintf('  Original: %s', substr($detail['originalContent']['name'] ?? '', 0, 60)));
                $io->text(sprintf('  Optimized: %s', substr($detail['optimizedContent']['title'] ?? '', 0, 60)));
                $io->newLine();
            }
        }
    }
}