<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Components\PaymentHandler;

use AmazonPayApiSdkExtension\Struct\ChargeAmount;
use AmazonPayApiSdkExtension\Struct\PaymentDetails;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession\CreateCheckoutSessionHydrator;
use Swag\AmazonPay\Components\Client\Hydrator\Request\UpdateCheckoutSession\UpdateCheckoutSessionHydratorInterface;
use Swag\AmazonPay\Components\Client\Service\Exception\ChargePaymentException;
use Swag\AmazonPay\Components\Client\Validation\Exception\PaymentDeclinedException;
use Swag\AmazonPay\Components\Client\Validation\Exception\ResponseValidationException;
use Swag\AmazonPay\Components\Client\Validation\ResponseValidatorInterface;
use Swag\AmazonPay\Components\Transaction\TransactionService;
use Swag\AmazonPay\Core\AmazonPay\SalesChannel\AmazonPayRoute;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionEntity;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;
use Swag\AmazonPay\Storefront\Controller\AmazonPurePaymentMethodController;
use Swag\AmazonPay\SwagAmazonPay;
use Swag\AmazonPay\Util\Helper\AmazonPayPaymentMethodHelperInterface;
use Swag\AmazonPay\Util\Util;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

readonly class AmazonPaymentHandler implements AsynchronousPaymentHandlerInterface
{
    public const REQUEST_PARAMETER_AMAZON_PAY_INITIALIZE_PURE_PAYMENT_URL = 'amazonPayInitializePurePaymentUrl';

    private ?SessionInterface $session;

    public function __construct(
        private ClientProviderInterface                $clientProvider,
        private UpdateCheckoutSessionHydratorInterface $updateCheckoutSessionHydrator,
        ?RequestStack                                  $requestStack,
        private ResponseValidatorInterface             $paymentProcessResponseValidator,
        private ResponseValidatorInterface             $paymentFinalizeResponseValidator,
        private EntityRepository                       $orderTransactionRepository,
        private TransactionService                     $transactionService,
        private LoggerInterface                        $logger,
        private AmazonPaymentHandlerInterface          $purePaymentMethodHandler,
        private OrderTransactionStateHandler           $orderTransactionStateHandler,
    )
    {
        try {
            $this->session = $requestStack->getSession();  // SW65 style
        } catch (Exception $e) {
            $this->session = null;
        }
    }

    public function pay(AsyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        $transactionId = $transaction->getOrderTransaction()->getId();
        $this->logger->debug('AmazonPaymentHandler::pay() called with transactionId: ' . $transactionId);

        if (method_exists($this->orderTransactionStateHandler, 'processUnconfirmed')) {
            try {
                $this->orderTransactionStateHandler->processUnconfirmed($transactionId, $salesChannelContext->getContext());
            } catch (Exception $e) {
                $this->logger->warning('AmazonPaymentHandler::pay() processUnconfirmed failed: ' . $e->getMessage());
            }
        }

        if (!$dataBag->has('amazonPayCheckoutId') || empty($dataBag->get('amazonPayCheckoutId'))) {
            $this->logger->debug('AmazonPaymentHandler::pay() switching to pure payment method handler for transactionId: ' . $transactionId);
            return $this->switchToPurePayment($transaction, $dataBag, $salesChannelContext);
        }

        $amazonPayCheckoutSessionId = $dataBag->get('amazonPayCheckoutId');
        $this->logger->debug('AmazonPaymentHandler::pay() validate address for transactionId: ' . $transactionId);
        try {
            $this->validateAddress($amazonPayCheckoutSessionId, $transaction->getOrderTransaction(), $salesChannelContext);
        } catch (Exception $e) {
            $this->logger->warning('AmazonPaymentHandler::pay() address not valid for transactionId ' . $transactionId . ': ' . $e->getMessage());
            return $this->switchToPurePayment($transaction, $dataBag, $salesChannelContext);
        }
        $this->logger->debug('AmazonPaymentHandler::pay() address validated for transactionId: ' . $transactionId);

        $this->setTransactionCustomFields(
            $transaction,
            $amazonPayCheckoutSessionId,
            null,
            null,
            $salesChannelContext->getContext()
        );

        $updatedSessionData = $this->updateCheckoutSessionHydrator->hydrate(
            $transaction,
            $salesChannelContext->getCurrency(),
            $salesChannelContext->getContext()
        );

        try {
            $client = $this->clientProvider->getClient($salesChannelContext->getSalesChannel()->getId());
            $checkoutSession = $client->updateCheckoutSession(
                $amazonPayCheckoutSessionId,
                $updatedSessionData,
                $this->clientProvider->getHeaders()
            );

            $this->paymentProcessResponseValidator->validateResponse($checkoutSession);

            if ($this->session) {
                // Remove the "on hold" checkout session id from the session.
                if ($this->session->has(SwagAmazonPay::CHECKOUT_SESSION_KEY)) {
                    $this->session->remove(SwagAmazonPay::CHECKOUT_SESSION_KEY);
                }
            }
            $this->logger->debug('AmazonPaymentHandler::pay() redirecting to: ' . $checkoutSession->getWebCheckoutDetails()->getAmazonPayRedirectUrl());

            return new RedirectResponse($checkoutSession->getWebCheckoutDetails()->getAmazonPayRedirectUrl());
        } catch (ResponseValidationException|Exception $exception) {
            $this->logPaymentProcessingError('Could not process payment:', $exception, [
                'checkoutSessionResponse' => $checkoutSession ?? ['not obtained'],
                'amazonPayCheckoutId' => $amazonPayCheckoutSessionId,
            ]);

            throw PaymentException::asyncProcessInterrupted($transaction->getOrderTransaction()->getId(), $exception->getMessage());
        }
    }

    protected function switchToPurePayment(AsyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): RedirectResponse{
        if ($salesChannelContext->getContext()->getSource() instanceof SalesChannelApiSource) {
            $this->logger->debug('AmazonPaymentHandler::switchToPurePayment() Headless switching to pure payment for transaction: ' . $transaction->getOrderTransaction()->getId());
            $redirectUrl = $dataBag->get(static::REQUEST_PARAMETER_AMAZON_PAY_INITIALIZE_PURE_PAYMENT_URL);
            if (!empty($redirectUrl)) {
                $redirectUrl .= (!str_contains($redirectUrl, '?') ? '?' : '&') . AmazonPayRoute::REQUEST_PARAMETER_ORDER_TRANSACTION_ID . '=' . $transaction->getOrderTransaction()->getId();
                $this->logger->debug('AmazonPaymentHandler::switchToPurePayment() Headless switching to pure payment redirect: ' . $redirectUrl);
                return new RedirectResponse($redirectUrl);
            }
        }
        $this->logger->debug('AmazonPaymentHandler::switchToPurePayment() switching to pure payment method handler for transactionId: ' . $transaction->getOrderTransaction()->getId());

        return $this->purePaymentMethodHandler->handleAmazonPayment($transaction, $dataBag, $salesChannelContext);
    }

    public function finalize(AsyncPaymentTransactionStruct $transaction, Request $request, SalesChannelContext $salesChannelContext): void
    {
        if ($request->query->has(CreateCheckoutSessionHydrator::CUSTOMER_CANCELLED_PARAMETER) && $request->query->getBoolean(CreateCheckoutSessionHydrator::CUSTOMER_CANCELLED_PARAMETER)) {
            throw PaymentException::customerCanceled($transaction->getOrderTransaction()->getId(), '');
        }
        // Initially fetch the checkoutSessionId from the custom fields
        $transactionCustomFields = $transaction->getOrderTransaction()->getCustomFields();
        if (isset($transactionCustomFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHECKOUT_ID])) {
            $amazonPayCheckoutSessionId = $transactionCustomFields[CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHECKOUT_ID];
        }

        // If the finalize request contains a checkoutSessionId use this one instead. (Used for after order pure payment flow)
        if ($request->query->has(AmazonPurePaymentMethodController::AMAZON_CHECKOUT_SESSION_ID_PARAMETER_KEY)) {
            $amazonPayCheckoutSessionId = $request->query->get(AmazonPurePaymentMethodController::AMAZON_CHECKOUT_SESSION_ID_PARAMETER_KEY);
        }

        // Store API
        if ($request->request->has(AmazonPayRoute::REQUEST_PARAMETER_AMAZON_CHECKOUT_SESSION_ID)) {
            $amazonPayCheckoutSessionId = $request->request->get(AmazonPayRoute::REQUEST_PARAMETER_AMAZON_CHECKOUT_SESSION_ID);
        }

        // Finally check that a checkoutSessionId was obtained in any way
        if (empty($amazonPayCheckoutSessionId)) {
            $this->logPaymentProcessingError('Could not finalize payment: No checkout session id found.', new Exception());
            throw PaymentException::asyncFinalizeInterrupted($transaction->getOrderTransaction()->getId(), 'Could not determine the Amazon Pay Checkout Session.');
        }

        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();

        try {
            $this->logger->debug('AmazonPaymentHandler::finalize() called with checkoutSessionId: ' . $amazonPayCheckoutSessionId);
            $client = $this->clientProvider->getClient($salesChannelId);
            $chargeAmount = (new ChargeAmount([
                'amount' => Util::round(
                    $transaction->getOrder()->getAmountTotal(),
                    AmazonPayPaymentMethodHelperInterface::DEFAULT_DECIMAL_PRECISION
                ),
                'currencyCode' => $salesChannelContext->getCurrency()->getIsoCode(), // TODO change to order currency
            ]));

            $paymentDetails = (new PaymentDetails())->setChargeAmount($chargeAmount);

            $checkoutSession = $client->completeCheckoutSession(
                $amazonPayCheckoutSessionId,
                $paymentDetails,
                $this->clientProvider->getHeaders()
            );

            $this->paymentFinalizeResponseValidator->validateResponse($checkoutSession);

            if (method_exists($this->orderTransactionStateHandler, 'reopen')) {
                try {
                    $this->orderTransactionStateHandler->reopen($transaction->getOrderTransaction()->getId(), $salesChannelContext->getContext());
                } catch (Exception $e) {
                    $this->logger->warning('AmazonPaymentHandler::finalize() reopen failed: ' . $e->getMessage());
                }
            }

            /** @var string|null $chargeId */
            $chargeId = $checkoutSession->getChargeId() ?? null;
            $chargePermissionId = $checkoutSession->getChargePermissionId() ?? null;

            $this->setTransactionCustomFields(
                $transaction,
                $amazonPayCheckoutSessionId,
                $chargeId,
                $chargePermissionId,
                $salesChannelContext->getContext()
            );

            if (!$chargeId) {
                return;
            }
            $chargePermission = $client->getChargePermission($chargePermissionId);
            $this->transactionService->persistAmazonPayTransaction($chargePermission, $salesChannelContext->getContext(), $transaction->getOrderTransaction());
            $chargePermissionEntity = $this->transactionService->getAmazonPayTransactionEntity(
                $chargePermissionId,
                AmazonPayTransactionEntity::TRANSACTION_TYPE_CHARGE_PERMISSION,
                $salesChannelContext->getContext(),
                true,
                $salesChannelId
            );
            $charge = $client->getCharge($chargeId);
            $this->transactionService->updateCharge($charge, $salesChannelContext->getContext(), $transaction->getOrderTransaction(), $chargePermissionEntity);
        } catch (ResponseValidationException|PaymentDeclinedException $exception) {
            $this->logPaymentProcessingError('Payment has been declined:', $exception, [
                'checkoutSessionResponse' => $checkoutSessionResponse ?? ['not obtained'],
                'amazonPayCheckoutId' => $amazonPayCheckoutSessionId,
            ]);

            $this->setTransactionCustomFieldsOnError(
                $transaction,
                $amazonPayCheckoutSessionId,
                $checkoutSession['reasonCode'] ?? 'Unknown',
                $checkoutSession['message'] ?? 'An unknown error occurred',
                $salesChannelContext->getContext()
            );

            throw PaymentException::asyncFinalizeInterrupted($transaction->getOrderTransaction()->getId(), $exception->getMessage());
        } catch (ChargePaymentException $exception) {
            $this->logPaymentProcessingError('Could not charge payment:', $exception, [
                'checkoutSessionResponse' => $checkoutSessionResponse ?? ['not obtained'],
                'amazonPayCheckoutId' => $amazonPayCheckoutSessionId,
            ]);
        } catch (Exception $exception) {
            $this->logPaymentProcessingError('Could not finalize payment:', $exception, [
                'checkoutSessionResponse' => $checkoutSessionResponse ?? ['not obtained'],
                'amazonPayCheckoutId' => $amazonPayCheckoutSessionId,
            ]);

            throw PaymentException::asyncFinalizeInterrupted($transaction->getOrderTransaction()->getId(), $exception->getMessage());
        }

        // Reset the AmazonPay related errors
        $this->setTransactionCustomFieldsOnError(
            $transaction,
            $amazonPayCheckoutSessionId,
            null,
            null,
            $salesChannelContext->getContext()
        );
    }


    public function purePaymentFinalize(string $orderTransactionId, Request $request, SalesChannelContext $salesChannelContext): array{
        try {
            $criteria = new Criteria([$orderTransactionId]);
            $criteria->setLimit(1);
            $criteria->addAssociation('order');

            /** @var OrderTransactionEntity|null $orderTransaction */
            $orderTransaction = $this->orderTransactionRepository->search($criteria, $salesChannelContext->getContext())->first();
            if ($orderTransaction === null) {
                throw PaymentException::invalidTransaction($orderTransactionId);
            }

            $order = $orderTransaction->getOrder();
            if ($order === null) {
                throw PaymentException::invalidTransaction($orderTransactionId);
            }

            $asyncPaymentTransactionStruct = new AsyncPaymentTransactionStruct(
                $orderTransaction,
                $order,
                ''
            );

            $this->finalize(
                $asyncPaymentTransactionStruct,
                $request,
                $salesChannelContext
            );
        } catch (Exception $e) {
            $logDetails = [
                'orderTransactionId' => $orderTransactionId,
                'exceptionMessage' => $e->getMessage(),
                'exceptionTrace' => $e->getTraceAsString(),
                'exceptionType' => get_class($e),
                'request' => $request,
            ];
            if ($e->getErrorCode() === PaymentException::PAYMENT_INVALID_TRANSACTION_ID) {
                $this->logger->error('Order transaction id is invalid in AmazonPaymentHandler::purePaymentFinalize()', $logDetails);
            } else {
                if ($e->getErrorCode() === PaymentException::PAYMENT_CUSTOMER_CANCELED_EXTERNAL) {
                    $this->orderTransactionStateHandler->cancel($orderTransactionId, $salesChannelContext->getContext());
                } else {
                    $this->logger->warning('An error occurred during finalizing in AmazonPaymentHandler::purePaymentFinalize()', $logDetails);
                    $this->orderTransactionStateHandler->fail($orderTransactionId, $salesChannelContext->getContext());
                }
            }
            throw $e;
        }
        return [
            'order'=>$order,
            'orderTransaction'=>$orderTransaction
        ];
    }

    /**
     * Sets the Amazon Pay specific custom fields to a transaction.
     */
    private function setTransactionCustomFields(AsyncPaymentTransactionStruct $transaction, string $checkoutSessionId, ?string $chargeId, ?string $chargePermissionId, Context $context): void
    {
        $customFields = [
            CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHECKOUT_ID => $checkoutSessionId,
            CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_ID => $chargeId,
            CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHARGE_PERMISSION_ID => $chargePermissionId,
        ];

        $existingCustomFields = $transaction->getOrderTransaction()->getCustomFields() ?? [];

        // In case that the cache kicks in update the current struct either to avoid any misbehavior when working with custom fields in later steps.
        $transaction->getOrderTransaction()->setCustomFields(
            \array_merge($existingCustomFields, $customFields)
        );

        $this->orderTransactionRepository->upsert([
            [
                'id' => $transaction->getOrderTransaction()->getId(),
                'customFields' => $customFields,
            ],
        ], $context);
    }

    /**
     * Sets the Amazon Pay specific custom fields to a transaction on process error.
     */
    private function setTransactionCustomFieldsOnError(
        AsyncPaymentTransactionStruct $transaction,
        string                        $checkoutSessionId,
        ?string                       $reasonCode,
        ?string                       $reasonDescription,
        Context                       $context,
    ): void
    {
        $customFields = [
            CustomFieldsInstaller::CUSTOM_FIELD_NAME_CHECKOUT_ID => $checkoutSessionId,
            CustomFieldsInstaller::CUSTOM_FIELD_NAME_ERROR_REASON_CODE => $reasonCode,
            CustomFieldsInstaller::CUSTOM_FIELD_NAME_ERROR_REASON_DESCRIPTION => $reasonDescription,
        ];

        $existingCustomFields = $transaction->getOrderTransaction()->getCustomFields() ?? [];

        // To avoid any misbehavior when working with custom fields in later steps.
        $transaction->getOrderTransaction()->setCustomFields(
            \array_merge($existingCustomFields, $customFields)
        );

        $this->orderTransactionRepository->upsert([
            [
                'id' => $transaction->getOrderTransaction()->getId(),
                'customFields' => $customFields,
            ],
        ], $context);
    }

    private function logPaymentProcessingError(string $message, Exception $exception, array $arguments = []): void
    {
        $this->logger->error(\sprintf($message . ' %s', $exception->getMessage()), $arguments);
    }

    private function validateAddress(mixed $amazonPayCheckoutSessionId, OrderTransactionEntity $getOrderTransaction, SalesChannelContext $salesChannelContext)
    {
        $order = $this->transactionService->getOrderFromOrderTransaction($getOrderTransaction, $salesChannelContext->getContext());
        $deliveries = $order->getDeliveries();
        if (empty($deliveries) || empty($deliveries->first())) {
            $this->logger->warning('No delivery found for order: ' . $order->getId());
            return;
        }
        $delivery = $deliveries->first();
        $orderShippingAddress = $delivery->getShippingOrderAddress();

        $checkoutSession = $this->clientProvider->getClient($salesChannelContext->getSalesChannel()->getId())->getCheckoutSession($amazonPayCheckoutSessionId);
        $address = $checkoutSession->getShippingAddress();
        if($address) {
            //only applies if not PayOnly
            if ($address->getCountryCode() !== $orderShippingAddress->getCountry()->getIso()) {
                throw new Exception('Checkout shipping address country code does not match');
            }
            if ($address->getPostalCode() !== $orderShippingAddress->getZipcode()) {
                throw new Exception('Checkout shipping address postal code does not match');
            }
            if ($address->getCity() !== $orderShippingAddress->getCity()) {
                throw new Exception('Checkout shipping address city does not match');
            }
            $addressLinesCombined = $address->getAddressLine1() . $address->getAddressLine2() . $address->getAddressLine3();
            if (stripos($addressLinesCombined, $orderShippingAddress->getStreet()) === false) {
                throw new Exception('Checkout shipping address street does not match');
            }
        }
    }
}
