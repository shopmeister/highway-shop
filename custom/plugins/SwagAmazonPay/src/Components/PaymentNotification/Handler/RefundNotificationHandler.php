<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentNotification\Handler;

use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\PaymentNotification\Exception\PaymentNotificationProcessException;
use Swag\AmazonPay\Components\PaymentNotification\Struct\PaymentNotificationMessage;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionEntity;

readonly class RefundNotificationHandler extends AbstractPaymentNotificationHandler
{

    /**
     * {@inheritdoc}
     */
    public function supports(string $objectType): bool
    {
        return $objectType === self::NOTIFICATION_OBJECT_TYPE_REFUND;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PaymentNotificationMessage $notificationMessage, Context $context): void
    {
        parent::process($notificationMessage, $context);

        $refundEntity = $this->transactionService->getAmazonPayTransactionEntity($notificationMessage->getObjectId(), AmazonPayTransactionEntity::TRANSACTION_TYPE_REFUND, $context, true);
        if ($refundEntity !== null) {
            $this->transactionService->updateTransactionFromApi($refundEntity, $context);
            $orderTransaction = $refundEntity->getOrderTransaction();
        } else {
            $chargePermissionId = $notificationMessage->getChargePermissionId();
            $chargePermissionEntity = $this->transactionService->getAmazonPayTransactionEntity($chargePermissionId, AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE_PERMISSION, $context, true);
            if ($chargePermissionEntity === null) {
                throw new PaymentNotificationProcessException(\sprintf('Could not find charge permission for refund by charge-permission-id [%s]', $chargePermissionId));
            }
            $orderTransaction = $chargePermissionEntity->getOrderTransaction();
            $salesChannelId = $chargePermissionEntity->getOrder()->getSalesChannelId();
            $client = $this->clientProvider->getClient($salesChannelId);

            $refund = $client->getRefund(
                $notificationMessage->getObjectId(),
                $this->clientProvider->getHeaders()
            );
            $chargeEntity = $this->transactionService->getAmazonPayTransactionEntity(
                $refund->getChargeId(),
                AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE,
                $context,
                true,
                $salesChannelId
            );
            if ($chargeEntity === null) {
                throw new PaymentNotificationProcessException(\sprintf('Could not find charge permission for refund by charge-permission-id [%s]', $chargePermissionId));
            }
            $this->transactionService->updateRefund(
                $refund,
                $context,
                $chargePermissionEntity->getOrderTransaction(),
                $chargeEntity,
                $salesChannelId
            );
        }

        $this->updateNotificationRecord(
            $notificationMessage->getNotificationId(),
            $orderTransaction->getId(),
            true,
            $context
        );
    }
}
