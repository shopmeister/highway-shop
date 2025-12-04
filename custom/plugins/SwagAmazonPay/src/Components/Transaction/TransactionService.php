<?php

namespace Swag\AmazonPay\Components\Transaction;

use AmazonPayApiSdkExtension\Struct\Charge;
use AmazonPayApiSdkExtension\Struct\ChargePermission;
use AmazonPayApiSdkExtension\Struct\Refund;
use AmazonPayApiSdkExtension\Struct\StatusDetails;
use AmazonPayApiSdkExtension\Struct\StructBase;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\AmazonPay\Components\Client\ClientProvider;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Client\Service\PaymentActionService;
use Swag\AmazonPay\Components\Config\ConfigService;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\StateMachine\OrderTransactionStateHandlerInterface;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionCollection;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionEntity;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;
use Symfony\Component\DependencyInjection\Container;

class TransactionService
{
    private Container $container;
    private ?ClientProviderInterface $clientProvider;

    public function __construct(
        private readonly EntityRepository                      $amazonPayTransactionRepository,
        private readonly EntityRepository                      $orderTransactionRepository,
        private readonly EntityRepository                      $orderRepository,
        private readonly OrderTransactionStateHandlerInterface $orderTransactionStateHandler,
        private readonly ConfigService                         $configService,
        private readonly LoggerInterface                       $logger)
    {
    }

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function getOrderFromOrderTransaction(OrderTransactionEntity $orderTransaction, ?Context $context = null, bool $requireFullAssociations = false): ?OrderEntity
    {
        if (empty($orderTransaction->getOrderId())) {
            return null;
        }
        if(!empty($orderTransaction->getOrder()) && !$requireFullAssociations) {
            return $orderTransaction->getOrder();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $orderTransaction->getOrderId()));
        $criteria->addAssociations([
            'deliveries.shippingOrderAddress.country',
            'addresses.country',
            'orderCustomer.customer',
            'currency',
        ]);

