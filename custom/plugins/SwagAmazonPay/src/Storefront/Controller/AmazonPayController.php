<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\Controller;

use AmazonPayApiSdkExtension\Struct\CheckoutSession;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannel\SalesChannelContextSwitcher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\AmazonPay\Components\Account\AmazonPayAccountServiceInterfaceV2;
use Swag\AmazonPay\Components\Account\Struct\AmazonLoginDataStruct;
use Swag\AmazonPay\Components\Cart\CartServiceInterface;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class AmazonPayController extends StorefrontController
{
    private ClientProviderInterface $clientProvider;

    private CartServiceInterface $cartService;

    private AmazonPayAccountServiceInterfaceV2 $amazonPayAccountService;

    private LoggerInterface $logger;

    public function __construct(
        ClientProviderInterface            $clientProvider,
        CartServiceInterface               $cartService,
        AmazonPayAccountServiceInterfaceV2 $amazonPayAccountService,
        LoggerInterface                    $logger,
    )
    {
        $this->clientProvider = $clientProvider;
        $this->cartService = $cartService;
        $this->amazonPayAccountService = $amazonPayAccountService;
        $this->logger = $logger;
    }

    // SW65
    public function setTwig($twig): void
    {
        if (is_callable('parent::setTwig')) {
            parent::setTwig($twig);
        }
    }

    #[Route(path: 'swag_amazon_pay/checkout-review', name: 'payment.swag_amazon_pay.review', methods: ['GET'])]
    public function oneClickCheckoutReview(Request $request, SalesChannelContextSwitcher $contextSwitcher, SalesChannelContext $salesChannelContext): Response
    {
        $checkoutSessionId = $request->query->get(AmazonPurePaymentMethodController::AMAZON_CHECKOUT_SESSION_ID_PARAMETER_KEY);

        if (!$checkoutSessionId) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $client = $this->clientProvider->getClient($salesChannelContext->getSalesChannel()->getId());

        try {
            $checkoutSession = $client->getCheckoutSession($checkoutSessionId);
        } catch (Exception $e) {
            $this->logger->warning('checkout review failed - no checkout session for ' . $checkoutSessionId, ['exception' => $e->getMessage()]);

            return new Response('SwagAmazonPay.errors.generic', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $currentCustomer = $salesChannelContext->getCustomer();

        // Set Amazon Pay as active payment method
        $contextSwitcher->update(
            new RequestDataBag([
                SalesChannelContextService::PAYMENT_METHOD_ID => PaymentMethodInstaller::AMAZON_PAYMENT_ID,
            ]),
            $salesChannelContext
        );

        if ($currentCustomer) {
            $this->amazonPayAccountService->updateCustomer($currentCustomer->getId(), $checkoutSession->toArray(), $salesChannelContext);
        } else {
            $this->startCustomerOrGuestSession($checkoutSession, $salesChannelContext);
        }

        if ($this->cartService->isCartIsEmpty($salesChannelContext) === true) {
            return $this->redirectToRoute('frontend.home.page');
        }

        return $this->redirectToRoute('frontend.checkout.confirm.page', [
            'amazonPayCheckoutId' => $checkoutSessionId,
            'oneClickCheckout' => true,
        ]);
    }

    protected function startCustomerOrGuestSession(CheckoutSession $checkoutSession, SalesChannelContext $salesChannelContext): void
    {
        $amazonLoginData = new AmazonLoginDataStruct($checkoutSession->getBuyer()->getBuyerId(), $checkoutSession->getBuyer()->getEmail());
        $customerId = $this->amazonPayAccountService->getActiveCustomerId($amazonLoginData, $salesChannelContext);

        if (!$customerId) {
            $customerId = $this->amazonPayAccountService->registerCustomerOrGuest($checkoutSession->toArray(), $salesChannelContext, true);
        } else {
            $this->amazonPayAccountService->updateCustomer($customerId, $checkoutSession->toArray(), $salesChannelContext);
        }

        // Login/registration failed!
        if (!$customerId) {
            $this->logger->error('Could not initiate payment confirmation: The customer account could either not be logged in or not be registered to this shop.', [
                'checkoutSession' => $checkoutSession->toArray(),
            ]);
            throw new \RuntimeException('Could not automatically register or login customer account.');
        }

        $this->amazonPayAccountService->loginByCustomerId($customerId, $salesChannelContext);
    }
}
