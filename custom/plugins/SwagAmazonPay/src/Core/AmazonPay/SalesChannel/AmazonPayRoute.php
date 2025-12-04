<?php declare(strict_types=1);

namespace Swag\AmazonPay\Core\AmazonPay\SalesChannel;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannel\SalesChannelContextSwitcher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPage;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Swag\AmazonPay\Components\Account\AmazonPayAccountServiceInterfaceV2;
use Swag\AmazonPay\Components\Account\Struct\AmazonLoginDataStruct;
use Swag\AmazonPay\Components\Button\ButtonProviderInterface;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\PaymentHandler\AmazonPaymentHandler;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Swag\AmazonPay\Storefront\Page\Extension\ExtensionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ["store-api"]])]
class AmazonPayRoute extends AbstractAmazonPayRoute
{
    const REQUEST_PARAMETER_REVIEW_RETURN_URL = 'reviewReturnUrl';
    const REQUEST_PARAMETER_RETURN_URL = 'returnUrl';
    const REQUEST_PARAMETER_AMAZON_CHECKOUT_SESSION_ID = 'amazonCheckoutSessionId';
    const REQUEST_PARAMETER_ORDER_TRANSACTION_ID = 'orderTransactionId';


    public function __construct(
        private readonly ButtonProviderInterface            $buttonProvider,
        private readonly ClientProviderInterface            $clientProvider,
        private readonly ExtensionService                   $extensionService,
        private readonly EntityRepository                   $orderTransactionRepository,
        private readonly AmazonPayAccountServiceInterfaceV2 $amazonPayAccountService,
        private readonly AmazonPaymentHandler               $amazonPaymentHandler,
        private readonly LoggerInterface                    $logger,
    )
    {
    }

