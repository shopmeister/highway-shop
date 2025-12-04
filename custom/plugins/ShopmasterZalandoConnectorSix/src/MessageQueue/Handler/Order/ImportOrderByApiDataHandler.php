<?php

namespace ShopmasterZalandoConnectorSix\MessageQueue\Handler\Order;

use Monolog\Logger;
use ShopmasterZalandoConnectorSix\MessageQueue\Message\Order\ImportOrderByApiDataMessage;
use ShopmasterZalandoConnectorSix\Services\Import\Order\ImportOrderService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: ImportOrderByApiDataMessage::class)]
class ImportOrderByApiDataHandler
{
    private Logger $logger;

    public function __construct(
        Logger                              $logger,
        readonly private ImportOrderService $importOrderService
    )
    {
        $this->logger = $logger->withName('ImportOrderByApiDataHandler');
    }

    /**
     * @param ImportOrderByApiDataMessage $message
     * @return void
     */
    public function __invoke(ImportOrderByApiDataMessage $message): void
    {
        try {
            $this->logger->info('data', $message->getOrderCollection()->toArray());
            $this->importOrderService->makeOrdersByCollection($message->getOrderCollection());
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }
}