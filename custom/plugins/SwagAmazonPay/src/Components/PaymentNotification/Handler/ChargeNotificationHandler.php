<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentNotification\Handler;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\PaymentNotification\Exception\PaymentNotificationProcessException;
use Swag\AmazonPay\Components\PaymentNotification\Struct\PaymentNotificationMessage;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionEntity;

readonly class ChargeNotificationHandler extends AbstractPaymentNotificationHandler
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $objectType): bool
    {
        return $objectType === self::NOTIFICATION_OBJECT_TYPE_CHARGE;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PaymentNotificationMessage $notificationMessage, Context $context): void
    {
        parent::process($notificationMessage, $context);
        $i = 0;
        while (true) {
            $chargeEntity = $this->transactionService->getAmazonPayTransactionEntity(
                $notificationMessage->getObjectId(),
                AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE,
                $context,
                true
            );
            if ($chargeEntity !== null) {
                break;
            } elseif ($i < 5) {
                $this->logger->debug('🛈 race condition catcher iteration ' . $i, ['chargeId' => $notificationMessage->getObjectId()]);
                usleep(pow(2, $i) * 100000);
            } else {
                break;
            }
            $i++;
        }

        if ($chargeEntity === null) {
            throw new PaymentNotificationProcessException(\sprintf('Could not find charge by charge-id [%s]', $notificationMessage->getObjectId()));
        }
        $this->transactionService->updateTransactionFromApi($chargeEntity, $context);

        $this->updateNotificationRecord(
            $notificationMessage->getNotificationId(),
            $chargeEntity->getOrderTransaction()?->getId(),
            true,
            $context
        );
    }
}
