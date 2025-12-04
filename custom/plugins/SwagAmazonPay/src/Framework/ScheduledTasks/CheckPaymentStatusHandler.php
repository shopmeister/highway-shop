<?php declare(strict_types=1);

namespace Swag\AmazonPay\Framework\ScheduledTasks;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Swag\AmazonPay\Util\Helper\TransactionRepositoryHelperInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CheckPaymentStatus::class)]
class CheckPaymentStatusHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository                                      $scheduledTaskRepository,
        private readonly TransactionRepositoryHelperInterface $transactionRepositoryHelper,
        private readonly OrderTransactionStateHandler         $orderTransactionStateHandler,
        private readonly LoggerInterface                      $logger
    )
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $this->logger->debug('Starting to check for invalid amazon pay transactions...');

        $context = Context::createDefaultContext();

        $transactionIdResult = $this->transactionRepositoryHelper->getInvalidTransactions($context);
        $transactionIds = $transactionIdResult->getIds();

        if (empty($transactionIds)) {
            $this->logger->debug('No invalid transactions could be found.');

            return;
        }

        foreach ($transactionIds as $transactionId) {
            if (!\is_string($transactionId)) {
                continue;
            }

            try {
                $this->logger->debug(\sprintf('Set payment status for transaction %s to cancelled, due to incomplete checkout process.', $transactionId));

                $this->orderTransactionStateHandler->cancel($transactionId, $context);
            } catch (\Throwable $e) {
                $this->logger->error('Error cancelling transaction: '.$e->getMessage(), ['transactionId' => $transactionId ?: 'unknown transactionId']);
            }
        }

        $this->logger->debug('Finished checking for incomplete amazon pay transactions.');
    }
}
