<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Stock;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Stock\ExportStockByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Export\Product\Stock\ExportStockService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: MessagePsrInterface::class, priority: 0)]
class ExportStockByPsrHandler
{
    private Logger $logger;

    public function __construct(
        readonly private ExportStockService $exportStockService,
        Logger                              $logger
    )
    {
        $this->logger = $logger->withName('ExportStockByPsrHandler');
    }

    /**
     * @param MessagePsrInterface $message
     * @return void
     */
    public function __invoke(MessagePsrInterface $message): void
    {
        if (!($message instanceof ExportStockByPsrMessage)) {
            return;
        }
        try {
            $this->logger->info('psr data', $message->getPsr()->toArray());
            $response = $this->exportStockService->runExportProcessByPsr($message->getPsr());
            if (!$response) {
                $this->logger->info('nothing for update');
            } else {
                $this->logger->info('success', $response->getContentArray());
            }

        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }

}