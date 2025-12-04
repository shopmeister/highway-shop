<?php declare(strict_types=1);


namespace Swag\AmazonPay\Components\Account\Hydrator;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\AmazonPay\Components\Account\Hydrator\Exception\AddressNotFoundException;
use Swag\AmazonPay\Components\Client\ClientProviderInterface;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Installer\DefaultSalutationInstaller;
use Symfony\Component\Intl\Exception\MissingResourceException;
use TheIconic\NameParser\Parser;

class RegistrationDataHydrator implements RegistrationDataHydratorInterface
{
    private ?string $salutationId = null;

    public function __construct(
        private readonly EntityRepository $countryRepository,
        private readonly EntityRepository $salutationRepository,
        private readonly ConfigServiceInterface $configService,
        private readonly ClientProviderInterface $clientProvider
    ) {

    }

    /**
     * {@inheritdoc}
     *
     * @throws AddressNotFoundException
     * @throws \RuntimeException
     * @throws InconsistentCriteriaIdsException
     */
    public function hydrateCustomerDataBag(array $checkoutSession, SalesChannelContext $context): DataBag
    {
        $this->setDefaultSalutationId($context->getContext());

        $amazonPayShippingAddress = $checkoutSession['shippingAddress'] ?? null;
        $amazonPayBillingAddress = $checkoutSession['billingAddress'] ?? null;

        $billingAddress = null;
        $shippingAddress = null;

        if ($amazonPayBillingAddress !== null) {
            $billingAddress = $this->hydrateAddressDataBag($amazonPayBillingAddress, $context);
        }

        if ($amazonPayShippingAddress !== null) {
            $shippingAddress = $this->hydrateAddressDataBag($amazonPayShippingAddress, $context);
        }

        if (!$billingAddress && !$shippingAddress) {
            throw new AddressNotFoundException($checkoutSession['checkoutSessionId']);
        }

        if ($billingAddress === null && $shippingAddress !== null) {
            $billingAddress = $shippingAddress;
        }

        if ($shippingAddress === null && $billingAddress !== null) {
            $shippingAddress = $billingAddress;
        }

        if ($billingAddress === null) {
            throw new AddressNotFoundException($checkoutSession['checkoutSessionId']);
        }

        return new DataBag([
            'firstName' => $billingAddress->get('firstName'),
            'lastName' => $billingAddress->get('lastName'),
            'email' => $checkoutSession['buyer']['email'],
            'salutationId' => $this->salutationId,
            'acceptedDataProtection' => true,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateAddressDataBag(array $amazonPayAddress, SalesChannelContext $salesChannelContext): DataBag
    {
        $context = $salesChannelContext->getContext();

        $this->setDefaultSalutationId($context);

        $addressName = $this->splitName($amazonPayAddress['name']);
        $streetAndCompanyInfo = $this->parseStreetAndCompany($amazonPayAddress);

        $showPhoneNumberField = $this->configService->getSystemConfig('core.loginRegistration.showPhoneNumberField', $salesChannelContext->getSalesChannel()->getId());

        return new DataBag([
            'salutationId' => $this->salutationId,
            'firstName' => $addressName['firstName'],
            'lastName' => $addressName['lastName'],
            'city' => $amazonPayAddress['city'],
            'countryId' => $this->getCountryId($amazonPayAddress['countryCode'], $context),
            'street' => $streetAndCompanyInfo['street'],
            'additionalAddressLine1' => $amazonPayAddress['addressLine3'] ?? '',
            'zipcode' => $amazonPayAddress['postalCode'],
            'phoneNumber' => ($showPhoneNumberField === true && !empty($amazonPayAddress['phoneNumber'])) ? $amazonPayAddress['phoneNumber'] : null,
            'company' => $streetAndCompanyInfo['company'],
        ]);
    }

    public function hydrateBuyerInformation(string $buyerToken, SalesChannelContext $salesChannelContext): DataBag
    {
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();
        $context = $salesChannelContext->getContext();

        $this->setDefaultSalutationId($context);

        $client = $this->clientProvider->getLegacyClient($salesChannelId);
        $response = $client->getBuyer($buyerToken);

        $buyerInformation = \json_decode($response['response'], true);

        if (!\array_key_exists('buyerId', $buyerInformation) || !$buyerInformation['buyerId']) {
            throw new MissingResourceException('BuyerInformation do not contain a buyerId!');
        }

        $addressName = $this->splitName($buyerInformation['shippingAddress']['name']);
        $streetAndCompanyInfo = $this->parseStreetAndCompany($buyerInformation['shippingAddress']);

        $phoneNumberFieldRequired = $this->configService->getSystemConfig('core.loginRegistration.phoneNumberFieldRequired', $salesChannelContext->getSalesChannel()->getId());

        return new DataBag(
            [
                'amazonAccountId' => $buyerInformation['buyerId'],
                'salutationId' => $this->salutationId,
                'firstName' => $addressName['firstName'],
                'lastName' => $addressName['lastName'],
                'email' => $buyerInformation['email'],
                'emailConfirmation' => $buyerInformation['email'],
                'company' => $streetAndCompanyInfo['company'],
                'acceptedDataProtection' => true,
                'billingAddress' => new DataBag([
                    'company' => $streetAndCompanyInfo['company'],
                    'street' => $streetAndCompanyInfo['street'],
                    'zipcode' => $buyerInformation['shippingAddress']['postalCode'],
                    'city' => $buyerInformation['shippingAddress']['city'],
                    'countryId' => $this->getCountryId($buyerInformation['shippingAddress']['countryCode'], $context),
                    'phoneNumber' => ($phoneNumberFieldRequired === true) ? $buyerInformation['shippingAddress']['phoneNumber'] : null,
                    'additionalAddressLine1' => $buyerInformation['shippingAddress']['addressLine3'] ?? '',
                ]),
            ]
        );
    }

    private function setDefaultSalutationId(Context $context): void
    {
        if (!$this->salutationId) {
            $this->salutationId = $this->getSalutationId($context);
        }
    }

    /**
     * Example:
     *  Given: 'Max Test Mustermann'
     *  Result: ['firstName => 'Max Test', 'lastName' => 'Mustermann']
     */
    private function splitName(string $name): array
    {
        $nameParser = new Parser();
        $result = $nameParser->parse($name);

        return [
            'firstName' => $result->getGivenName(),
            'lastName' => $result->getLastname() ?: $result->getGivenName(),
        ];
    }

    /**
     * Amazon Pay has to deal with some legacy address data from the amazon systems itself,
     * this method will identify the correct street and company name from it.
     * The actual problem is that amazonPayAddressData["addressLine1"] is not always the street, depending on whether the customer
     * is from DE,AT or anywhere else in the world or even just being a company.
     */
    private function parseStreetAndCompany(array $amazonPayAddressData): array
    {
        $company = null;
        $street = null;

        $addressLine1 = isset($amazonPayAddressData['addressLine1'])?(string)$amazonPayAddressData['addressLine1']:'';
        $addressLine2 = isset($amazonPayAddressData['addressLine2'])?(string)$amazonPayAddressData['addressLine2']:'';
        $addressLine3 = isset($amazonPayAddressData['addressLine3'])?(string)$amazonPayAddressData['addressLine3']:'';

        /*
         * The following code is a one-to-one implementation of the address parsing pseudo code
         * provided by Amazon.
         */
        if (\in_array($amazonPayAddressData['countryCode'], ['DE', 'AT'])) {
            if (!empty($addressLine2)) {
                if ($this->stringStartsWithNumber($addressLine2) && \mb_strlen($addressLine2) < 10) {
                    $houseNumber = \str_replace(' ', '', $addressLine2);
                    $street = \sprintf('%s %s', $addressLine1, $houseNumber);
                } else {
                    if ($this->stringEndContainsNumber($addressLine2) === false) {
                        if ($this->stringEndContainsNumber($addressLine1) === true) {
                            $street = $addressLine1;
                            $company = $addressLine2;
                        } else {
                            $street = $addressLine2;
                            $company = $addressLine1;
                        }
                    } else {
                        $street = $addressLine2;
                        $company = $addressLine1;
                    }
                }
            } elseif (!empty($addressLine1)) {
                $street = $addressLine1;
            }

            if (!empty($addressLine3)) {
                $company = \sprintf('%s %s', $company, $addressLine3);
            }
        } else {
            if (!empty($addressLine1)) {
                $street = $addressLine1;

                if (!empty($addressLine2)) {
                    $company = $addressLine2;
                }

                if (!empty($addressLine3)) {
                    $company = \sprintf('%s %s', $company, $addressLine3);
                }
            } elseif (!empty($addressLine2)) {
                $street = $addressLine2;

                if (!empty($addressLine3)) {
                    $company = $addressLine3;
                }
            } elseif (!empty($addressLine3)) {
                $street = $addressLine3;
            }
        }

        if (empty($street)) {
            throw new \RuntimeException('Could not determine street from address');
        }

        return [
            'street' => \trim($street),
            'company' => $company ? \trim($company) : null,
        ];
    }

    private function getCountryId(string $countryCode, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('iso', $countryCode)
        );

        return $this->countryRepository->searchIds($criteria, $context)->firstId();
    }

    /**
     * @throws \RuntimeException
     * @throws InconsistentCriteriaIdsException
     */
    private function getSalutationId(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('salutationKey', DefaultSalutationInstaller::DEFAULT_SALUTATION_KEY)
        );

        $salutationId = $this->salutationRepository->searchIds($criteria, $context)->firstId();
        if ($salutationId !== null) {
            return $salutationId;
        }

        $salutationId = $this->salutationRepository->searchIds($criteria->resetFilters(), $context)->firstId();
        if ($salutationId !== null) {
            return $salutationId;
        }

        throw new \RuntimeException('No salutation found in Shopware');
    }

    private function stringStartsWithNumber(string $value): bool
    {
        return \is_numeric(\mb_substr($value, 0, 1));
    }

    private function stringEndContainsNumber(string $value): bool
    {
        $elements = \mb_split('\s', $value);
        if (!\is_array($elements)) {
            return false;
        }

        $quantity = \count($elements);

        if ($quantity <= 1) {
            return false;
        }

        if ($quantity === 2) {
            return \preg_match('/\\d/', $elements[1]) === 1;
        }

        return \preg_match('/\\d/', $elements[$quantity - 1]) === 1 || \preg_match('/\\d/', $elements[$quantity - 2]) === 1;
    }
}
