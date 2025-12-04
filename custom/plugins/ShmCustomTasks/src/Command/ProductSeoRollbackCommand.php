<?php declare(strict_types=1);

namespace Shm\ShmCustomTasks\Command;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shm:product:seo-rollback',
    description: 'Rollback product title optimizations using backup files'
)]
class ProductSeoRollbackCommand extends Command
{
    private const BACKUP_DIR = 'var/product_title_backups';

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('backup-file', 'f', InputOption::VALUE_REQUIRED, 'Path to backup file to restore from')
            ->addOption('list-backups', 'l', InputOption::VALUE_NONE, 'List available backup files')
            ->addOption('product-numbers', 'p', InputOption::VALUE_OPTIONAL, 'Comma-separated list of specific products to rollback')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate rollback without making changes')
            ->setHelp(<<<'EOF'
This command allows you to rollback product title optimizations using backup files.

Examples:
  <info>%command.name% --list-backups</info>
  <info>%command.name% --backup-file="var/product_title_backups/product_titles_2024-01-15_14-30-00.json"</info>
  <info>%command.name% --backup-file="backup.json" --product-numbers="SW10001,SW10002" --dry-run</info>

The backup files contain:
- Original product titles before optimization
- Product IDs and numbers
- Timestamp of backup creation
- Language information
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();

        $backupFile = $input->getOption('backup-file');
        $listBackups = $input->getOption('list-backups');
        $productNumbers = $input->getOption('product-numbers');
        $dryRun = $input->getOption('dry-run');

        $io->title('Product SEO Title Rollback');

        try {
            // List available backups
            if ($listBackups) {
                $this->listAvailableBackups($io);
                return Command::SUCCESS;
            }

            // Validate backup file
            if (!$backupFile) {
                $io->error('Please specify a backup file with --backup-file or use --list-backups to see available files');
                return Command::FAILURE;
            }

            $backupPath = $this->resolveBackupPath($backupFile);
            if (!file_exists($backupPath)) {
                $io->error(sprintf('Backup file not found: %s', $backupPath));
                return Command::FAILURE;
            }

            // Load backup data
            $io->section('Loading Backup Data');
            $backupData = $this->loadBackupData($backupPath, $io);

            // Filter products if specific ones were requested
            if ($productNumbers) {
                $requestedProducts = array_map('trim', explode(',', $productNumbers));
                $backupData['products'] = array_filter($backupData['products'], function ($product) use ($requestedProducts) {
                    return in_array($product['productNumber'], $requestedProducts);
                });
                $io->info(sprintf('Filtered to %d specific products', count($backupData['products'])));
            }

            if (empty($backupData['products'])) {
                $io->warning('No products found in backup to restore');
                return Command::SUCCESS;
            }

            // Display rollback plan
            $this->displayRollbackPlan($backupData, $io);

            // Confirm rollback
            if (!$io->confirm(sprintf('Proceed with rollback of %d products?', count($backupData['products'])), !$dryRun)) {
                $io->info('Rollback cancelled by user');
                return Command::SUCCESS;
            }

            // Execute rollback
            $io->section('Executing Rollback');
            $results = $this->executeRollback($backupData, $dryRun, $context, $io);

            // Display results
            $this->displayResults($results, $io);

            $io->success(sprintf('Rollback completed. Restored %d products.', $results['restored']));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Product SEO rollback failed', [
                'error' => $e->getMessage(),
                'backupFile' => $backupFile
            ]);

