<?php

declare(strict_types=1);


namespace Swag\AmazonPay\Components\Client\Service;

use AmazonPayApiSdkExtension\Struct\CaptureAmount;
use AmazonPayApiSdkExtension\Struct\Charge;
use AmazonPayApiSdkExtension\Struct\ChargeAmount;
use AmazonPayApiSdkExtension\Struct\Refund;
use AmazonPayApiSdkExtension\Struct\RefundAmount;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Client\Service\Exception\CancelPaymentException;
use Swag\AmazonPay\Components\Client\Service\Exception\ChargePaymentException;
use Swag\AmazonPay\Components\Client\Service\Exception\RefundPaymentException;
use Swag\AmazonPay\Components\Transaction\TransactionService;
use Swag\AmazonPay\Util\Util;

class PaymentActionService
{
    private TransactionService $transactionService;


    private ClientProviderInterface $clientProvider;
    private LoggerInterface $logger;


    public function __construct(
        TransactionService      $transactionService,
        ClientProviderInterface $clientProvider,
        LoggerInterface         $logger
    )
    {
        $this->transactionService = $transactionService;
        $this->clientProvider = $clientProvider;
        $this->logger = $logger;
    }

    public function capture(
        string  $chargeId,
        float   $amount,
        ?string $softDescriptor,
        ?string $currencyCode,
        Context $context
    ): Charge
    {
        $this->logger->debug('💰️ Capture charge: ' . $chargeId, [
            'amount' => $amount,
            'softDescriptor' => $softDescriptor,
            'currencyCode' => $currencyCode,
        ]);
        $this->extendPermissions($context);
        if (is_string($softDescriptor) && strlen($softDescriptor) > 16) {
            $softDescriptor = substr($softDescriptor, 0, 16);
        }
        $orderTransaction = null;
        try {
            $orderTransaction = $this->transactionService->getOrderTransactionByChargeId($chargeId, $context);
        } catch (Exception $e) {
            $this->logger->error('Could not find order transaction by chargeId: ' . $chargeId . ' - ' . $e->getMessage());
        }
        if ($orderTransaction === null) {
            throw new ChargePaymentException(new Exception('Invalid transaction on charge payment!'));
        }

        $salesChannelId = null;
        $order = $orderTransaction->getOrder();
        $orderCurrency = $order?->getCurrency();

        $salesChannelId = $order?->getSalesChannelId();

        try {
            $client = $this->clientProvider->getClient($salesChannelId);

            $captureAmount = (new CaptureAmount())
                ->setCurrencyCode($currencyCode)
                ->setAmount(Util::round($amount, Util::getPrecision($orderCurrency)));
            $captureCharge = (new Charge())
                ->setCaptureAmount($captureAmount)
                ->setSoftDescriptor($softDescriptor ?: null);


            $captureCharge = $client->captureCharge($chargeId, $captureCharge, $this->clientProvider->getHeaders());
            $this->transactionService->updateCharge($captureCharge, $context, $orderTransaction);
            return $captureCharge;
        } catch (Exception $ex) {
            throw new ChargePaymentException($ex);
        }
    }

    public function createCharge(string $chargePermissionId, Context $context, $amount = null, ?string $softDescriptor = null): Charge
    {
        $this->logger->debug('💰️ Create charge: ' . $chargePermissionId, [
            'amount' => $amount,
            'softDescriptor' => $softDescriptor,
        ]);
        $this->extendPermissions($context);
        $chargePermissionTransaction = $this->transactionService->getChargePermissionEntity($chargePermissionId, $context, true);
        if ($chargePermissionTransaction === null) {
            throw new Exception('Could not find charge permission transaction to create charge');
        }
        $salesChannelId = $chargePermissionTransaction->getOrder()->getSalesChannelId();
        $client = $this->clientProvider->getClient($salesChannelId);
        $chargePermission = $client->getChargePermission($chargePermissionId);

        $charge = (new Charge())
            ->setChargePermissionId($chargePermissionId)
            ->setChargeAmount(
                (new ChargeAmount())
                    ->setAmount($amount ?: $chargePermission->getLimits()->getAmountBalance()->getAmount())
                    ->setCurrencyCode($chargePermission->getPresentmentCurrency())
            );
        if ($softDescriptor) {
            $charge->setSoftDescriptor($softDescriptor);
        }
        $charge = $client->createCharge($charge);
        $this->transactionService->persistAmazonPayTransaction($charge, $context, $chargePermissionTransaction->getOrderTransaction(), $chargePermissionTransaction);
        return $charge;
    }

