<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service;

use Psr\Log\LoggerInterface;

class Logger
{

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function logExecutionDuration(callable $callback, $message): void {
        $duration = $this->measureExecutionTime($callback);
        $this->logger->debug(sprintf($message, $duration));
    }

    private function measureExecutionTime(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        return round(($end - $start) * 1000, 2);
    }

}