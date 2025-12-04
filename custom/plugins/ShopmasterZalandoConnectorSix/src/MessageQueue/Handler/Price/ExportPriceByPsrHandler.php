<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Price;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\MessagePsrInterface;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Price\ExportPriceByPsrMessage;
use ShopmasterZalandoConnectorSix\Services\Export\Product\Price\ExportPriceService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: MessagePsrInterface::class, priority: 0)]
class ExportPriceByPsrHandler
{
    private Logger $logger;

    public function __construct(
        Logger                              $logger,
        readonly private ExportPriceService $exportPriceService
    )
    {
        $this->logger = $logger->withName('ExportPriceByPsrHandler');
    }

    /**
     * @param MessagePsrInterface $message
     * @return void
     */
    public function __invoke(MessagePsrInterface $message): void
    {
        if (!($message instanceof ExportPriceByPsrMessage)) {
            return;
        }
        try {
            if (!$message->getPsr()->count()) {
                return;
            }
            $this->logger->info('psr data', $message->getPsr()->toArray());
            $response = $this->exportPriceService->runExportProcessByPsr($message->getPsr());
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