    public function refund(
        string  $chargeId,
        float   $amount,
        ?string $softDescriptor,
        ?string $currencyCode,
        Context $context
    ): Refund
    {
        $this->logger->debug('💰️ Refund: ' . $chargeId, [
            'amount' => $amount,
            'softDescriptor' => $softDescriptor,
            'currencyCode' => $currencyCode,
        ]);
        $this->extendPermissions($context);
        $orderTransaction = null;
        try {
            $orderTransaction = $this->transactionService->getOrderTransactionByChargeId($chargeId, $context);
        } catch (Exception $e) {
            $this->logger->error('Could not find order transaction by chargeId: ' . $chargeId . ' - ' . $e->getMessage());
        }

        if ($orderTransaction === null) {
            throw new RefundPaymentException(new Exception('Invalid transaction on refunding payment!'));
        }

        $order = $orderTransaction->getOrder();
        $orderCurrency = $order?->getCurrency();

        $salesChannelId = null;
        $salesChannelId = $order?->getSalesChannelId();

        if (is_string($softDescriptor) && strlen($softDescriptor) > 16) {
            $softDescriptor = substr($softDescriptor, 0, 16);
        }

        try {
            $client = $this->clientProvider->getClient($salesChannelId);
            $chargeEntity = $this->transactionService->getChargeEntity($chargeId, $context, true, $salesChannelId);

            if ($chargeEntity === null) {
                throw new RefundPaymentException(new Exception('Invalid chargeId on refunding payment!'));
            }

            $refund = (new Refund())
                ->setChargeId($chargeId)
                ->setRefundAmount(
                    (new RefundAmount())
                        ->setAmount(Util::round($amount, Util::getPrecision($orderCurrency)))
                        ->setCurrencyCode($currencyCode ?? $chargeEntity->getCurrency())
                );

            if (!empty($softDescriptor)) {
                $refund->setSoftDescriptor($softDescriptor);
            }

            $refundResponse = $client->createRefund($refund, $this->clientProvider->getHeaders());
            $chargeEntity = $this->transactionService->getChargeEntity($chargeId, $context, true, $salesChannelId);
            $this->transactionService->updateRefund(
                $refundResponse,
                $context,
                $orderTransaction,
                $chargeEntity,
                $salesChannelId
            );
            return $refundResponse;
        } catch (Exception $ex) {
            throw new RefundPaymentException($ex);
        }
    }

    public function cancel(
        string  $chargePermissionId,
        string  $cancellationReason,
        Context $context
    ): array
    {
        $this->logger->debug('❌ Cancel: ' . $chargePermissionId, [
            'cancellationReason' => $cancellationReason,
        ]);
        $this->extendPermissions($context);
        $chargePermissionEntity = $this->transactionService->getChargePermissionEntity($chargePermissionId, $context, true);
        if ($chargePermissionEntity === null) {
            throw new Exception('Could not find charge permission to cancel');
        }
        $order = $chargePermissionEntity->getOrder();
        $salesChannelId = null;
        $salesChannelId = $order?->getSalesChannelId();

        try {
            $client = $this->clientProvider->getClient($salesChannelId);
            $chargePermission = $client->closeChargePermission(
                $chargePermissionId,
                [
                    'closureReason' => $cancellationReason,
                    'cancelPendingCharges' => true,
                ]
            );
            $this->transactionService->persistAmazonPayTransaction($chargePermission, $context);
            return $chargePermission->toArray();
        } catch (Exception $ex) {
            throw new CancelPaymentException($ex);
        }
    }

    protected function extendPermissions(Context $context): void
    {
        try {
            Util::extendAdminPermissionsForAmazonPayTransactions($context);
        } catch (Exception $e) {
            $this->logger->error('Could not extend permissions: ' . $e->getMessage());
        }
    }
}
