<?php declare(strict_types=1);

namespace Swag\AmazonPay\Administration\Controller;

use AmazonPayApiSdkExtension\Struct\StatusDetails;
use Exception;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swag\AmazonPay\Components\Client\Service\PaymentActionService;
use Swag\AmazonPay\Components\Transaction\TransactionService;
use Swag\AmazonPay\DataAbstractionLayer\Entity\AmazonPayTransaction\AmazonPayTransactionEntity;
use Swag\AmazonPay\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AmazonPayOrderController extends AbstractController
{
    private PaymentActionService $paymentActionService;
    private TransactionService $transactionService;

    public function __construct(
        PaymentActionService $paymentActionService,
        TransactionService   $transactionService
    )
    {
        $this->paymentActionService = $paymentActionService;
        $this->transactionService = $transactionService;
    }

    /**
     * @throws Exception
     */
    #[Route(path: 'api/_action/swag-amazon-pay-order/payment-details/{chargePermissionId}', name: 'api.action.swag.amazon.pay.payment.details', defaults: ['_acl' => ['order.viewer']], methods: ['GET'])]
    public function fetchPaymentDetails(Request $request, Context $context, string $chargePermissionId): JsonResponse
    {
        $this->extendPermissions($context);
        $isRefreshData = $request->query->getBoolean('refreshData');
        if ($isRefreshData) {
            $this->transactionService->updateAllTransactionsFromApi($chargePermissionId, $context);
        }
        $return = [
            'hasPendingRefunds' => false,
            'isPartiallyCaptured' => false,
            'totalChargedAmount' => 0,
            'totalRefundedAmount' => 0,
            'totalRefundPendingAmount' => 0,
            'charges' => [],
            'transactionsCombined' => [],
        ];
        $chargePermissionEntity = $this->transactionService->getChargePermissionEntity($chargePermissionId, $context, true);
        if ($chargePermissionEntity === null) {
            $this->transactionService->migrateChargePermission($chargePermissionId, $context);
            $chargePermissionEntity = $this->transactionService->getChargePermissionEntity($chargePermissionId, $context, true);
        }

        if ($chargePermissionEntity === null) {
            return new JsonResponse($return);
        }

        $return['chargePermission'] = $chargePermissionEntity->toPublicArray();

        $charges = $this->transactionService->getTransactionChildren($chargePermissionEntity, $context);

        /** @var AmazonPayTransactionEntity $charge */
        foreach ($charges as $charge) {
            $return['charges'][$charge->getReference()] = [
                'details' => $charge->toPublicArray(),
                'maxRefundableAmount' => min($charge->getCapturedAmount() * 1.15, $charge->getCapturedAmount() + 75),
                'defaultRefundableAmount' => $charge->getCapturedAmount(),
                'refunds' => [],
            ];
            $return['transactionsCombined'][$charge->getReference()] = $charge->toPublicArray();

            if (in_array($charge->getStatus(), [StatusDetails::AUTHORIZED, StatusDetails::AUTHORIZATION_INITIATED])) {
                $return['totalChargedAmount'] += $charge->getAmount();
            } elseif ($charge->getStatus() === StatusDetails::CAPTURED) {
                $return['totalChargedAmount'] += $charge->getCapturedAmount();
            }

            $refunds = $this->transactionService->getTransactionChildren($charge, $context);
            /** @var AmazonPayTransactionEntity $refund */
            foreach ($refunds as $refund) {
                $return['charges'][$charge->getReference()]['refunds'][$refund->getId()] = [
                    'details' => $refund->toPublicArray(),
                ];
                if ($refund->getStatus() === StatusDetails::REFUND_INITIATED) {
                    $return['hasPendingRefunds'] = true;
                }
                if (in_array($refund->getStatus(), [StatusDetails::REFUND_INITIATED, StatusDetails::REFUNDED])) {
                    $return['charges'][$charge->getReference()]['maxRefundableAmount'] -= $refund->getAmount();
                    $return['charges'][$charge->getReference()]['defaultRefundableAmount'] -= $refund->getAmount();
                    $return[$refund->getStatus() === StatusDetails::REFUNDED ? 'totalRefundedAmount' : 'totalRefundPendingAmount'] += $refund->getAmount();
                }
                $return['transactionsCombined'][$refund->getReference()] = $refund->toPublicArray();
            }
            if ($return['charges'][$charge->getReference()]['defaultRefundableAmount'] < 0) {
                $return['charges'][$charge->getReference()]['defaultRefundableAmount'] = 0;
            }
            if ($return['charges'][$charge->getReference()]['maxRefundableAmount'] < 0) {
                $return['charges'][$charge->getReference()]['maxRefundableAmount'] = 0;
            }
        }

        return new JsonResponse($return);
    }

    #[Route(path: 'api/_action/swag-amazon-pay-order/charge-payment/{chargeId}', name: 'api.action.swag.amazon.pay.payment.charge', defaults: ['_acl' => ['order.editor']], methods: ['POST'])]
    public function chargePayment(RequestDataBag $dataBag, Context $context, string $chargeId): JsonResponse
    {
        if (!$dataBag->has('amount')) {
            throw RoutingException::missingRequestParameter('amount');
        }

        $amount = $dataBag->get('amount');
        $currencyCode = $dataBag->has('currencyCode') ? $dataBag->get('currencyCode') : 'EUR';
        $softDescriptor = $dataBag->get('softDescriptor');

        try {
            $charge = $this->paymentActionService->capture(
                $chargeId,
                $amount,
                $softDescriptor,
                $currencyCode,
                $context
            );

            return new JsonResponse(
                [
                    'chargeId' => $charge->getChargeId(),
                    'details' => $charge->toArray(),
                ]
            );
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    #[Route(path: 'api/_action/swag-amazon-pay-order/create-charge/{chargePermissionId}', name: 'api.action.swag.amazon.pay.payment.capture-from-charge-permission', defaults: ['_acl' => ['order.editor']], methods: ['POST'])]
    public function createCharge(RequestDataBag $dataBag, Context $context, string $chargePermissionId): JsonResponse
    {
        if (!$dataBag->has('amount')) {
            throw RoutingException::missingRequestParameter('amount');
        }

        $amount = $dataBag->get('amount');
        $softDescriptor = $dataBag->get('softDescriptor');

        try {
            $charge = $this->paymentActionService->createCharge(
                $chargePermissionId,
                $context,
                $amount,
                $softDescriptor
            );

            return new JsonResponse(
                [
                    'chargeId' => $charge->getChargeId(),
                    'details' => $charge->toArray(),
                ]
            );
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    #[Route(path: 'api/_action/swag-amazon-pay-order/refund-payment/{chargeId}', name: 'api.action.swag.amazon.pay.payment.refund', defaults: ['_acl' => ['order.editor']], methods: ['POST'])]
    public function refundPayment(RequestDataBag $dataBag, Context $context, string $chargeId): JsonResponse
    {
        if (!$dataBag->has('amount')) {
            throw RoutingException::missingRequestParameter('amount');
        }

        $amount = $dataBag->get('amount');
        $currencyCode = $dataBag->has('currencyCode') ? $dataBag->get('currencyCode') : 'EUR';
        $softDescriptor = $dataBag->get('softDescriptor');
        try {
            $refund = $this->paymentActionService->refund(
                $chargeId,
                $amount,
                $softDescriptor,
                $currencyCode,
                $context
            );

            return new JsonResponse([
                'refundId' => $refund->getRefundId(),
                'chargeId' => $refund->getChargeId(),
                'details' => $refund->toArray(),
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    #[Route(path: 'api/_action/swag-amazon-pay-order/cancel-payment/{chargePermissionId}', name: 'api.action.swag.amazon.pay.payment.close.charge.permission', defaults: ['_acl' => ['order.editor']], methods: ['POST'])]
    public function closeChargePermission(RequestDataBag $dataBag, Context $context, string $chargePermissionId): JsonResponse
    {
        if (!$dataBag->has('closureReason')) {
            throw RoutingException::missingRequestParameter('closureReason');
        }

        $closureReason = $dataBag->get('closureReason', '');
        try {
            $result = $this->paymentActionService->cancel(
                $chargePermissionId,
                $closureReason,
                $context
            );

            return new JsonResponse($result);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function extendPermissions(Context $context): void
    {
        try {
            Util::extendAdminPermissionsForAmazonPayTransactions($context);
        } catch (Exception) {
            //silent
        }
    }
}
