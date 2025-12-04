<?php

declare(strict_types=1);

namespace Swag\AmazonPay\Framework\EventListeners;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swag\AmazonPay\SwagAmazonPay;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Validator\Constraints\Optional;

class AmazonPayAddressValidationListener implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    public function __construct(
        RequestStack $requestStack
    )
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.address.create' => 'disableAdditionalAddressValidation',
            'framework.validation.address.update' => 'disableAdditionalAddressValidation',
            'framework.validation.customer.create' => 'disableBirthdayValidation',
            'kernel.controller_arguments' => 'onKernelControllerArguments',
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {

        if ($event->getRequest()->attributes->get('_route') === 'frontend.account.addressbook') {
            // we need to make sure that the amazon pay shipping address is not changed when billingAddress === shippingAddress and the shopper edits the billing address

            if ($event->getRequest()->getSession()->get(SwagAmazonPay::CHECKOUT_SESSION_KEY)) {
                //is amazon pay express checkout session

                $filteredArguments = array_filter((array)$event->getArguments(), function ($argument) {
                    return $argument instanceof CustomerEntity;
                });
                $customer = array_pop($filteredArguments);
                if (empty($customer)) {
                    return;
                }

                $filteredArguments = array_filter($event->getArguments(), function ($argument) {
                    return $argument instanceof RequestDataBag;
                });
                $requestDataBag = array_pop($filteredArguments);
                if (empty($requestDataBag)) {
                    return;
                }

                if ($addressId = $requestDataBag->get('addressId')) {
                    if ($addressId === $customer->getDefaultShippingAddressId()) {
                        $requestDataBag->set('addressId', null);
                        if ($address = $requestDataBag->get('address')) {
                            $address->set('id', null);
                        }
                    }
                }

            }
        }
    }


    public function disableAdditionalAddressValidation(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return;
        }

        if (\mb_strpos($request->getPathInfo(), 'swag_amazon_pay') === false) {
            return;
        }

        $definition = $event->getDefinition();

        $definition->set('additionalAddressLine1', new Optional());
        $definition->set('additionalAddressLine2', new Optional());
        $definition->set('phoneNumber', new Optional());
    }

    public function disableBirthdayValidation(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return;
        }

        if (\mb_strpos($request->getPathInfo(), 'swag_amazon_pay') === false) {
            return;
        }

        $definition = $event->getDefinition();

        $definition->set('birthdayDay', new Optional());
        $definition->set('birthdayMonth', new Optional());
        $definition->set('birthdayYear', new Optional());
    }
}
