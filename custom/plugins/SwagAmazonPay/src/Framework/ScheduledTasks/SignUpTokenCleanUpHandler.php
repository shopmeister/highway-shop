<?php declare(strict_types=1);

namespace Swag\AmazonPay\Framework\ScheduledTasks;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Swag\AmazonPay\DataAbstractionLayer\Entity\SignUpToken\SignUpTokenServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: SignUpTokenCleanUp::class)]
class SignUpTokenCleanUpHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository                             $scheduledTaskRepository,
        private readonly SignUpTokenServiceInterface $signUpTokenService,
        private readonly LoggerInterface             $logger
    )
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $this->logger->debug('Starting to clean up Amazon Pay SignUpTokens...');

        $this->signUpTokenService->cleanup(Context::createDefaultContext());

        $this->logger->debug('Finished cleaning up Amazon Pay SignUpTokens.');
    }
}
