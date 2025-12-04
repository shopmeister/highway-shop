<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Command;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shm:property:consolidate',
    description: 'Consolidates fragmented property groups (Größe, Farbe) while preserving variant structures and foreign key integrity'
)]
class PropertyConsolidationCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $propertyGroupRepository,
        private readonly EntityRepository $propertyGroupOptionRepository,
        private readonly EntityRepository $productRepository,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('target-property', 't', InputOption::VALUE_REQUIRED, 'Target property name (e.g., "Größe" or "Farbe")')
            ->addOption('source-properties', 's', InputOption::VALUE_REQUIRED, 'Comma-separated list of source properties to consolidate')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate the consolidation without making changes')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Batch size for processing', 50)
            ->setHelp(<<<'EOF'
This command consolidates fragmented property groups into standardized properties while preserving:
- Product variant structures
- Property value assignments
- Foreign key integrity

Examples:
  <info>%command.name% --target-property="Größe" --source-properties="GRÖßE VARIANTEN,GRÖßE1,GRÖßEN" --dry-run</info>
  <info>%command.name% --target-property="Farbe" --source-properties="COLOR,FARBE,FARBEN" --batch-size=100</info>

The command will:
1. Analyze existing properties and their values
2. Deduplicate values (case-insensitive, normalized)
3. Migrate product assignments using Repository pattern (FK-safe)
4. Clean up obsolete property groups
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();
        
        $targetProperty = $input->getOption('target-property');
        $sourcePropertiesRaw = $input->getOption('source-properties');
        $dryRun = $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');

        if (!$targetProperty || !$sourcePropertiesRaw) {
            $io->error('Both --target-property and --source-properties are required');
            return Command::FAILURE;
        }

        $sourceProperties = array_map('trim', explode(',', $sourcePropertiesRaw));
        
        $io->title('Property Consolidation Command');
        $io->section(sprintf('Target: "%s" | Sources: %s | Mode: %s', 
            $targetProperty, 
            implode(', ', $sourceProperties),
            $dryRun ? 'DRY-RUN' : 'LIVE'
        ));

        try {
            // Phase 1: Analyze existing properties
            $io->section('Phase 1: Analyzing Properties');
            $analysisResult = $this->analyzeProperties($targetProperty, $sourceProperties, $io);
            
            if (!$analysisResult['canProceed']) {
                return Command::FAILURE;
            }

            // Phase 2: Value deduplication analysis
            $io->section('Phase 2: Value Deduplication Analysis');
            $deduplicationPlan = $this->planValueDeduplication(
                $analysisResult['targetGroup'], 
                $analysisResult['sourceGroups'], 
                $io
            );

            // Phase 3: Show execution plan
            $this->displayExecutionPlan($deduplicationPlan, $io);

            if (!$io->confirm('Proceed with the consolidation?', !$dryRun)) {
                $io->info('Operation cancelled by user');
                return Command::SUCCESS;
            }

            // Phase 4: Execute consolidation
            $io->section('Phase 4: Executing Consolidation');
            if ($dryRun) {
                $io->warning('DRY-RUN MODE: No changes will be made');
                $this->simulateConsolidation($deduplicationPlan, $io);
            } else {
                $this->executeConsolidationWithTransaction($deduplicationPlan, $context, $batchSize, $io);
            }

            $io->success('Property consolidation completed successfully');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Property consolidation failed', [
                'error' => $e->getMessage(),
                'target' => $targetProperty,
                'sources' => $sourceProperties
            ]);
            
            $io->error(sprintf('Consolidation failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function analyzeProperties(string $targetProperty, array $sourceProperties, SymfonyStyle $io): array
    {
        $allProperties = array_merge([$targetProperty], $sourceProperties);
        
        // Find existing property groups using DBAL (read-only) with translation table
        // WICHTIG: COUNT(DISTINCT pgo.id) verwenden, da der LEFT JOIN mit product_property
        // zu mehrfachen Zählungen der gleichen Option führen würde
        $existingGroups = $this->connection->fetchAllAssociative(
            'SELECT HEX(pg.id) as id, pgt.name,
                    COUNT(DISTINCT pgo.id) as option_count,
                    COUNT(DISTINCT pp.product_id) as product_count
             FROM property_group pg
             LEFT JOIN property_group_translation pgt ON pg.id = pgt.property_group_id AND pgt.language_id = UNHEX(:languageId)
             LEFT JOIN property_group_option pgo ON pg.id = pgo.property_group_id
             LEFT JOIN product_property pp ON pgo.id = pp.property_group_option_id
             WHERE pgt.name IN (:names)
             GROUP BY pg.id, pgt.name',
            [
                'names' => $allProperties,
                'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b' // Default DE language
            ],
            ['names' => Connection::PARAM_STR_ARRAY]
        );

        $foundGroups = array_column($existingGroups, 'name');
        $missingGroups = array_diff($allProperties, $foundGroups);

        // Display analysis results
        $table = [];
        foreach ($existingGroups as $group) {
            $table[] = [
                $group['name'],
                $group['id'],
                $group['option_count'],
                $group['product_count']
            ];
        }

        $io->table(['Property Name', 'ID', 'Options', 'Products'], $table);

        if ($missingGroups) {
            $io->warning(sprintf('Missing properties: %s', implode(', ', $missingGroups)));
        }

        // Validate target property exists
        $targetGroup = null;
        $sourceGroups = [];
        
        foreach ($existingGroups as $group) {
            if ($group['name'] === $targetProperty) {
                $targetGroup = $group;
            } elseif (in_array($group['name'], $sourceProperties)) {
                $sourceGroups[] = $group;
            }
        }

        if (!$targetGroup) {
            $io->error(sprintf('Target property "%s" not found', $targetProperty));
            return ['canProceed' => false];
        }

        if (empty($sourceGroups)) {
            $io->warning('No source properties found to consolidate');
            return ['canProceed' => false];
        }

        $io->info(sprintf(
            'Found target property "%s" (ID: %s) with %d options',
            $targetGroup['name'],
            $targetGroup['id'],
            $targetGroup['option_count']
        ));

        return [
            'canProceed' => true,
            'targetGroup' => $targetGroup,
            'sourceGroups' => $sourceGroups
        ];
    }

    private function planValueDeduplication(array $targetGroup, array $sourceGroups, SymfonyStyle $io): array
    {
        $sourceGroupIds = array_column($sourceGroups, 'id');
        
        // Get all existing options from target group with translations
        $targetOptions = $this->connection->fetchAllAssociative(
            'SELECT HEX(pgo.id) as id, pgot.name
             FROM property_group_option pgo
             LEFT JOIN property_group_option_translation pgot ON pgo.id = pgot.property_group_option_id AND pgot.language_id = UNHEX(:languageId)
             WHERE pgo.property_group_id = UNHEX(:id)',
            [
                'id' => $targetGroup['id'],
                'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b'
            ]
        );

        // Get all options from source groups with translations
        $sourceOptions = [];
        foreach ($sourceGroups as $sourceGroup) {
            $groupOptions = $this->connection->fetchAllAssociative(
                'SELECT HEX(pgo.id) as id, pgot.name, HEX(pgo.property_group_id) as property_group_id, :group_name as group_name,
                        COUNT(DISTINCT pp.product_id) as product_count
                 FROM property_group_option pgo
                 LEFT JOIN property_group_option_translation pgot ON pgo.id = pgot.property_group_option_id AND pgot.language_id = UNHEX(:languageId)
                 LEFT JOIN product_property pp ON pgo.id = pp.property_group_option_id
                 WHERE pgo.property_group_id = UNHEX(:group_id)
                 GROUP BY pgo.id, pgot.name, pgo.property_group_id',
                [
                    'group_id' => $sourceGroup['id'],
                    'group_name' => $sourceGroup['name'],
                    'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b'
                ]
            );
            $sourceOptions = array_merge($sourceOptions, $groupOptions);
        }

        // Build normalization map
        $targetOptionsNormalized = [];
        foreach ($targetOptions as $option) {
            $normalized = $this->normalizeValue($option['name']);
            $targetOptionsNormalized[$normalized] = $option;
        }

        // Plan migration
        $migrationPlan = [
            'targetGroup' => $targetGroup,
            'sourceGroups' => $sourceGroups,
            'migrations' => [],
            'newOptions' => [],
            'statistics' => [
                'totalSourceOptions' => count($sourceOptions),
                'duplicatesFound' => 0,
                'newOptionsToCreate' => 0,
                'productsAffected' => 0
            ]
        ];

        foreach ($sourceOptions as $sourceOption) {
            $normalized = $this->normalizeValue($sourceOption['name']);
            
            if (isset($targetOptionsNormalized[$normalized])) {
                // Duplicate found - map to existing target option
                $migrationPlan['migrations'][] = [
                    'sourceOptionId' => $sourceOption['id'],
                    'sourceOptionName' => $sourceOption['name'],
                    'sourceGroupName' => $sourceOption['group_name'],
                    'targetOptionId' => $targetOptionsNormalized[$normalized]['id'],
                    'targetOptionName' => $targetOptionsNormalized[$normalized]['name'],
                    'productCount' => (int)$sourceOption['product_count'],
                    'action' => 'MIGRATE_TO_EXISTING'
                ];
                $migrationPlan['statistics']['duplicatesFound']++;
            } else {
                // New option needed
                $newOptionId = 'NEW_' . $sourceOption['id'];
                $migrationPlan['newOptions'][] = [
                    'tempId' => $newOptionId,
                    'name' => $sourceOption['name'],
                    'normalized' => $normalized
                ];
                
                $migrationPlan['migrations'][] = [
                    'sourceOptionId' => $sourceOption['id'],
                    'sourceOptionName' => $sourceOption['name'],
                    'sourceGroupName' => $sourceOption['group_name'],
                    'targetOptionId' => $newOptionId,
                    'targetOptionName' => $sourceOption['name'],
                    'productCount' => (int)$sourceOption['product_count'],
                    'action' => 'CREATE_AND_MIGRATE'
                ];
                
                $targetOptionsNormalized[$normalized] = ['id' => $newOptionId, 'name' => $sourceOption['name']];
                $migrationPlan['statistics']['newOptionsToCreate']++;
            }
            
            $migrationPlan['statistics']['productsAffected'] += (int)$sourceOption['product_count'];
        }

        return $migrationPlan;
    }

    private function displayExecutionPlan(array $plan, SymfonyStyle $io): void
    {
        $io->section('Execution Plan');
        
        // Statistics
        $stats = $plan['statistics'];
        $io->definitionList(
            ['Total source options' => $stats['totalSourceOptions']],
            ['Duplicates found' => $stats['duplicatesFound']],
            ['New options to create' => $stats['newOptionsToCreate']],
            ['Products affected' => $stats['productsAffected']]
        );

        // Migrations table
        $table = [];
        foreach ($plan['migrations'] as $migration) {
            $table[] = [
                $migration['sourceGroupName'],
                $migration['sourceOptionName'],
                $migration['targetOptionName'],
                $migration['productCount'],
                $migration['action']
            ];
        }

        $io->table(['Source Group', 'Source Option', 'Target Option', 'Products', 'Action'], $table);
    }

    private function simulateConsolidation(array $plan, SymfonyStyle $io): void
    {
        $io->info('Simulating consolidation process...');
        
        if (!empty($plan['newOptions'])) {
            $io->text(sprintf('Would create %d new options in target group', count($plan['newOptions'])));
        }
        
        $io->text(sprintf('Would migrate %d property assignments', count($plan['migrations'])));
        $io->text(sprintf('Would delete %d source property groups', count($plan['sourceGroups'])));
        
        $io->success('Simulation completed - no actual changes made');
    }

    private function executeConsolidationWithTransaction(array $plan, Context $context, int $batchSize, SymfonyStyle $io): void
    {
        $totalSteps = count($plan['newOptions']) + count($plan['migrations']) + count($plan['sourceGroups']);
        $progressBar = new ProgressBar($io, $totalSteps);
        $progressBar->start();

        // Start database transaction for all operations
        $this->connection->beginTransaction();
        
        try {
            $io->text('Starting transaction...');
            
            // Step 1: Create new options in target group
            $newOptionMapping = [];
            if (!empty($plan['newOptions'])) {
                $io->text('Creating new property options...');
                $newOptionMapping = $this->createNewOptions($plan['targetGroup']['id'], $plan['newOptions'], $context, $progressBar);
            }

            // Step 2: Migrate product assignments
            $io->text('Migrating product property assignments...');
            $this->migrateProductAssignments($plan['migrations'], $newOptionMapping, $context, $batchSize, $progressBar);

            // Step 3: Validate data integrity before cleanup
            $io->text('Validating data integrity...');
            $this->validateMigrationIntegrity($plan, $io);

            // Step 4: Clean up source groups
            $io->text('Cleaning up source property groups...');
            $this->cleanupSourceGroups($plan['sourceGroups'], $context, $progressBar);

            // Final validation
            $io->text('Performing final validation...');
            $this->validateFinalState($plan, $io);

            // Commit transaction
            $this->connection->commit();
            $io->text('Transaction committed successfully.');

            $progressBar->finish();
            $io->newLine(2);

            // Log successful completion
            $this->logger->info('Property consolidation completed successfully', [
                'targetGroup' => $plan['targetGroup']['name'],
                'sourceGroups' => array_column($plan['sourceGroups'], 'name'),
                'migrationsProcessed' => count($plan['migrations']),
                'newOptionsCreated' => count($plan['newOptions'])
            ]);

        } catch (\Exception $e) {
            $progressBar->clear();
            
            // Rollback transaction on any error
            $this->connection->rollBack();
            $io->error('Transaction rolled back due to error');
            
            $this->logger->error('Property consolidation failed, transaction rolled back', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'targetGroup' => $plan['targetGroup']['name'] ?? 'unknown'
            ]);
            
            throw $e;
        }
    }

    private function validateMigrationIntegrity(array $plan, SymfonyStyle $io): void
    {
        $errors = [];

        // Check if all source options were properly migrated
        foreach ($plan['migrations'] as $migration) {
            $sourceOptionId = $migration['sourceOptionId'];
            
            // Check product_property table
            $remainingProductProperties = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM product_property WHERE property_group_option_id = UNHEX(:sourceOptionId)',
                ['sourceOptionId' => $sourceOptionId]
            );

            if ((int)$remainingProductProperties > 0) {
                $errors[] = sprintf(
                    'Source option %s (%s) still has %d product property assignments',
                    $migration['sourceOptionName'],
                    $sourceOptionId,
                    $remainingProductProperties
                );
            }

            // Check product_configurator_setting table
            $remainingConfiguratorSettings = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM product_configurator_setting WHERE property_group_option_id = UNHEX(:sourceOptionId)',
                ['sourceOptionId' => $sourceOptionId]
            );

            if ((int)$remainingConfiguratorSettings > 0) {
                $errors[] = sprintf(
                    'Source option %s (%s) still has %d configurator settings',
                    $migration['sourceOptionName'],
                    $sourceOptionId,
                    $remainingConfiguratorSettings
                );
            }
        }

        if (!empty($errors)) {
            $io->error('Migration integrity validation failed:');
            foreach ($errors as $error) {
                $io->text('  - ' . $error);
            }
            throw new \RuntimeException('Migration integrity validation failed: ' . implode('; ', $errors));
        }

        $io->text('✓ Migration integrity validation passed');
    }

    private function validateFinalState(array $plan, SymfonyStyle $io): void
    {
        $errors = [];

        // Verify target group exists and has expected options
        $targetGroupOptions = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM property_group_option WHERE property_group_id = UNHEX(:groupId)',
            ['groupId' => $plan['targetGroup']['id']]
        );

        // Debug: Check what happened to target group options
        $targetGroupOptionsDetailed = $this->connection->fetchAllAssociative(
            'SELECT HEX(pgo.id) as id, pgot.name
             FROM property_group_option pgo
             LEFT JOIN property_group_option_translation pgot ON pgo.id = pgot.property_group_option_id AND pgot.language_id = UNHEX(:languageId)
             WHERE pgo.property_group_id = UNHEX(:groupId)
             ORDER BY pgot.name
             LIMIT 10',
            [
                'groupId' => $plan['targetGroup']['id'],
                'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b'
            ]
        );

        $this->logger->debug('Target group options after migration (first 10)', [
            'targetGroupId' => $plan['targetGroup']['id'],
            'actualCount' => (int)$targetGroupOptions,
            'sampleOptions' => $targetGroupOptionsDetailed
        ]);

        // Berechne erwartete Options: Original + Neue Options
        // WICHTIG: option_count ist jetzt mit COUNT(DISTINCT) berechnet und korrekt
        $originalTargetOptions = (int)$plan['targetGroup']['option_count'];
        $newOptionsCreated = count($plan['newOptions']);

        // Für eine korrekte Berechnung müssen wir alle Source-Options berücksichtigen, die tatsächlich migriert wurden
        $totalSourceOptions = 0;
        $duplicatesToExisting = 0;

        foreach ($plan['migrations'] as $migration) {
            $totalSourceOptions++;
            if ($migration['action'] === 'MIGRATE_TO_EXISTING') {
                $duplicatesToExisting++;
            }
        }

        // Erwartete Anzahl: Original Target Options + Neue Options
        // Die neuen Options wurden definitiv zur Target Group hinzugefügt
        // Duplicates (MIGRATE_TO_EXISTING) fügen keine neuen Options hinzu, sie migrieren nur Product Properties
        $expectedMinimumOptions = $originalTargetOptions + $newOptionsCreated;

        $this->logger->info('Final state validation calculation', [
            'originalTargetOptions' => $originalTargetOptions,
            'newOptionsCreated' => $newOptionsCreated,
            'totalSourceOptions' => $totalSourceOptions,
            'duplicatesToExisting' => $duplicatesToExisting,
            'expectedMinimumOptions' => $expectedMinimumOptions,
            'actualTargetOptions' => (int)$targetGroupOptions
        ]);

        // Wir erwarten mindestens die Original Options + Neue Options
        // Es können nicht weniger sein, da wir keine Target Options löschen
        if ((int)$targetGroupOptions < $expectedMinimumOptions) {
            $errors[] = sprintf(
                'Target group has %d options, expected at least %d (original: %d + new: %d)',
                $targetGroupOptions,
                $expectedMinimumOptions,
                $originalTargetOptions,
                $newOptionsCreated
            );
        }

        // Warnung wenn es mehr Options gibt als erwartet (sollte nicht passieren)
        if ((int)$targetGroupOptions > $expectedMinimumOptions) {
            $this->logger->warning('Target group has more options than expected', [
                'actualOptions' => (int)$targetGroupOptions,
                'expectedOptions' => $expectedMinimumOptions,
                'difference' => (int)$targetGroupOptions - $expectedMinimumOptions
            ]);
        }

        // Verify source groups are ready for deletion (no remaining options with references)
        foreach ($plan['sourceGroups'] as $sourceGroup) {
            $optionsWithReferences = $this->connection->fetchOne(
                'SELECT COUNT(DISTINCT pgo.id) 
                 FROM property_group_option pgo 
                 LEFT JOIN product_property pp ON pgo.id = pp.property_group_option_id
                 LEFT JOIN product_configurator_setting pcs ON pgo.id = pcs.property_group_option_id
                 WHERE pgo.property_group_id = UNHEX(:groupId)
                 AND (pp.product_id IS NOT NULL OR pcs.product_id IS NOT NULL)',
                ['groupId' => $sourceGroup['id']]
            );

            if ((int)$optionsWithReferences > 0) {
                $errors[] = sprintf(
                    'Source group %s (%s) still has %d options with references',
                    $sourceGroup['name'],
                    $sourceGroup['id'],
                    $optionsWithReferences
                );
            }
        }

        if (!empty($errors)) {
            $io->error('Final state validation failed:');
            foreach ($errors as $error) {
                $io->text('  - ' . $error);
            }
            throw new \RuntimeException('Final state validation failed: ' . implode('; ', $errors));
        }

        $io->text('✓ Final state validation passed');
    }

    private function createNewOptions(string $targetGroupId, array $newOptions, Context $context, ProgressBar $progressBar): array
    {
        $newOptionMapping = [];
        $languageId = '2fbb5fe2e29a4d70aa5854ce7ce3e20b'; // DE language

        // Debug logging
        $this->logger->info('Creating new options with DBAL', [
            'targetGroupId' => $targetGroupId,
            'targetGroupIdLength' => strlen($targetGroupId),
            'newOptionsCount' => count($newOptions)
        ]);

        foreach ($newOptions as $option) {
            try {
                // Generate new UUID for option
                $newOptionId = \Shopware\Core\Framework\Uuid\Uuid::randomHex();

                // Insert into property_group_option
                $this->connection->executeStatement(
                    'INSERT INTO property_group_option (id, property_group_id, created_at) VALUES (UNHEX(?), UNHEX(?), NOW())',
                    [$newOptionId, $targetGroupId]
                );

                // Insert translation
                $this->connection->executeStatement(
                    'INSERT INTO property_group_option_translation (property_group_option_id, language_id, name, position, created_at) VALUES (UNHEX(?), UNHEX(?), ?, 1, NOW())',
                    [$newOptionId, $languageId, $option['name']]
                );

                // Map temp ID to real ID
                $newOptionMapping[$option['tempId']] = $newOptionId;

                // Immediate verification that option was created
                $verifyOption = $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM property_group_option WHERE id = UNHEX(:optionId)',
                    ['optionId' => $newOptionId]
                );

                $verifyTranslation = $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM property_group_option_translation WHERE property_group_option_id = UNHEX(:optionId)',
                    ['optionId' => $newOptionId]
                );

                if ((int)$verifyOption === 0 || (int)$verifyTranslation === 0) {
                    throw new \RuntimeException(sprintf(
                        'Failed to create new option %s - Option exists: %d, Translation exists: %d',
                        $option['name'],
                        $verifyOption,
                        $verifyTranslation
                    ));
                }

                $this->logger->debug('Created and verified property option via DBAL', [
                    'optionId' => $newOptionId,
                    'name' => $option['name'],
                    'tempId' => $option['tempId'],
                    'optionExists' => $verifyOption,
                    'translationExists' => $verifyTranslation
                ]);

            } catch (\Exception $e) {
                $this->logger->error('Failed to create property option via DBAL', [
                    'optionName' => $option['name'],
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            $progressBar->advance();
        }

        // Final verification of all created options
        $finalVerificationCount = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM property_group_option WHERE property_group_id = UNHEX(:targetGroupId)',
            ['targetGroupId' => $targetGroupId]
        );

        $this->logger->info(sprintf('Created %d new property options via DBAL', count($newOptions)), [
            'newOptionMapping' => $newOptionMapping,
            'totalTargetGroupOptions' => $finalVerificationCount
        ]);

        if (count($newOptionMapping) !== count($newOptions)) {
            throw new \RuntimeException(sprintf(
                'Option creation verification failed: Created %d mappings but expected %d',
                count($newOptionMapping),
                count($newOptions)
            ));
        }

        return $newOptionMapping;
    }

    private function migrateProductAssignments(array $migrations, array $newOptionMapping, Context $context, int $batchSize, ProgressBar $progressBar): void
    {
        $chunks = array_chunk($migrations, $batchSize);
        
        foreach ($chunks as $chunk) {
            $this->processMigrationChunk($chunk, $newOptionMapping, $context);

            foreach ($chunk as $migration) {
                $progressBar->advance();
            }
        }
        
        $this->logger->info(sprintf('Completed migration of %d property assignments', count($migrations)));
    }

    private function processMigrationChunk(array $migrations, array $newOptionMapping, Context $context): void
    {
        // Group migrations by source option for efficient processing
        $migrationGroups = [];
        foreach ($migrations as $migration) {
            $sourceOptionId = $migration['sourceOptionId'];
            if (!isset($migrationGroups[$sourceOptionId])) {
                $migrationGroups[$sourceOptionId] = $migration;
            }
        }

        foreach ($migrationGroups as $sourceOptionId => $migration) {
            $targetOptionId = $migration['targetOptionId'];

            $this->logger->debug('Processing migration group', [
                'sourceOptionId' => $sourceOptionId,
                'originalTargetId' => $targetOptionId,
                'targetOptionName' => $migration['targetOptionName'],
                'productCount' => $migration['productCount'] ?? 'unknown'
            ]);

            // Handle new options that were just created
            if (str_starts_with($targetOptionId, 'NEW_')) {
                if (isset($newOptionMapping[$targetOptionId])) {
                    $realTargetOptionId = $newOptionMapping[$targetOptionId];
                    $this->logger->debug('Resolved new option via mapping', [
                        'tempId' => $targetOptionId,
                        'realId' => $realTargetOptionId,
                        'optionName' => $migration['targetOptionName']
                    ]);
                    $targetOptionId = $realTargetOptionId;
                } else {
                    $this->logger->error('Could not resolve new option ID from mapping', [
                        'tempId' => $targetOptionId,
                        'optionName' => $migration['targetOptionName'],
                        'availableMappings' => array_keys($newOptionMapping),
                        'mappingValues' => $newOptionMapping
                    ]);
                    continue;
                }
            }

            // Get all products that have the source property
            $productsWithSourceProperty = $this->connection->fetchAllAssociative(
                'SELECT DISTINCT HEX(product_id) as product_id FROM product_property WHERE property_group_option_id = UNHEX(:sourceOptionId)',
                ['sourceOptionId' => $sourceOptionId]
            );

            $this->logger->debug('Found products with source property', [
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'productCount' => count($productsWithSourceProperty)
            ]);

            // Migrate each product using DBAL
            $migrationSuccess = 0;
            $migrationErrors = 0;

            foreach ($productsWithSourceProperty as $productRow) {
                try {
                    $this->migrateProductProperty($productRow['product_id'], $sourceOptionId, $targetOptionId, $context);
                    $migrationSuccess++;
                } catch (\Exception $e) {
                    $migrationErrors++;
                    $this->logger->error('Product property migration failed', [
                        'productId' => $productRow['product_id'],
                        'sourceOptionId' => $sourceOptionId,
                        'targetOptionId' => $targetOptionId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->logger->debug('Product property migration results', [
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'totalProducts' => count($productsWithSourceProperty),
                'migrationSuccess' => $migrationSuccess,
                'migrationErrors' => $migrationErrors
            ]);

            // Also check product_configurator_setting for variant configurations
            $variantConfigs = $this->connection->fetchAllAssociative(
                'SELECT DISTINCT HEX(product_id) as product_id FROM product_configurator_setting WHERE property_group_option_id = UNHEX(:sourceOptionId)',
                ['sourceOptionId' => $sourceOptionId]
            );

            $this->logger->debug('Found variant configurations with source property', [
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'variantConfigCount' => count($variantConfigs)
            ]);

            foreach ($variantConfigs as $variantRow) {
                $this->migrateConfiguratorSetting($variantRow['product_id'], $sourceOptionId, $targetOptionId, $context);
            }

            // WICHTIG: Auch parent products mit variant children prüfen
            // Parent-Produkte können configurator_group_config haben, die auf die Property Group verweist
            $parentProductsWithVariants = $this->connection->fetchAllAssociative(
                'SELECT DISTINCT HEX(p.id) as product_id, HEX(p.parent_id) as parent_id
                 FROM product p
                 INNER JOIN product_configurator_setting pcs ON pcs.product_id = p.id
                 WHERE pcs.property_group_option_id = UNHEX(:sourceOptionId) AND p.parent_id IS NOT NULL',
                ['sourceOptionId' => $sourceOptionId]
            );

            $this->logger->debug('Found parent products with variant children using source property', [
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'parentProductCount' => count($parentProductsWithVariants)
            ]);

            // Prüfe und update configurator_group_config für Parent-Products
            foreach ($parentProductsWithVariants as $productRow) {
                if ($productRow['parent_id']) {
                    $this->migrateParentProductVariantConfig($productRow['parent_id'], $sourceOptionId, $targetOptionId, $context);
                }
            }
        }
    }

    private function migrateProductProperty(string $productId, string $sourceOptionId, string $targetOptionId, Context $context): void
    {
        try {
            // First check if source property actually exists
            $sourceExists = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM product_property WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:sourceOptionId)',
                [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId
                ]
            );

            if ((int)$sourceExists === 0) {
                $this->logger->debug('Source product property does not exist', [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId,
                    'targetOptionId' => $targetOptionId
                ]);
                return;
            }

            // Check if target property already exists to avoid duplicates
            $existsTarget = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM product_property WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:targetOptionId)',
                [
                    'productId' => $productId,
                    'targetOptionId' => $targetOptionId
                ]
            );

            if ((int)$existsTarget === 0) {
                // Direct DBAL update - replace source with target
                $affectedRows = $this->connection->executeStatement(
                    'UPDATE product_property SET property_group_option_id = UNHEX(:targetOptionId) WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:sourceOptionId)',
                    [
                        'targetOptionId' => $targetOptionId,
                        'productId' => $productId,
                        'sourceOptionId' => $sourceOptionId
                    ]
                );

                $this->logger->debug('Migrated product property via DBAL', [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId,
                    'targetOptionId' => $targetOptionId,
                    'affectedRows' => $affectedRows
                ]);
            } else {
                // Target already exists, just delete source
                $affectedRows = $this->connection->executeStatement(
                    'DELETE FROM product_property WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:sourceOptionId)',
                    [
                        'productId' => $productId,
                        'sourceOptionId' => $sourceOptionId
                    ]
                );

                $this->logger->debug('Deleted duplicate product property via DBAL', [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId,
                    'targetOptionId' => $targetOptionId,
                    'affectedRows' => $affectedRows
                ]);
            }

            // Verify migration success
            $remainingSource = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM product_property WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:sourceOptionId)',
                [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId
                ]
            );

            if ((int)$remainingSource > 0) {
                $this->logger->error('Product property migration verification failed - source still exists', [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId,
                    'targetOptionId' => $targetOptionId,
                    'remainingSource' => $remainingSource
                ]);
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to migrate product property via DBAL', [
                'productId' => $productId,
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function migrateConfiguratorSetting(string $productId, string $sourceOptionId, string $targetOptionId, Context $context): void
    {
        try {
            // Get current configurator settings
            $configuratorSettings = $this->connection->fetchAllAssociative(
                'SELECT HEX(id) as id FROM product_configurator_setting WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:sourceOptionId)',
                [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId
                ]
            );

            if (empty($configuratorSettings)) {
                $this->logger->debug('No configurator settings found for source option', [
                    'productId' => $productId,
                    'sourceOptionId' => $sourceOptionId,
                    'targetOptionId' => $targetOptionId
                ]);
                return;
            }

            foreach ($configuratorSettings as $setting) {
                // Check if target option already has a configurator setting to avoid duplicates
                $existsTarget = $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM product_configurator_setting WHERE product_id = UNHEX(:productId) AND property_group_option_id = UNHEX(:targetOptionId)',
                    [
                        'productId' => $productId,
                        'targetOptionId' => $targetOptionId
                    ]
                );

                if ((int)$existsTarget === 0) {
                    // Update the configurator setting using direct DBAL
                    $affectedRows = $this->connection->executeStatement(
                        'UPDATE product_configurator_setting SET property_group_option_id = UNHEX(:targetOptionId) WHERE id = UNHEX(:settingId)',
                        [
                            'targetOptionId' => $targetOptionId,
                            'settingId' => $setting['id']
                        ]
                    );

                    $this->logger->debug('Migrated configurator setting via DBAL', [
                        'productId' => $productId,
                        'sourceOptionId' => $sourceOptionId,
                        'targetOptionId' => $targetOptionId,
                        'settingId' => $setting['id'],
                        'affectedRows' => $affectedRows
                    ]);
                } else {
                    // Delete the duplicate setting
                    $affectedRows = $this->connection->executeStatement(
                        'DELETE FROM product_configurator_setting WHERE id = UNHEX(:settingId)',
                        ['settingId' => $setting['id']]
                    );

                    $this->logger->debug('Deleted duplicate configurator setting via DBAL', [
                        'productId' => $productId,
                        'sourceOptionId' => $sourceOptionId,
                        'targetOptionId' => $targetOptionId,
                        'settingId' => $setting['id'],
                        'affectedRows' => $affectedRows
                    ]);
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to migrate configurator setting', [
                'productId' => $productId,
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function migrateParentProductVariantConfig(string $parentProductId, string $sourceOptionId, string $targetOptionId, Context $context): void
    {
        try {
            // Prüfe ob Parent-Product configurator_group_config hat, die auf Source Property Group zeigt
            $sourcePropertyGroupId = $this->connection->fetchOne(
                'SELECT HEX(property_group_id) FROM property_group_option WHERE id = UNHEX(:sourceOptionId)',
                ['sourceOptionId' => $sourceOptionId]
            );

            $targetPropertyGroupId = $this->connection->fetchOne(
                'SELECT HEX(property_group_id) FROM property_group_option WHERE id = UNHEX(:targetOptionId)',
                ['targetOptionId' => $targetOptionId]
            );

            if (!$sourcePropertyGroupId || !$targetPropertyGroupId) {
                $this->logger->warning('Could not determine property group IDs for parent product variant config migration', [
                    'parentProductId' => $parentProductId,
                    'sourceOptionId' => $sourceOptionId,
                    'targetOptionId' => $targetOptionId,
                    'sourcePropertyGroupId' => $sourcePropertyGroupId,
                    'targetPropertyGroupId' => $targetPropertyGroupId
                ]);
                return;
            }

            // Wenn Source und Target zur gleichen Property Group gehören, ist keine Migration nötig
            if ($sourcePropertyGroupId === $targetPropertyGroupId) {
                $this->logger->debug('Source and target belong to same property group, no parent config migration needed', [
                    'parentProductId' => $parentProductId,
                    'propertyGroupId' => $sourcePropertyGroupId
                ]);
                return;
            }

            // Prüfe product_configurator_group_config
            $configuratorGroupConfigs = $this->connection->fetchAllAssociative(
                'SELECT HEX(id) as id FROM product_configurator_group_config
                 WHERE product_id = UNHEX(:parentProductId) AND property_group_id = UNHEX(:sourcePropertyGroupId)',
                [
                    'parentProductId' => $parentProductId,
                    'sourcePropertyGroupId' => $sourcePropertyGroupId
                ]
            );

            foreach ($configuratorGroupConfigs as $config) {
                // Prüfe ob Target Property Group bereits konfiguriert ist
                $targetExists = $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM product_configurator_group_config
                     WHERE product_id = UNHEX(:parentProductId) AND property_group_id = UNHEX(:targetPropertyGroupId)',
                    [
                        'parentProductId' => $parentProductId,
                        'targetPropertyGroupId' => $targetPropertyGroupId
                    ]
                );

                if ((int)$targetExists === 0) {
                    // Update zur Target Property Group
                    $affectedRows = $this->connection->executeStatement(
                        'UPDATE product_configurator_group_config
                         SET property_group_id = UNHEX(:targetPropertyGroupId)
                         WHERE id = UNHEX(:configId)',
                        [
                            'targetPropertyGroupId' => $targetPropertyGroupId,
                            'configId' => $config['id']
                        ]
                    );

                    $this->logger->debug('Migrated parent product configurator group config', [
                        'parentProductId' => $parentProductId,
                        'sourcePropertyGroupId' => $sourcePropertyGroupId,
                        'targetPropertyGroupId' => $targetPropertyGroupId,
                        'configId' => $config['id'],
                        'affectedRows' => $affectedRows
                    ]);
                } else {
                    // Target existiert bereits, lösche Source Config
                    $affectedRows = $this->connection->executeStatement(
                        'DELETE FROM product_configurator_group_config WHERE id = UNHEX(:configId)',
                        ['configId' => $config['id']]
                    );

                    $this->logger->debug('Deleted duplicate parent product configurator group config', [
                        'parentProductId' => $parentProductId,
                        'sourcePropertyGroupId' => $sourcePropertyGroupId,
                        'targetPropertyGroupId' => $targetPropertyGroupId,
                        'configId' => $config['id'],
                        'affectedRows' => $affectedRows
                    ]);
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to migrate parent product variant config', [
                'parentProductId' => $parentProductId,
                'sourceOptionId' => $sourceOptionId,
                'targetOptionId' => $targetOptionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function resolveNewOptionId(string $optionName, Context $context): ?string
    {
        $result = $this->connection->fetchOne(
            'SELECT HEX(pgo.id) as id
             FROM property_group_option pgo
             LEFT JOIN property_group_option_translation pgot ON pgo.id = pgot.property_group_option_id AND pgot.language_id = UNHEX(:languageId)
             WHERE pgot.name = :name
             ORDER BY pgo.created_at DESC
             LIMIT 1',
            [
                'name' => $optionName,
                'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b'
            ]
        );

        return $result ?: null;
    }

    private function cleanupSourceGroups(array $sourceGroups, Context $context, ProgressBar $progressBar): void
    {
        foreach ($sourceGroups as $group) {
            try {
                // First, verify no options are left (safety check)
                $remainingOptions = $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM property_group_option WHERE property_group_id = UNHEX(:groupId)',
                    ['groupId' => $group['id']]
                );

                if ((int)$remainingOptions > 0) {
                    $this->logger->warning('Source group still has options, skipping deletion', [
                        'groupId' => $group['id'],
                        'groupName' => $group['name'],
                        'remainingOptions' => $remainingOptions
                    ]);
                    $progressBar->advance();
                    continue;
                }

                // Safe deletion using Repository pattern
                $this->propertyGroupRepository->delete([
                    ['id' => $group['id']]
                ], $context);

                $this->logger->info('Deleted source property group', [
                    'groupId' => $group['id'],
                    'groupName' => $group['name']
                ]);

            } catch (\Exception $e) {
                $this->logger->error('Failed to delete source property group', [
                    'groupId' => $group['id'],
                    'groupName' => $group['name'],
                    'error' => $e->getMessage()
                ]);
            }
            
            $progressBar->advance();
        }
    }

    private function normalizeValue(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}