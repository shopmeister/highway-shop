<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\Logger;

use Doctrine\DBAL\Connection;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;

class SwagAmazonPayLoggerFactory implements SwagAmazonPayLoggerFactoryInterface
{
    protected string $rotatingFilePathPattern = '';

    protected Level $logLevel;

    protected Connection $connection;

    public function __construct(
        string     $rotatingFilePathPattern,
        Connection $connection
    )
    {
        $this->logLevel = Level::Warning;
        $this->rotatingFilePathPattern = $rotatingFilePathPattern;
        $this->connection = $connection;
    }

    public function setLogLevel(ConfigServiceInterface $configService): void
    {
        try {
            if ($configService->getPluginConfig(null, true)->getLoggingMode() === 'advanced') {
                $this->logLevel = Level::Debug;
            }
        } catch (ConfigValidationException $e) {
            $this->logLevel = Level::Debug;
        }
    }

    public function createLogger(string $filePrefix, ?int $fileRotationCount = null): LoggerInterface
    {
        $filepath = \sprintf($this->rotatingFilePathPattern, $filePrefix);

        $result = new Logger($filePrefix);
        $result->pushHandler(new RotatingFileHandler($filepath, $fileRotationCount ?? 14, $this->logLevel));
        $result->pushProcessor(new PsrLogMessageProcessor());

        return $result;
    }
}