            $io->error(sprintf('Rollback failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function listAvailableBackups(SymfonyStyle $io): void
    {
        $backupDir = $this->projectDir . '/' . self::BACKUP_DIR;

        if (!is_dir($backupDir)) {
            $io->warning('No backup directory found. No backups available.');
            return;
        }

        $backupFiles = glob($backupDir . '/*.json');

        if (empty($backupFiles)) {
            $io->warning('No backup files found.');
            return;
        }

        $io->section('Available Backup Files');

        $table = [];
        foreach ($backupFiles as $file) {
            $data = json_decode(file_get_contents($file), true);
            $table[] = [
                basename($file),
                $data['timestamp'] ?? 'Unknown',
                $data['totalProducts'] ?? 'Unknown',
                $data['languageId'] === '2fbb5fe2e29a4d70aa5854ce7ce3e20b' ? 'DE' : $data['languageId'] ?? 'Unknown',
                filesize($file) . ' bytes'
            ];
        }

        $io->table(['Filename', 'Created', 'Products', 'Language', 'Size'], $table);

        $io->info('Use --backup-file with the filename to restore from a specific backup');
    }

    private function resolveBackupPath(string $backupFile): string
    {
        // If it's already an absolute path, use it
        if (str_starts_with($backupFile, '/')) {
            return $backupFile;
        }

        // If it's just a filename, look in backup directory
        if (!str_contains($backupFile, '/')) {
            return $this->projectDir . '/' . self::BACKUP_DIR . '/' . $backupFile;
        }

        // Otherwise, treat as relative to project directory
        return $this->projectDir . '/' . $backupFile;
    }

    private function loadBackupData(string $backupPath, SymfonyStyle $io): array
    {
        $jsonData = file_get_contents($backupPath);
        $backupData = json_decode($jsonData, true);

        if (!$backupData) {
            throw new \RuntimeException('Invalid backup file format');
        }

        $io->info(sprintf('Loaded backup from %s', $backupData['timestamp']));
        $io->info(sprintf('Language: %s', $backupData['languageId'] === '2fbb5fe2e29a4d70aa5854ce7ce3e20b' ? 'DE' : $backupData['languageId']));
        $io->info(sprintf('Total products in backup: %d', count($backupData['products'])));

        return $backupData;
    }

    private function displayRollbackPlan(array $backupData, SymfonyStyle $io): void
    {
        $io->section('Rollback Plan');

        // Show first few products as preview
        $preview = array_slice($backupData['products'], 0, 5);
        $table = [];

        foreach ($preview as $product) {
            // Get current title for comparison
            $currentTitle = $this->getCurrentProductTitle($product['id'], $backupData['languageId']);

            $table[] = [
                $product['productNumber'],
                $currentTitle ? substr($currentTitle, 0, 40) . '...' : 'Not found',
                substr($product['originalTitle'], 0, 40) . '...',
                $currentTitle === $product['originalTitle'] ? 'No change' : 'Will restore'
            ];
        }

        $io->table(['Product Number', 'Current Title', 'Backup Title', 'Action'], $table);

        if (count($backupData['products']) > 5) {
            $io->info(sprintf('... and %d more products', count($backupData['products']) - 5));
        }
    }

    private function getCurrentProductTitle(string $productId, string $languageId): ?string
    {
        $result = $this->connection->fetchOne(
            'SELECT pt.name
             FROM product_translation pt
             WHERE pt.product_id = UNHEX(:productId) AND pt.language_id = UNHEX(:languageId)',
            [
                'productId' => $productId,
                'languageId' => $languageId
            ]
        );

        return $result ?: null;
    }

    private function executeRollback(array $backupData, bool $dryRun, Context $context, SymfonyStyle $io): array
    {
        $results = [
            'restored' => 0,
            'skipped' => 0,
            'failed' => 0,
            'details' => []
        ];

        $progressBar = new ProgressBar($io, count($backupData['products']));
        $progressBar->start();

        foreach ($backupData['products'] as $product) {
            try {
                $currentTitle = $this->getCurrentProductTitle($product['id'], $backupData['languageId']);

                // Skip if product not found
                if (!$currentTitle) {
                    $results['skipped']++;
                    $results['details'][] = [
                        'productNumber' => $product['productNumber'],
                        'status' => 'skipped',
                        'reason' => 'Product not found'
                    ];
                    $progressBar->advance();
                    continue;
                }

                // Skip if title is already the same as backup
                if ($currentTitle === $product['originalTitle']) {
                    $results['skipped']++;
                    $results['details'][] = [
                        'productNumber' => $product['productNumber'],
                        'status' => 'skipped',
                        'reason' => 'Title already matches backup'
                    ];
                    $progressBar->advance();
                    continue;
                }

                // Restore title
                if (!$dryRun) {
                    $this->restoreProductTitle($product['id'], $product['originalTitle'], $backupData['languageId'], $context);
                }

                $results['restored']++;
                $results['details'][] = [
                    'productNumber' => $product['productNumber'],
                    'status' => 'restored',
                    'currentTitle' => $currentTitle,
                    'restoredTitle' => $product['originalTitle']
                ];

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'productNumber' => $product['productNumber'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];

                $this->logger->error('Product rollback failed', [
                    'productNumber' => $product['productNumber'],
                    'productId' => $product['id'],
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);

        return $results;
    }

    private function restoreProductTitle(string $productId, string $originalTitle, string $languageId, Context $context): void
    {
        $updateData = [
            'id' => $productId,
            'translations' => [
                $languageId => [
                    'name' => $originalTitle
                ]
            ]
        ];

        $this->productRepository->update([$updateData], $context);

        $this->logger->info('Product title restored from backup', [
            'productId' => $productId,
            'restoredTitle' => $originalTitle,
            'languageId' => $languageId
        ]);
    }

    private function displayResults(array $results, SymfonyStyle $io): void
    {
        $io->section('Rollback Results');

        $io->definitionList(
            ['Restored' => $results['restored']],
            ['Skipped' => $results['skipped']],
            ['Failed' => $results['failed']]
        );

        if (!empty($results['details'])) {
            $table = [];
            foreach ($results['details'] as $detail) {
                $table[] = [
                    $detail['productNumber'],
                    $detail['status'],
                    $detail['currentTitle'] ?? '-',
                    $detail['restoredTitle'] ?? ($detail['reason'] ?? $detail['error'] ?? '-')
                ];
            }

            $io->table(['Product Number', 'Status', 'Current Title', 'Result/Reason'], $table);
        }
    }
}