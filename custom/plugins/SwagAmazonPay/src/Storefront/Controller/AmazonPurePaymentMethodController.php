<?php declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonPurePaymentExtension;
use Swag\AmazonPay\Storefront\Page\Extension\ExtensionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class AmazonPurePaymentMethodController extends StorefrontController
{
    public const AMAZON_CHECKOUT_SESSION_ID_PARAMETER_KEY = 'amazonCheckoutSessionId';

    private ExtensionService $extensionService;

    private EntityRepository $orderTransactionRepository;

    private AsynchronousPaymentHandlerInterface $amazonPaymentHandler;

    private RouterInterface $router;

    private OrderTransactionStateHandler $transactionStateHandler;

    private LoggerInterface $logger;

    public function __construct(
        ExtensionService                    $extensionService,
                                            $orderTransactionRepository,
        AsynchronousPaymentHandlerInterface $amazonPaymentHandler,
        RouterInterface                     $router,
        OrderTransactionStateHandler        $transactionStateHandler,
        LoggerInterface                     $logger
    )
    {
        $this->extensionService = $extensionService;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->amazonPaymentHandler = $amazonPaymentHandler;
        $this->router = $router;
        $this->transactionStateHandler = $transactionStateHandler;
        $this->logger = $logger;
    }

    //SW65
    public function setTwig($twig): void
    {
        if (is_callable('parent::setTwig')) {
            parent::setTwig($twig);
        }
    }

    /**
     * Renders a temporary page which immediately starts the Amazon Pay flow.
     */
    #[Route(path: 'checkout/amazon-pay-init-checkout/{orderTransactionId}', name: 'frontend.checkout.amazon_pay_init_checkout', methods: ['GET'])]
    public function initCheckout(string $orderTransactionId, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        if (!Uuid::isValid($orderTransactionId)) {
            throw PaymentException::invalidTransaction($orderTransactionId);
        }

        $criteria = new Criteria([$orderTransactionId]);
        $criteria->addAssociation('order');
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('paymentMethodId', PaymentMethodInstaller::AMAZON_PAYMENT_ID)
        );

        /** @var OrderTransactionEntity|null $orderTransaction */
        $orderTransaction = $this->orderTransactionRepository->search($criteria, $salesChannelContext->getContext())->first();
        if ($orderTransaction === null) {
            throw PaymentException::invalidTransaction($orderTransactionId);
        }

        try {
            $amazonPurePaymentExtension = $this->extensionService->getPurePaymentExtension(
                $salesChannelContext,
                $orderTransaction,
                $request->isSecure()
            );
        } catch (ConfigValidationException $e) {
            return $this->failPayment($orderTransaction, $salesChannelContext->getContext());
        }

        if ($amazonPurePaymentExtension === null) {
            return $this->failPayment($orderTransaction, $salesChannelContext->getContext());
        }

        return $this->renderStorefront(
            '@SwagAmazonPay/storefront/page/checkout/pure-payment-redirect/index.html.twig',
            [
                AmazonPurePaymentExtension::EXTENSION_NAME => $amazonPurePaymentExtension,
            ]
        );
    }

    #[Route(path: 'payment/amazon-pay/finalize/{orderTransactionId}', name: 'payment.amazon.finalize.transaction', methods: ['GET'])]
    public function finalizeTransaction(string $orderTransactionId, Request $request, SalesChannelContext $salesChannelContext): RedirectResponse
    {

        try {
            $orderInformation = $this->amazonPaymentHandler->purePaymentFinalize(
                $orderTransactionId,
                $request,
                $salesChannelContext
            );
        } catch (PaymentException $e) {
            /** @var OrderTransactionEntity $orderTransaction */
            $orderTransaction = $this->orderTransactionRepository->search(new Criteria([$orderTransactionId]), $salesChannelContext->getContext())->first();
            return $this->redirectToAfterOrderPaymentProcess(
                $e,
                $salesChannelContext->getContext(),
                $orderTransaction ? $orderTransaction->getOrderId() : '',
                false //already done in $this->amazonPaymentHandler->purePaymentFinalize()
            );
        }

        return new RedirectResponse($this->router->generate('frontend.checkout.finish.page', [
            'orderId' => $orderInformation['order']->getId(),
        ]));
    }

    private function failPayment(OrderTransactionEntity $orderTransaction, Context $context): RedirectResponse
    {
        $this->logger->error('A checkout for a pure payment method could not be initialized.', [$orderTransaction]);

        $orderId = $orderTransaction->getOrderId();
        $paymentProcessException = PaymentException::asyncProcessInterrupted($orderTransaction->getId(), 'A checkout for a pure payment method could not be initialized');

        return $this->redirectToAfterOrderPaymentProcess(
            $paymentProcessException,
            $context,
            $orderId
        );
    }

    private function redirectToAfterOrderPaymentProcess(PaymentException $paymentProcessException, Context $context, string $orderId, bool $statusChanges = true): RedirectResponse
    {
        $errorUrl = $this->router->generate('frontend.account.edit-order.page', ['orderId' => $orderId]);

        if ($paymentProcessException->getErrorCode() === PaymentException::PAYMENT_CUSTOMER_CANCELED_EXTERNAL) {
            if($statusChanges) {
                $this->transactionStateHandler->cancel(
                    $paymentProcessException->getOrderTransactionId(),
                    $context
                );
            }
            $urlQuery = \parse_url($errorUrl, \PHP_URL_QUERY) ? '&' : '?';

            return new RedirectResponse(\sprintf('%s%serror-code=%s', $errorUrl, $urlQuery, $paymentProcessException->getErrorCode()));
        }

        $transactionId = $paymentProcessException->getOrderTransactionId();
        $this->logger->error(
            'An error occurred in pure payment flow',
            ['orderTransactionId' => $transactionId, 'exceptionMessage' => $paymentProcessException->getMessage()]
        );
        if($statusChanges) {
            $this->transactionStateHandler->fail(
                $transactionId,
                $context
            );
        }
        $urlQuery = \parse_url($errorUrl, \PHP_URL_QUERY) ? '&' : '?';

        return new RedirectResponse(\sprintf('%s%serror-code=%s', $errorUrl, $urlQuery, $paymentProcessException->getErrorCode()));
    }
}
