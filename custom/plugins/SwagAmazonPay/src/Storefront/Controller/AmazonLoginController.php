<?php declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPage;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoader;
use Swag\AmazonPay\Components\Account\AmazonPayAccountServiceInterfaceV2;
use Swag\AmazonPay\Components\Account\Exception\CustomerNotActiveException;
use Swag\AmazonPay\Components\Account\Hydrator\RegistrationDataHydratorInterface;
use Swag\AmazonPay\Components\Account\Struct\AmazonLoginDataStruct;
use Swag\AmazonPay\Core\Checkout\Customer\SalesChannel\AccountRegistrationService;
use Swag\AmazonPay\DataAbstractionLayer\Entity\SignUpToken\SignUpTokenServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class AmazonLoginController extends StorefrontController
{
    private CheckoutRegisterPageLoader $registerPageLoader;

    private RegistrationDataHydratorInterface $registrationDataHydrator;

    private AmazonPayAccountServiceInterfaceV2 $accountService;

    private AccountRegistrationService $accountRegistrationService;

    private SignUpTokenServiceInterface $signUpTokenService;

    private LoggerInterface $logger;

    public function __construct(
        CheckoutRegisterPageLoader         $registerPageLoader,
        RegistrationDataHydratorInterface  $registrationDataHydrator,
        AmazonPayAccountServiceInterfaceV2 $accountService,
        AccountRegistrationService         $accountRegistrationService,
        SignUpTokenServiceInterface        $signUpTokenService,
        LoggerInterface                    $logger
    )
    {
        $this->registerPageLoader = $registerPageLoader;
        $this->registrationDataHydrator = $registrationDataHydrator;
        $this->accountService = $accountService;
        $this->accountRegistrationService = $accountRegistrationService;
        $this->signUpTokenService = $signUpTokenService;
        $this->logger = $logger;
    }

    // SW65
    public function setTwig($twig): void
    {
        if (is_callable('parent::setTwig')) {
            parent::setTwig($twig);
        }
    }

    /**
     * Login with Amazon.
     */
    #[Route(path: 'swag_amazon_pay/customer_sign_in', name: 'frontend.swag_amazon_pay.customer_sign_in', defaults: ['XmlHttpRequest' => true], methods: ['GET'])]
    public function customerSignIn(Request $request, SalesChannelContext $context): Response
    {
        $signUpTokenId = $request->query->getAlnum('signUpTokenId');
        if (!$this->signUpTokenService->validate($signUpTokenId, $context->getContext())) {
            $this->addFlash('warning', $this->trans('SwagAmazonPay.errors.signupTokenInvalid'));

            return new RedirectResponse($this->generateUrl('frontend.account.login.page'));
        }

        $customerData = new DataBag();
        $buyerToken = (string)$request->query->get('buyerToken');
        $redirectTo = (string)$request->query->get('redirectTo', 'frontend.home.page');

        try {
            // try login
            $customerData = $this->registrationDataHydrator->hydrateBuyerInformation($buyerToken, $context);
            $amazonLoginData = new AmazonLoginDataStruct($customerData->get('amazonAccountId'), $customerData->get('email'));
            $this->accountService->loginByAmazonAccount($amazonLoginData, $context);
            return new RedirectResponse($this->generateUrl($redirectTo));
        } catch (CustomerNotFoundException) {
            // if no registered customer found > redirect to prefilled register page
            return $this->renderStorefront('@Storefront/storefront/page/swag-amazon-pay/register.html.twig', [
                'redirectTo' => $redirectTo,
                'redirectParameters' => $request->query->get('redirectParameters', '[]'),
                'page' => $this->loadRegisterPage($request, $context, $customerData),
                'data' => $customerData,
                'registrationUrl' => $this->generateUrl('frontend.swag_amazon_pay.register_customer'),
            ]);
        } catch (CustomerNotActiveException) {
            // if customer is not active > redirect to login page and show error
            $this->addFlash('danger', $this->trans('SwagAmazonPay.errors.loginWithAmazon'));
            return new RedirectResponse($this->generateUrl('frontend.account.login.page'));
        } catch (Throwable $e) {
            // something else went wrong
            $this->logger->error('Unknown customerSignIn error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return new RedirectResponse($this->generateUrl('frontend.account.register.page', ['isAmazonLoginError' => true]));
        }
    }

    /**
     * Register account on first use of login button.
     */
    #[Route(path: 'swag_amazon_pay/register_customer', name: 'frontend.swag_amazon_pay.register_customer', methods: ['POST'])]
    public function registerCustomer(Request $request, RequestDataBag $data, SalesChannelContext $context): Response
    {
        try {
            if (!$data->has('differentShippingAddress')) {
                $data->remove('shippingAddress');
            }

            $this->accountService->setRandomDefaultPassword($data);

            $customer = $this->accountRegistrationService->register($data, false, $context);
            $this->accountService->setAmazonPayAccountId($customer->getId(), $data->get('amazonAccountId'), $context->getContext());
        } catch (ConstraintViolationException $cve) {
            $violations = $cve->getViolations()->findByCodes(\sprintf('VIOLATION::%s', CustomerEmailUnique::getErrorName(CustomerEmailUnique::CUSTOMER_EMAIL_NOT_UNIQUE)));
            if ($violations->count() < 1) {
                return new RedirectResponse($this->generateUrl('frontend.account.register.page', ['isAmazonLoginError' => true]));
            }
            $this->addFlash('danger', $this->trans('SwagAmazonPay.errors.signupEmailNotUnique'));

            return new RedirectResponse($this->generateUrl('frontend.account.register.page'));
        } catch (Throwable $e) {
            $this->logger->error('Unknown registerCustomer error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return new RedirectResponse($this->generateUrl('frontend.account.register.page', ['isAmazonLoginError' => true]));
        }

        if (!$customer->getDoubleOptInRegistration()) {
            try {
                $this->accountService->loginByCustomerId($customer->getId(), $context);
            } catch (Throwable $e) {
                $this->logger->error('Unable to login after registration: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return new RedirectResponse($this->generateUrl('frontend.account.register.page', ['isAmazonLoginError' => true]));
            }
        } else {
            $this->addFlash('success', $this->trans('account.optInRegistrationAlert'));
            return new RedirectResponse($this->generateUrl('frontend.account.register.page'));
        }

        return new RedirectResponse($this->generateUrl((string)$request->query->get('redirectTo', 'frontend.home.page')));
    }

    private function loadRegisterPage(Request $request, SalesChannelContext $context, DataBag $customerData): CheckoutRegisterPage
    {
        $page = $this->registerPageLoader->load($request, $context);

        if ($customerData->get('company', '')) {
            $pageAddress = new CustomerAddressEntity();

            /*
             * Field "company" determines preselection of account type select
             */
            $pageAddress->setCompany($customerData->get('company'));
            $page->setAddress($pageAddress);
        }

        return $page;
    }
}