    public function getDecorated(): AbstractAmazonPayRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route("/store-api/amazon-pay-checkout-button", name: "store-api.swag-amazon-pay.checkout-button", methods: ["POST"])]
    public function checkoutButton(Request $request, SalesChannelContext $context): AmazonPayRouteResponse
    {
        $event = new CheckoutCartPageLoadedEvent(new CheckoutCartPage(), $context, $request);
        $buttonExtension = $this->buttonProvider->getAmazonPayButton(
            $event,
            $request->get(static::REQUEST_PARAMETER_REVIEW_RETURN_URL)
        );
        $responseData = new ArrayStruct($buttonExtension ? $buttonExtension->jsonSerialize() : []);
        $response = new AmazonPayRouteResponse($responseData);
        if (empty($buttonExtension)) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * @throws Exception
     */
    #[Route("/store-api/amazon-pay-pure-payment", name: "store-api.swag-amazon-pay.pure-payment", methods: ["POST"])]
    public function purePayment(Request $request, SalesChannelContext $context): AmazonPayRouteResponse
    {
        try {
            $orderTransactionId = (string)$request->get(static::REQUEST_PARAMETER_ORDER_TRANSACTION_ID);
            if (!Uuid::isValid($orderTransactionId)) {
                throw PaymentException::invalidTransaction($orderTransactionId);
            }
            $returnUrl = $request->get(static::REQUEST_PARAMETER_RETURN_URL);
            if (empty($returnUrl)) {
                throw PaymentException::invalidTransaction(static::REQUEST_PARAMETER_RETURN_URL . ' should not be empty');
            }

            $criteria = new Criteria([$orderTransactionId]);
            $criteria->addAssociation('order');
            $criteria->setLimit(1);
            $criteria->addFilter(
                new EqualsFilter('paymentMethodId', PaymentMethodInstaller::AMAZON_PAYMENT_ID)
            );

            /** @var OrderTransactionEntity|null $orderTransaction */
            $orderTransaction = $this->orderTransactionRepository->search($criteria, $context->getContext())->first();
            if ($orderTransaction === null) {
                throw PaymentException::invalidTransaction($orderTransactionId);
            }

            $amazonPurePaymentExtension = $this->extensionService->getPurePaymentExtension(
                $context,
                $orderTransaction,
                $request->isSecure(),
                $returnUrl
            );

            if ($amazonPurePaymentExtension === null) {
                throw new Exception('Could not be initialized');
            }
            $responseData = new ArrayStruct($amazonPurePaymentExtension->jsonSerialize());
        } catch (Exception $e) {
            $responseData = new ArrayStruct(['error' => $e->getMessage(), 'errorType' => get_class($e)]);
        }
        $response = new AmazonPayRouteResponse($responseData);
        if (!empty($responseData->get('error'))) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }


    /**
     * @throws Exception
     */
    #[Route("/store-api/amazon-pay-pure-payment-finalize", name: "store-api.swag-amazon-pay.pure-payment-finalize", methods: ["POST"])]
    public function purePaymentFinalize(Request $request, SalesChannelContext $context): AmazonPayRouteResponse
    {
        $orderTransactionId = $request->get(static::REQUEST_PARAMETER_ORDER_TRANSACTION_ID);
        try {
            $orderInformation = $this->amazonPaymentHandler->purePaymentFinalize(
                $orderTransactionId,
                $request,
                $context
            );
            $responseData = new ArrayStruct([
                'success' => 1,
                'orderId' => $orderInformation['order']->getId(),
                'orderTransactionId' => $orderTransactionId,
            ]);
        } catch (Exception $e) {
            $responseData = new ArrayStruct(['error' => $e->getMessage(), 'errorType' => get_class($e)]);
        }
        $response = new AmazonPayRouteResponse($responseData);
        if (!empty($responseData->get('error'))) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    #[Route("/store-api/amazon-pay-checkout-review", name: "store-api.swag-amazon-pay.checkout-review", methods: ["POST"])]
    public function checkoutReview(Request $request, SalesChannelContextSwitcher $contextSwitcher, SalesChannelContext $context): AmazonPayRouteResponse
    {
        $checkoutSessionId = $request->get(static::REQUEST_PARAMETER_AMAZON_CHECKOUT_SESSION_ID);
        if (!$checkoutSessionId) {
            return $this->getErrorResponse('empty checkout session');
        }

        $client = $this->clientProvider->getClient($context->getSalesChannel()->getId());

        try {
            $checkoutSession = $client->getCheckoutSession($checkoutSessionId);
        } catch (Exception $e) {
            $this->logger->warning('Could not get checkout session in /store-api/amazon-pay-checkout-review', ['error' => $e->getMessage()]);
            return $this->getErrorResponse('checkout session does not exist');
        }

        // Customer is already logged in.
        $currentCustomer = $context->getCustomer();

        // Set Amazon Pay as active payment method
        $contextSwitcher->update(new RequestDataBag([
            SalesChannelContextService::PAYMENT_METHOD_ID => PaymentMethodInstaller::AMAZON_PAYMENT_ID,
        ]), $context);
        $contextToken = $context->getToken();
        if ($currentCustomer) {
            $this->amazonPayAccountService->updateCustomer($currentCustomer->getId(), $checkoutSession->toArray(), $context);
        } else {
            $amazonLoginData = new AmazonLoginDataStruct($checkoutSession->getBuyer()->getBuyerId(), $checkoutSession->getBuyer()->getEmail());
            $customerId = $this->amazonPayAccountService->getActiveCustomerId($amazonLoginData, $context);
            if (!$customerId) {
                $customerId = $this->amazonPayAccountService->registerCustomerOrGuest($checkoutSession->toArray(), $context, true);
            } else {
                $this->amazonPayAccountService->updateCustomer($customerId, $checkoutSession->toArray(), $context);
            }

            if (!$customerId) {
                $this->logger->error('Could not initiate payment confirmation: The customer account could either not be logged in or not be registered to this shop.', [
                    'checkoutSessionResponse' => $checkoutSession->toArray(),
                ]);
                return $this->getErrorResponse('unable to register or login');
            }

            $contextToken = $this->amazonPayAccountService->loginByCustomerId($customerId, $context);
        }
        $response = new AmazonPayRouteResponse(new ArrayStruct(['success' => 1, 'contextToken' => $contextToken]));
        $response->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $contextToken);
        return $response;
    }

    protected function getErrorResponse($errorMessage): AmazonPayRouteResponse
    {
        $response = new AmazonPayRouteResponse(new ArrayStruct(['error' => $errorMessage]));
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        return $response;
    }
}
