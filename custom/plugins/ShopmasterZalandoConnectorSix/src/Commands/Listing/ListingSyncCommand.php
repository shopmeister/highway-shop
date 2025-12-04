<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Commands\Listing;

use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\Commands\ZalandoCommand;
use ShopmasterZalandoConnectorSix\Exception\License\SalesChannelNotLicensedException;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Export\Product\Listing\ExportListingByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPsrService;
use ShopmasterZalandoConnectorSix\Services\Api\Zalando\SalesChannels\ApiZalandoSalesChannelsService;
use ShopmasterZalandoConnectorSix\Services\Config\ConfigService;
use ShopmasterZalandoConnectorSix\Services\License\SalesChannelGuard;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sm:listing:sync',
    description: 'Sync product listings with Zalando (bulk operation)'
)]
class ListingSyncCommand extends ZalandoCommand
{
    private LoggerInterface $logger;
    
    /**
     * @var string
     */
    protected static $defaultName = 'sm:listing:sync';

    public function __construct(
        LoggerInterface $logger,
        private ApiZalandoProductPsrService $apiZalandoProductPsrService,
        private ConfigService $configService,
        private ApiZalandoSalesChannelsService $apiZalandoSalesChannelsService,
        private MessageBusInterface $messageBus,
        private SalesChannelGuard $salesChannelGuard
    ) {
        parent::__construct();
        $this->logger = $logger->withName('ListingSyncCommand');
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Run in dry-run mode (show what would be listed without actually doing it)')
            ->addOption('sales-channel', 's', InputOption::VALUE_REQUIRED, 'Specific sales channel ID to sync. If not specified, all active Zalando sales channels will be synced')
            ->setHelp('This command syncs product listings with Zalando for all products that exist at Zalando and are not excluded.');
    }

    public function runProcess(): void
    {
        $input = self::getInput();
        $output = self::getOutput();
        $io = new SymfonyStyle($input, $output);
        
        $isDryRun = $input->getOption('dry-run');
        $specificSalesChannelId = $input->getOption('sales-channel');
        $isVerbose = $output->isVerbose();
        
        $io->title('Product Listing Sync');
        
        if ($isDryRun) {
            $io->note('Running in DRY-RUN mode - no changes will be made');
        }
        
        if ($specificSalesChannelId) {
            $io->text('Filtering by sales channel: ' . $specificSalesChannelId);
        }

        // Check if listing sync is active in any sales channel
        if (!$this->isListingSyncActive($io, $isVerbose)) {
            $io->warning('No active listing sync configuration found. Please activate listing sync in at least one sales channel.');
            return;
        }

        try {
            $io->section('Starting Product Listing Synchronization');
            
            // Create and dispatch message for PSR processing
            $message = new ExportListingByPsrMessage($isDryRun, $specificSalesChannelId);
            
            if ($isVerbose) {
                $io->text('Message parameters:');
                $io->listing([
                    'Dry Run: ' . ($isDryRun ? 'Yes' : 'No'),
                    'Sales Channel Filter: ' . ($specificSalesChannelId ?? 'None (all channels)'),
                ]);
            }
            
            $this->logger->info('Dispatching listing sync message', [
                'isDryRun' => $isDryRun,
                'specificSalesChannelId' => $specificSalesChannelId
            ]);

            // Dispatch the message to the message bus for processing
            $this->messageBus->dispatch($message);
            
            $io->success('Listing sync completed successfully');
            
        } catch (\Throwable $e) {
            $io->error('Listing sync failed: ' . $e->getMessage());
            
            if ($isVerbose) {
                $io->text('Stack trace:');
                $io->text($e->getTraceAsString());
            }
            
            $this->logger->error('Listing sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function isListingSyncActive(SymfonyStyle $io, bool $isVerbose): bool
    {
        try {
            $salesChannelCollection = $this->apiZalandoSalesChannelsService->getCollection()->getActive();

            if ($isVerbose) {
                $io->section('Checking Listing Configuration');
                $io->text(sprintf('Found %d active sales channels', $salesChannelCollection->count()));
            }

            $activeChannels = [];
            foreach ($salesChannelCollection as $salesChannel) {
                try {
                    // LIZENZ-CHECK ZUERST
                    $this->salesChannelGuard->guardSalesChannel($salesChannel->getSalesChannelId());

                    $config = $this->configService->getFormBySalesChannelAndType($salesChannel->getSalesChannelId(), 'listing');
                    $isActive = $config && ($config['activeListing'] ?? false);

                    if ($isVerbose) {
                        $io->text(sprintf('- %s (%s): %s',
                            $salesChannel->getCountryName(),
                            $salesChannel->getCountryCode(),
                            $isActive ? 'ACTIVE' : 'inactive'
                        ));
                    }

                    if ($isActive) {
                        $activeChannels[] = $salesChannel->getCountryName();
                    }
                } catch (SalesChannelNotLicensedException $e) {
                    if ($isVerbose) {
                        $io->text(sprintf('- %s (%s): NOT LICENSED',
                            $salesChannel->getCountryName(),
                            $salesChannel->getCountryCode()
                        ));
                    }
                    $this->logger->warning($e->getMessage(), [
                        'salesChannelId' => $salesChannel->getSalesChannelId()
                    ]);
                    continue;
                }
            }
            
            if (!empty($activeChannels)) {
                if ($isVerbose) {
                    $io->success(sprintf('Listing sync is active for: %s', implode(', ', $activeChannels)));
                }
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $io->error('Failed to check listing sync status: ' . $e->getMessage());
            
            $this->logger->error('Failed to check listing sync status', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}