        $searchResult = $this->orderRepository->search($criteria, $context ?? Context::createDefaultContext());
        return $searchResult->first();

    }

    public function updateCharge(Charge $charge, ?Context $context = null, ?OrderTransactionEntity $orderTransaction = null, $parentTransaction = null, ?string $salesChannelId = null): void
    {
        $this->logger->debug('★ TransactionService::updateCharge', [$charge->toArray()]);
        $context = $context ?? Context::createDefaultContext();
        $this->persistAmazonPayTransaction($charge, $context, $orderTransaction, $parentTransaction);
        if ($salesChannelId === null && $orderTransaction !== null) {
            $salesChannelId = $orderTransaction->getOrder()->getSalesChannelId();
        }
        $chargeTransaction = $this->getChargeTransaction($charge, $context, $salesChannelId);
        if (empty($orderTransaction) && $chargeTransaction->getOrderTransactionId()) {
            $orderTransaction = $this->getOrderTransaction($chargeTransaction->getOrderTransactionId(), $context);
        }

        if ($orderTransaction) {

            if ($chargeTransaction->getStatus() === StatusDetails::CAPTURED) {
                $this->logger->debug('★ TransactionHelper::updateCharge - captured');
                if (!$chargeTransaction->getAdminInformed()) {
                    $this->logger->debug('★ TransactionHelper::updateCharge - captured - attempt to set status');
                    $isComplete = $charge->getCaptureAmount()->getAmount() >= $orderTransaction->getAmount()->getTotalPrice();
                    if ($isComplete) {
                        $this->logger->debug('★ TransactionHelper::updateCharge - captured - paid fully');
                        $this->orderTransactionStateHandler->pay($orderTransaction, $context);
                    } else {
                        $this->logger->debug('★ TransactionHelper::updateCharge - captured - paid partially');
                        $this->orderTransactionStateHandler->payPartially($orderTransaction, $context);
                    }
                    $this->amazonPayTransactionRepository->upsert([
                        [
                            'id' => $chargeTransaction->getId(),
                            'adminInformed' => true,
                        ],
                    ], $context);
                } else {
                    $this->logger->debug('★ TransactionHelper::updateCharge - captured - status has been set previously');
                }
            } elseif ($chargeTransaction->getStatus() === StatusDetails::AUTHORIZED) {
                $this->logger->debug('★ TransactionHelper::updateCharge - authorized');
                $this->orderTransactionStateHandler->authorize($orderTransaction, $context);
            } elseif ($chargeTransaction->getStatus() === StatusDetails::DECLINED) {
                $this->logger->debug('★ TransactionHelper::updateCharge - declined');
                $this->orderTransactionStateHandler->cancel($orderTransaction, $context);
            }

            if ($chargeTransaction->getStatus() === StatusDetails::AUTHORIZED) {
                $salesChannelId = $chargeTransaction->getOrder()?->getSalesChannelId();
                $config = $this->configService->getPluginConfig($salesChannelId);
                if ($config->getChargeMode() === AmazonPayConfigStruct::CHARGE_MODE_DIRECT) {
                    $this->doAutoCapture($chargeTransaction, $orderTransaction, $context);
                }
            }
        }


        if ($chargePermission = $chargeTransaction->getParent()) {
            $this->updateTransactionFromApi($chargePermission, $context);
        }
    }


    public function getOrderTransactionByChargeId(string $chargeId, Context $context, ?string $salesChannelId = null): ?OrderTransactionEntity
    {
        $chargeTransaction = $this->getAmazonPayTransactionEntity($chargeId, AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE, $context, true, $salesChannelId);
        $orderTransaction = null;
        if ($chargeTransaction !== null && !empty($chargeTransaction->getOrderTransactionId())) {
            $orderTransaction = $this->getOrderTransaction($chargeTransaction->getOrderTransactionId(), $context);
        }
        return $orderTransaction;
    }

    public function getOrderTransaction(string $orderTransactionId, Context $context): ?OrderTransactionEntity
    {
        return $this->orderTransactionRepository->search(
            (new Criteria([$orderTransactionId]))
                ->addAssociation('order'),
            $context
        )->first();
    }

    protected function doAutoCapture(AmazonPayTransactionEntity $chargeEntity, OrderTransactionEntity $orderTransaction, Context $context): void
    {
        $salesChannelId = $orderTransaction->getOrder()->getSalesChannelId();
        $this->logger->debug('start auto capture for ' . $chargeEntity->getReference(), ['orderTransaction' => $orderTransaction->getId(), 'orderNumber' => $orderTransaction->getOrder()->getOrderNumber()]);
        if ($salesChannelId) {
            /** @var PaymentActionService $paymentActionService */
            $paymentActionService = $this->container->get(PaymentActionService::class);
            try {
                $softDescriptor = $this->configService->getSoftDescriptor($salesChannelId);
                $paymentActionService->capture($chargeEntity->getReference(), $chargeEntity->getAmount(), $softDescriptor, $chargeEntity->getCurrency(), $context);
            } catch (Exception $e) {
                $this->logger->debug('capture failed: ' . $e->getMessage());
            }
        }
    }

    public function persistAmazonPayTransaction(StructBase $transactionStruct, Context $context, ?OrderTransactionEntity $orderTransaction = null, $parentTransaction = null, ?string $salesChannelId = null): void
    {
        if ($salesChannelId === null && $orderTransaction !== null) {
            $salesChannelId = $this->getSalesChannelIdFromOrderTransaction($orderTransaction);
        }
        if ($salesChannelId === null && $parentTransaction !== null) {
            $salesChannelId = $this->getSalesChannelIdFromAmazonPayTransaction($parentTransaction, $context);
        }

        if ($transactionStruct instanceof ChargePermission) {
            $transaction = $this->getChargePermissionTransaction($transactionStruct, $context, $salesChannelId);
        } elseif ($transactionStruct instanceof Charge) {
            $transaction = $this->getChargeTransaction($transactionStruct, $context, $salesChannelId);
        } elseif ($transactionStruct instanceof Refund) {
            $transaction = $this->getRefundTransaction($transactionStruct, $context, $salesChannelId);
        }

        if (!isset($transaction)) {
            throw new Exception('Invalid Transaction Type ' . get_class($transactionStruct));
        }

        if ($orderTransaction) {
            $transaction->setOrderTransaction($orderTransaction);
            $order = $this->getOrderFromOrderTransaction($orderTransaction, $context);
            if ($order) {
                $transaction->setOrder($order);
            }
        }

        if ($parentTransaction) {
            $transaction->setParent($parentTransaction);
        }
        $this->amazonPayTransactionRepository->upsert([
            $transaction->serializeForUpdate(),
        ], $context);
    }

    protected function getChargePermissionTransaction(ChargePermission $chargePermission, ?Context $context = null, ?string $salesChannelId = null): AmazonPayTransactionEntity
    {
        return $this->getAmazonPayTransactionEntity(
            $chargePermission->getChargePermissionId(),
            AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE_PERMISSION,
            $context,
            false,
            $salesChannelId
        )
            ->setCurrency($chargePermission->getLimits()->getAmountLimit()->getCurrencyCode())
            ->setAmount($chargePermission->getLimits()->getAmountLimit()->getAmount())
            ->setCapturedAmount($chargePermission->getLimits()->getAmountLimit()->getAmount() - $chargePermission->getLimits()->getAmountBalance()->getAmount())
            ->setStatus($chargePermission->getStatusDetails()->getState())
            ->setTime(new \DateTime($chargePermission->getCreationTimestamp()))
            ->setExpiration(new \DateTime($chargePermission->getExpirationTimestamp()));
    }

    public function getChargePermissionEntityOfOrder(OrderEntity $order, Context $context): ?AmazonPayTransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $criteria->addFilter(new EqualsFilter('type', AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE_PERMISSION));
        $criteria->setLimit(1);
        $searchResult = $this->amazonPayTransactionRepository->search($criteria, $context);
        return $searchResult->first();
    }

    // needs either salesChannelId or onlyExisting === true
    public function getChargePermissionEntity(string $chargePermissionId, ?Context $context = null, bool $onlyExisting = false, ?string $salesChannelId = null): ?AmazonPayTransactionEntity
    {
        return $this->getAmazonPayTransactionEntity($chargePermissionId, AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE_PERMISSION, $context, $onlyExisting, $salesChannelId);
    }

    public function getChargeEntity(string $chargeId, ?Context $context = null, bool $onlyExisting = false, ?string $salesChannelId = null): ?AmazonPayTransactionEntity
    {
        return $this->getAmazonPayTransactionEntity($chargeId, AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE, $context, $onlyExisting, $salesChannelId);
    }

    public function getTransactionChildren(AmazonPayTransactionEntity $chargePermission, ?Context $context): ?AmazonPayTransactionCollection
    {
        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('parentId', $chargePermission->getId()));

        $searchResult = $this->amazonPayTransactionRepository->search($criteria, $context);
        return $searchResult->getEntities();
    }

    public function getAmazonPayTransactionEntityById(string $transactionId, Context $context): ?AmazonPayTransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $transactionId));
        $searchResult = $this->amazonPayTransactionRepository->search($criteria, $context);
        return $searchResult->first();

    }

    // needs either salesChannelId or onlyExisting === true
    public function getAmazonPayTransactionEntity(string $reference, string $type, ?Context $context = null, bool $onlyExisting = false, ?string $salesChannelId = null): ?AmazonPayTransactionEntity
    {
        if ($context === null) {
            $context = Context::createDefaultContext();
        }
        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('reference', $reference))
            ->addFilter(new EqualsFilter('type', $type));

        $searchResult = $this->amazonPayTransactionRepository->search($criteria, $context);

        if ($searchResult->count() > 0) {
            return $searchResult->first();
        } elseif (!$onlyExisting) {
            //TODO derive sales channel id from parent transaction
            try {
                $configuration = $this->configService->getPluginConfig($salesChannelId);
                return (new AmazonPayTransactionEntity())
                    ->setReference($reference)
                    ->setType($type)
                    ->setMerchantId($configuration->getMerchantId())
                    ->setMode($configuration->isSandboxActive() ? 'sandbox' : 'live');
            } catch (Exception $e) {
                $this->logger->error('Unable to create transaction entity: ' . $e->getMessage());
                return null;
            }
        } else {
            return null;
        }
    }

    protected function getChargeTransaction(Charge $charge, ?Context $context = null, ?string $salesChannelId = null): AmazonPayTransactionEntity
    {
        $chargeTransaction = $this->getAmazonPayTransactionEntity(
            $charge->getChargeId(),
            AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE,
            $context,
            false,
            $salesChannelId)
            ->setAmount((float)$charge->getChargeAmount()->getAmount())
            ->setCurrency($charge->getChargeAmount()->getCurrencyCode())
            ->setStatus($charge->getStatusDetails()->getState())
            ->setTime(new \DateTime($charge->getCreationTimestamp()))
            ->setExpiration(new \DateTime($charge->getExpirationTimestamp()));

        if ($charge->getCaptureAmount()) {
            $chargeTransaction->setCapturedAmount((float)$charge->getCaptureAmount()->getAmount());
        }
        if ($charge->getRefundedAmount()) {
            $chargeTransaction->setRefundedAmount((float)$charge->getRefundedAmount()->getAmount());
        }

        return $chargeTransaction;
    }

    protected function getRefundTransaction(Refund $refund, ?Context $context = null, ?string $salesChannelId = null): ?AmazonPayTransactionEntity
    {
        return $this->getAmazonPayTransactionEntity(
            $refund->getRefundId(),
            AmazonPayTransactionEntity::TRANSACTION_TYPE_REFUND,
            $context,
            false,
            $salesChannelId
        )
            ->setAmount((float)$refund->getRefundAmount()->getAmount())
            ->setCurrency($refund->getRefundAmount()->getCurrencyCode())
            ->setStatus($refund->getStatusDetails()->getState())
            ->setTime(new \DateTime($refund->getCreationTimestamp()));
    }

    public function updateRefund(Refund $refund, ?Context $context = null, ?OrderTransactionEntity $orderTransaction = null, $parentTransaction = null, ?string $salesChannelId = null): void
    {
        $this->logger->debug('★ TransactionService::updateRefund', [$refund->toArray()]);
        $context = $context ?? Context::createDefaultContext();

        if ($salesChannelId === null && $orderTransaction !== null) {
            $salesChannelId = $orderTransaction->getOrder()->getSalesChannelId();
        }

        $this->persistAmazonPayTransaction($refund, $context, $orderTransaction, $parentTransaction, $salesChannelId);
        $refundTransaction = $this->getRefundTransaction($refund, $context, $salesChannelId);
        $orderTransaction = $refundTransaction->getOrderTransaction();

        if ($orderTransaction) {
            $this->storeRefundId($refundTransaction->getReference(), $orderTransaction, $context); //legacy
        }

        if ($refundTransaction->getStatus() === StatusDetails::REFUNDED && $orderTransaction) {
            if (!$refundTransaction->getAdminInformed()) {
                $isComplete = $refund->getRefundAmount()->getAmount() >= $orderTransaction->getAmount()->getTotalPrice();
                if ($isComplete) {
                    $this->orderTransactionStateHandler->refund($orderTransaction, $context);
                } else {
                    $this->orderTransactionStateHandler->refundPartially($orderTransaction, $context);
                }
                $this->amazonPayTransactionRepository->upsert([
                    [
                        'id' => $refundTransaction->getId(),
                        'adminInformed' => true,
                    ],
                ], $context);
            }
        }
    }

    protected function storeRefundId(string $refundId, OrderTransactionEntity $orderTransaction, Context $context): void
    {
        $this->orderTransactionRepository->upsert([
            [
                'id' => $orderTransaction->getId(),
                'customFields' => [CustomFieldsInstaller::CUSTOM_FIELD_NAME_LAST_REFUND_ID => $refundId],
            ],
        ], $context);
    }

    /**
     * @throws Exception
     */
    public function updateAllTransactionsFromApi(string $chargePermissionId, Context $context): void
    {
        $this->logger->debug('⭮⭮⭮ update all transactions from api ' . $chargePermissionId);
        $chargePermission = $this->getChargePermissionEntity($chargePermissionId, $context, true);
        if ($chargePermission === null) {
            throw new Exception('Could not find charge permission for update: ' . $chargePermissionId);
        }
        $this->updateTransactionFromApi($chargePermission, $context);
        foreach ($this->getTransactionChildren($chargePermission, $context) as $charge) {
            $this->updateTransactionFromApi($charge, $context);
            foreach ($this->getTransactionChildren($charge, $context) as $refund) {
                $this->updateTransactionFromApi($refund, $context);
            }
        }
    }

    public function updateTransactionFromApi(AmazonPayTransactionEntity $amazonPayTransaction, Context $context): void
    {
        $this->logger->debug('⭮ update transaction from api ' . $amazonPayTransaction->getReference());
        $order = $amazonPayTransaction->getOrder();
        $salesChannelId = $order?->getSalesChannelId();
        try {
            $pluginConfig = $this->configService->getPluginConfig($salesChannelId);
            if ($transactionMode = $amazonPayTransaction->getMode()) {
                $pluginConfig->setIsSandboxActive($transactionMode === 'sandbox');
            }
            $client = $this->getClientProvider()->getClient($salesChannelId, null, $pluginConfig);

            if ($amazonPayTransaction->getType() === AmazonPayTransactionEntity::TRANSACTION_TYPE_REFUND) {
                $refund = $client->getRefund($amazonPayTransaction->getReference());
                $this->updateRefund($refund, $context, null, null, $salesChannelId);
            } elseif ($amazonPayTransaction->getType() === AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE) {
                $charge = $client->getCharge($amazonPayTransaction->getReference());
                $this->updateCharge($charge, $context);
            } elseif ($amazonPayTransaction->getType() === AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE_PERMISSION) {
                $chargePermission = $client->getChargePermission($amazonPayTransaction->getReference());
                $this->persistAmazonPayTransaction($chargePermission, $context);
            }
        } catch (Exception $e) {
            $this->logger->error('Unable to update transaction from api: ' . $e->getMessage(), ['trace' => $e->getTrace(), 'transaction' => $amazonPayTransaction->jsonSerialize()]);
        }
    }

    protected function getClientProvider(): ClientProviderInterface
    {
        if (empty($this->clientProvider)) {
            $this->clientProvider = $this->container->get(ClientProvider::class);
        }
        return $this->clientProvider;
    }


    public function doCron(): void
    {
        $this->logger->debug('💻 cron');
        $context = Context::createDefaultContext();
        foreach ($this->getOpenTransactions($context)->getElements() as $transaction) {
            try {
                $this->updateTransactionFromApi($transaction, $context);
            } catch (Exception $e) {
                $this->logger->error('Unable to update transaction in cron: ' . $e->getMessage(), ['trace' => $e->getTrace(), 'transaction' => $transaction->jsonSerialize()]);
            }
        }
    }

    public function getOpenTransactions(Context $context): ?AmazonPayTransactionCollection
    {
        $statusCollection = [
            StatusDetails::REFUND_INITIATED,
            StatusDetails::OPEN,
            StatusDetails::AUTHORIZATION_INITIATED,
            StatusDetails::NON_CHARGEABLE,
            StatusDetails::CHARGEABLE,
        ];
        $pluginConfig = $this->configService->getPluginConfig(null, true); //get from main config only - might reconsider this
        if ($pluginConfig->getChargeMode() !== AmazonPayConfigStruct::CHARGE_MODE_DIRECT) {
            $statusCollection[] = StatusDetails::AUTHORIZED;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('status', $statusCollection));
        return $this->amazonPayTransactionRepository->search($criteria, $context)->getEntities();
    }

    public function migrateChargePermission(string $chargePermissionId, Context $context): void
    {
        $chargePermissionEntity = $this->getChargePermissionEntity($chargePermissionId, $context, true);
        if ($chargePermissionEntity) {
            return;
        }
        $criteria = new Criteria();
        $criteria->addAssociation('order');
        $criteria->addFilter(
            new EqualsFilter(
                \sprintf('customFields.%s', CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_PERMISSION_ID),
                $chargePermissionId
            )
        );
        /** @var OrderTransactionEntity $orderTransaction */
        $orderTransaction = $this->orderTransactionRepository->search($criteria, $context)->first();
        if (!$orderTransaction) {
            return;
        }
        $order = $orderTransaction->getOrder();
        $salesChannelId = $order->getSalesChannelId();

        $client = $this->getClientProvider()->getClient($salesChannelId);
        $chargePermission = $client->getChargePermission($chargePermissionId);
        $this->persistAmazonPayTransaction($chargePermission, $context, $orderTransaction);
        $chargePermissionEntity = $this->getChargePermissionEntity($chargePermissionId, $context, false, $salesChannelId);

        $orderTransactionCustomFields = $orderTransaction->getCustomFields();
        if (is_array($orderTransactionCustomFields)) {
            if (isset($orderTransactionCustomFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_ID])) {
                $chargeId = $orderTransactionCustomFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_ID];
                $charge = $client->getCharge($chargeId);
                $this->persistAmazonPayTransaction($charge, $context, $orderTransaction, $chargePermissionEntity);
                $chargeEntity = $this->getChargeEntity($chargeId, $context, false, $salesChannelId);
                if (isset($orderTransactionCustomFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_LAST_REFUND_ID])) {
                    $refundId = $orderTransactionCustomFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_LAST_REFUND_ID];
                    $refund = $client->getRefund($refundId);
                    $this->persistAmazonPayTransaction($refund, $context, $orderTransaction, $chargeEntity);
                }
            }
        }
    }

    protected function getSalesChannelIdFromOrderTransaction(OrderTransactionEntity $orderTransaction): ?string
    {
        $order = $orderTransaction->getOrder();
        return $order?->getSalesChannelId();
    }

    protected function getSalesChannelIdFromAmazonPayTransaction(AmazonPayTransactionEntity $transaction, Context $context): ?string
    {
        $order = $transaction->getOrder();
        if ($order) {
            return $order->getSalesChannelId();
        }
        if ($parentTransaction = $transaction->getParent()) {
            //get to hydrate order etc
            $parentTransaction = $this->getAmazonPayTransactionEntityById($parentTransaction->getId(), $context);
            return $this->getSalesChannelIdFromAmazonPayTransaction($parentTransaction, $context);
        }
        return null;
    }
}
