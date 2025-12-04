<?php

declare(strict_types=1);

namespace Swag\AmazonPay\DataAbstractionLayer\EventListeners;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Swag\AmazonPay\Components\Client\Service\PaymentActionService;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Transaction\TransactionService;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionEntity;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;

readonly class AmazonPayTransitionListener
{
    public function __construct(
        private EntityRepository       $orderRepository,
        private PaymentActionService   $paymentActionService,
        private TransactionService     $transactionService,
        private ConfigServiceInterface $configService,
        private LoggerInterface        $logger,
    )
    {
    }

    public function onOrderStateChange(StateMachineStateChangeEvent $event): void
    {
        try {
            if (StateMachineStateChangeEvent::STATE_MACHINE_TRANSITION_SIDE_ENTER === $event->getTransitionSide()) {
                return;
            }
            $orderId = $event->getTransition()->getEntityId();
            $order = $this->getOrderById($orderId, $event->getContext());

            if (!$order) {
                return;
            }

            $pluginConfig = $this->configService->getPluginConfig($order->getSalesChannelId());
            $transactions = $order->getTransactions();

            if (null === $transactions) {
                return;
            }

            $transaction = $transactions->first();

            if (null === $transaction) {
                return;
            }

            $this->chargeOnOrderStateChange($order, $transaction, $pluginConfig, $event->getNextState(), $event->getContext());
            $this->refundOnOrderStateChange($order, $transaction, $pluginConfig, $event->getNextState(), $event->getContext());
        } catch (\Throwable $exception) {
            $this->logger->error('An error occurred while reacting to order state change', ['Exception' => $exception->getMessage()]);
        }
    }

    private function getOrderById(string $orderId, Context $context): ?OrderEntity
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('currency');

        return $this->orderRepository->search(
            $criteria,
            $context
        )->first();
    }

    private function refundOnOrderStateChange(OrderEntity $order, OrderTransactionEntity $transaction, AmazonPayConfigStruct $pluginConfig, StateMachineStateEntity $state, Context $context): void
    {
        if (empty($pluginConfig->getOrderRefundTriggerState()) || $state->getId() !== $pluginConfig->getOrderRefundTriggerState()) {
            return;
        }

        $chargePermissionEntity = $this->transactionService->getChargePermissionEntityOfOrder($order, $context);
        if (null === $chargePermissionEntity) {
            $customFields = $transaction->getCustomFields();

            if (!empty($customFields)) {
                $chargePermissionId = $customFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_PERMISSION_ID] ?? null;
                if ($chargePermissionId) {
                    $this->transactionService->migrateChargePermission($chargePermissionId, $context);
                    $chargePermissionEntity = $this->transactionService->getChargePermissionEntity($chargePermissionId, $context, true);
                }
            }
        }

        if (empty($chargePermissionEntity)) {
            return;
        }

        $this->logger->debug('start complete refund on order state change', ['order' => $order->getId()]);
        $charges = $this->transactionService->getTransactionChildren($chargePermissionEntity, $context);
        /** @var AmazonPayTransactionEntity $charge */
        foreach ($charges as $charge) {
            if (AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE !== $charge->getType()) {
                continue;
            }
            try {
                $this->logger->debug('start refund charge on order state change', ['charge' => $charge->getId()]);
                $this->paymentActionService->refund(
                    $charge->getReference(),
                    $charge->getAmount(),
                    '',
                    $order->getCurrency()->getIsoCode(),
                    $context
                );
            } catch (\Exception $e) {
                $this->logger->error('An error occurred while refunding charge on state change', ['charge' => $charge->getId(), 'exception' => $e->getMessage()]);
            }
        }
    }

    private function chargeOnOrderStateChange(OrderEntity $order, OrderTransactionEntity $transaction, AmazonPayConfigStruct $pluginConfig, StateMachineStateEntity $state, Context $context): void
    {
        if (AmazonPayConfigStruct::CHARGE_MODE_SHIPPING !== $pluginConfig->getChargeMode()) {
            return;
        }
        if ($state->getId() !== $pluginConfig->getOrderChargeTriggerState()) {
            return;
        }
        $customFields = $transaction->getCustomFields();

        if (empty($customFields) || empty($customFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_ID])) {
            return;
        }

        $chargeId = $customFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_ID];
        $softDescriptor = $this->configService->getSoftDescriptor($order->getSalesChannelId());
        $currency = $order->getCurrency();
        $currencyCode = $currency?->getIsoCode();

        $this->paymentActionService->capture(
            $chargeId,
            $order->getAmountTotal(),
            $softDescriptor,
            $currencyCode,
            $context
        );
    }
}
