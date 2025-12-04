<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentNotification\Handler;

use AmazonPayApiSdkExtension\Client\Client;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\PaymentNotification\Struct\PaymentNotificationMessage;
use Swag\AmazonPay\Components\Transaction\TransactionService;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;

abstract readonly class AbstractPaymentNotificationHandler implements PaymentNotificationHandlerInterface
{
    public function __construct(
        protected EntityRepository        $orderTransactionRepository,
        protected EntityRepository        $paymentNotificationRepository,
        protected ClientProviderInterface $clientProvider,
        protected ConfigServiceInterface  $configService,
        protected TransactionService      $transactionService,
        protected LoggerInterface         $logger
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        PaymentNotificationMessage $notificationMessage,
        Context                    $context
    ): void
    {
        $this->insertNotificationRecord($notificationMessage, $context);
    }

    /**
     * Returns an order transaction by its charge-id.
     */
    protected function getOrderTransaction(string $chargeId, Context $context): ?OrderTransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociations([
            'order',
            'stateMachineState',
        ]);

        $criteria->addFilter(
            new EqualsFilter(\sprintf('customFields.%s', CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_ID), $chargeId)
        );

        return $this->orderTransactionRepository->search($criteria, $context)->first();
    }

    /**
     * Inserts a new PaymentNotificationEntity with the minimum required information into the database.
     * For beste practise use this function before processing the actual notification.
     */
    protected function insertNotificationRecord(
        PaymentNotificationMessage $notificationMessage,
        Context                    $context
    ): EntityWrittenContainerEvent
    {
        $rowData = [
            'transactionId' => null,
            'orderTransactionId' => null,
            'objectType' => $notificationMessage->getObjectType(),
            'objectId' => $notificationMessage->getObjectId(),
            'chargePermissionId' => $notificationMessage->getChargePermissionId(),
            'notificationId' => $notificationMessage->getNotificationId(),
            'notificationVersion' => $notificationMessage->getNotificationVersion(),
            'notificationType' => $notificationMessage->getNotificationType(),
            'processed' => false,
        ];

        return $this->paymentNotificationRepository->create([$rowData], $context);
    }

    /**
     * Updates an existing PaymentNotificationEntity record with further information.
     * For best practise use this function after processing the notification.
     *
     * @return EntityWrittenContainerEvent
     */
    protected function updateNotificationRecord(
        string  $notificationId,
        ?string $transactionId,
        bool    $processed,
        Context $context
    ): ?EntityWrittenContainerEvent
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('notificationId', $notificationId)
        );

        $existingPaymentNotificationId = $this->paymentNotificationRepository->searchIds($criteria, $context)->firstId();
        if ($existingPaymentNotificationId === null) {
            return null;
        }

        $rowData = [
            'id' => $existingPaymentNotificationId,
            'transactionId' => $transactionId,
            'orderTransactionId' => $transactionId,
            'processed' => $processed,
        ];

        return $this->paymentNotificationRepository->update([$rowData], $context);
    }

    protected function getClient(string $merchantId, Context $context): Client
    {
        $salesChannelId = null;

        $configEntity = $this->configService->getConfigEntityByMerchantId($merchantId, $context);

        $salesChannelId = $configEntity?->getSalesChannelId();

        return $this->clientProvider->getClient($salesChannelId);
    }
}
