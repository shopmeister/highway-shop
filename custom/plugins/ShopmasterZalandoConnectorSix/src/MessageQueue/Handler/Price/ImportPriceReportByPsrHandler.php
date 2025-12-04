<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Price;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Price\ImportPriceReportByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Import\Product\PriceReport\ImportPriceReportService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: MessagePsrInterface::class, priority: 0)]
class ImportPriceReportByPsrHandler
{
    private Logger $logger;

    public function __construct(
        Logger                                    $logger,
        readonly private ImportPriceReportService $importPriceReportService
    )
    {
        $this->logger = $logger->withName('ImportPriceReportByPsrHandler');
    }


    /**
     * @param MessagePsrInterface $message
     * @return void
     */
    public function __invoke(MessagePsrInterface $message): void
    {
        if (!($message instanceof ImportPriceReportByPsrMessage)) {
            return;
        }
        if (!$message->getPsr()->count()) {
            return;
        }
        $this->logger->info('psr data', $message->getPsr()->toArray());
        $this->importPriceReportService->runImportProcessByPsr($message->getPsr());
    }

}