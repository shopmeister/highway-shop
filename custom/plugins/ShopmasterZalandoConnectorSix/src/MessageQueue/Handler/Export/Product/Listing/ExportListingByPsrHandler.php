<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Export\Product\Listing;

use Psr\Log\LoggerInterface;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Export\Product\Listing\ExportListingByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Export\Product\Listing\ExportListingService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
class ExportListingByPsrHandler
{
    public function __construct(
        private ExportListingService $exportListingService,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ExportListingByPsrMessage $message): void
    {
        $this->logger->info('Processing listing export by PSR', [
            'isDryRun' => $message->isDryRun(),
            'psrProductCount' => $message->getPsr()->count()
        ]);

        try {
            $this->exportListingService->exportByPsr($message);
            
            $this->logger->info('Listing export by PSR completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Listing export by PSR failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}