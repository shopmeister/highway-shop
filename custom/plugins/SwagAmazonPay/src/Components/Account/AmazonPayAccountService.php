<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\Account;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundByIdException;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\Context\CartRestorer;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Account\Exception\CustomerNotActiveException;
use Swag\AmazonPay\Components\Account\Hydrator\RegistrationDataHydratorInterface;
use Swag\AmazonPay\Components\Account\Struct\AmazonLoginDataStruct;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Core\Checkout\Customer\SalesChannel\AccountRegistrationService;
use Swag\AmazonPay\Installer\CustomFieldsInstaller;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class AmazonPayAccountService implements AmazonPayAccountServiceInterfaceV2
{
    public function __construct(
        private EntityRepository                  $customerRepository,
        private ConfigServiceInterface            $configService,
        private AccountRegistrationService        $registrationService,
        private RegistrationDataHydratorInterface $registrationDataHydrator,
        private EntityRepository                  $customerAddressRepository,
        private EventDispatcherInterface          $eventDispatcher,
        private ?CartRestorer                     $contextRestorer,
        private SalesChannelContextPersister      $contextPersister,
        private bool                              $noGuests
    )
    {
    }

    public function loginByCustomerId(string $customerId, SalesChannelContext $context): string
    {
        $customer = $this->getCustomerById($customerId, $context);
        if (null === $customer) {
            throw new CustomerNotFoundByIdException($customerId);
        }

        return $this->login($customer, $context);
    }

    public function loginByAmazonAccount(AmazonLoginDataStruct $amazonLoginDataStruct, SalesChannelContext $context): string
    {
        $amazonAccountId = $amazonLoginDataStruct->getAmazonAccountId();
        $customer = null;
        if ($amazonLoginDataStruct->getAmazonAccountEmail()) {
            $customer = $this->getCustomerByEmailAddress($amazonLoginDataStruct->getAmazonAccountEmail(), $context);
        }
        if (null === $customer && $amazonAccountId) {
            $customer = $this->getCustomerByAmazonAccountId($amazonAccountId, $context);
        }

        if (null === $customer) {
            throw new CustomerNotFoundException($amazonLoginDataStruct->getAmazonAccountEmail());
        }

        if(!$customer->getActive()){
            throw new CustomerNotActiveException($customer->getId());
        }

        return $this->login($customer, $context);
    }

    public function registerCustomerOrGuest(array $checkoutSession, SalesChannelContext $salesChannelContext, bool $isGuest = true): string
    {
        if($this->noGuests){
            $isGuest = false;
        }
        $customerDataBag = $this->registrationDataHydrator->hydrateCustomerDataBag($checkoutSession, $salesChannelContext);
        $this->setRandomDefaultPassword($customerDataBag);
        $customerId = $this->registrationService->register($customerDataBag, $isGuest, $salesChannelContext)->getId();
        $amazonAccountId = $checkoutSession['buyer']['buyerId'];

        $this->setAmazonPayAccountId($customerId, $amazonAccountId, $salesChannelContext->getContext());
        $this->setDefaultPaymentMethodId($customerId, $salesChannelContext->getContext());

        return $customerId;
    }

    public function updateCustomer(
        string              $customerId,
        array               $checkoutSession,
        SalesChannelContext $salesChannelContext,
    ): void
    {
        $this->setDefaultPaymentMethodId($customerId, $salesChannelContext->getContext());

        $context = $salesChannelContext->getContext();

        if (empty($checkoutSession['shippingAddress'])) {
            return;
        }
        $shippingAddress = $this->registrationDataHydrator->hydrateAddressDataBag($checkoutSession['shippingAddress'], $salesChannelContext);
        $this->updateShippingAddress($shippingAddress->all(), $customerId, $context);
    }

    public function getActiveCustomerId(AmazonLoginDataStruct $amazonLoginDataStruct, SalesChannelContext $salesChannelContext): ?string
    {
        $amazonAccountId = $amazonLoginDataStruct->getAmazonAccountId();
        $amazonAccountEmail = $amazonLoginDataStruct->getAmazonAccountEmail();

        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter(\sprintf('customFields.%s', CustomFieldsInstaller::CUSTOM_FIELD_NAME_ACCOUNT_ID), $amazonAccountId),
                new EqualsFilter('email', $amazonAccountEmail),
            ]),
            new EqualsFilter('guest', false),
            new EqualsFilter('active', true)
        );

        $this->addCustomerBoundToSalesChannelFilter($salesChannelContext, $criteria);

        return $this->customerRepository->searchIds($criteria, $salesChannelContext->getContext())->firstId();
    }

    public function getAmazonCustomerIdByEmail(string $email, SalesChannelContext $salesChannelContext): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new NotFilter(MultiFilter::CONNECTION_AND, [
                    new EqualsFilter(\sprintf('customFields.%s', CustomFieldsInstaller::CUSTOM_FIELD_NAME_ACCOUNT_ID), null),
                ]),
                new EqualsFilter('email', $email),
            ]),
            new EqualsFilter('guest', false)
        );

        $this->addCustomerBoundToSalesChannelFilter($salesChannelContext, $criteria);

        return $this->customerRepository->searchIds($criteria, $salesChannelContext->getContext())->firstId();
    }

    public function setAmazonPayAccountId(string $customerId, string $amazonAccountId, Context $context): void
    {
        $this->customerRepository->upsert([
            [
                'id' => $customerId,
                'customFields' => [
                    CustomFieldsInstaller::CUSTOM_FIELD_NAME_ACCOUNT_ID => $amazonAccountId,
                ],
            ],
        ], $context);
    }

    public function setRandomDefaultPassword(DataBag $data): void
    {
        $password = Uuid::randomHex();
        $data->set('password', $password);
        $data->set('passwordConfirmation', $password);
    }

    public function getCustomerIdByBuyerId(string $amazonAccountId, SalesChannelContext $salesChannelContext): ?string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter(\sprintf('customFields.%s', CustomFieldsInstaller::CUSTOM_FIELD_NAME_ACCOUNT_ID), $amazonAccountId),
            new EqualsFilter('guest', false)
        );

        $this->addCustomerBoundToSalesChannelFilter($salesChannelContext, $criteria);

        return $this->customerRepository->searchIds($criteria, $salesChannelContext->getContext())->firstId();
    }

    private function login(CustomerEntity $customer, SalesChannelContext $context): string
    {
        if (null !== $this->contextRestorer) {
            $context = $this->contextRestorer->restore($customer->getId(), $context);
            $newToken = $context->getToken();
        } else {
            $newToken = $this->contextPersister->replace($context->getToken(), $context);
            $this->contextPersister->save($newToken, ['billingAddressId' => null, 'shippingAddressId' => null], $context->getSalesChannel()->getId(), $customer->getId());
        }

        $event = new CustomerLoginEvent($context, $customer, $newToken);
        $this->eventDispatcher->dispatch($event);

        return $newToken;
    }

    private function getCustomerByAmazonAccountId(string $amazonPayAccountId, SalesChannelContext $salesChannelContext): ?CustomerEntity
    {
        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter(\sprintf('customFields.%s', CustomFieldsInstaller::CUSTOM_FIELD_NAME_ACCOUNT_ID), $amazonPayAccountId),
            new EqualsFilter('guest', false)
        );

        $this->addCustomerBoundToSalesChannelFilter($salesChannelContext, $criteria);

        return $this->customerRepository->search($criteria, $salesChannelContext->getContext())->first();
    }

    private function getCustomerByEmailAddress(string $emailAddress, SalesChannelContext $salesChannelContext): ?CustomerEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('email', $emailAddress),
            new EqualsFilter('guest', false)
        );
        $this->addCustomerBoundToSalesChannelFilter($salesChannelContext, $criteria);

        return $this->customerRepository->search($criteria, $salesChannelContext->getContext())->first();
    }

    private function getCustomerById(string $customerId, SalesChannelContext $salesChannelContext): ?CustomerEntity
    {
        $criteria = new Criteria([$customerId]);

        return $this->customerRepository->search($criteria, $salesChannelContext->getContext())->first();
    }

    /**
     * Returns an id of an address if the comparison to existing addresses of the customer was successfully.
     * Returns null if no matching address could be found.
     */
    private function getExistingCustomerAddressId(string $customerId, array $amazonAddress, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('customerId', $customerId),
            new EqualsFilter('firstName', $amazonAddress['firstName']),
            new EqualsFilter('lastName', $amazonAddress['lastName']),
            new EqualsFilter('city', $amazonAddress['city']),
            new EqualsFilter('zipcode', $amazonAddress['zipcode']),
            new EqualsFilter('phoneNumber', $amazonAddress['phoneNumber']),
            new EqualsFilter('company', $amazonAddress['company'])
        );

        return $this->customerAddressRepository->searchIds($criteria, $context)->firstId();
    }

    /**
     * Sets the default payment method id to the Amazon Pay payment method id for the specified customer.
     */
    private function setDefaultPaymentMethodId(string $customerId, Context $context): void
    {
        $this->customerRepository->upsert([
            [
                'id' => $customerId,
                'defaultPaymentMethodId' => PaymentMethodInstaller::AMAZON_PAYMENT_ID,
            ],
        ], $context);
    }

    /**
     * @param array $address The address data
     * @param string $customerId The customer id for which the address should be updated
     * @param Context $context The Shopware Context
     */
    private function updateShippingAddress(array $address, string $customerId, Context $context): void
    {
        // Duplicate?
        $addressId = $this->getExistingCustomerAddressId($customerId, $address, $context);

        if ($addressId) {
            $this->customerRepository->upsert([
                [
                    'id' => $customerId,
                    'defaultShippingAddressId' => $addressId,
                ],
            ], $context);

            return;
        }

        $this->customerRepository->upsert([
            [
                'id' => $customerId,
                'defaultShippingAddress' => $address,
            ],
        ], $context);
    }

    private function addCustomerBoundToSalesChannelFilter(SalesChannelContext $salesChannelContext, Criteria $criteria): void
    {
        if ($this->configService->getSystemConfig(
            'core.systemWideLoginRegistration.isCustomerBoundToSalesChannel',
            $salesChannelContext->getSalesChannel()->getId()
        )) {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('customer.boundSalesChannelId', null),
                new EqualsFilter('customer.boundSalesChannelId', $salesChannelContext->getSalesChannel()->getId()),
            ]));
        }
    }
}
