<?php
declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\EventListeners;

use AmazonPayApiSdkExtension\Struct\CheckoutSession;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Swag\AmazonPay\Components\Client\ClientProvider;
use Swag\AmazonPay\Installer\PaymentMethodInstaller;
use Swag\AmazonPay\Storefront\Page\Extension\AmazonPayConfirmExtension;
use Swag\AmazonPay\SwagAmazonPay;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AmazonPayConfirmEventListener implements EventSubscriberInterface
{
    private ClientProvider $clientProvider;

    public function __construct(ClientProvider $clientProvider)
    {
        $this->clientProvider = $clientProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onLoadConfirmPage',
        ];
    }

    public function onLoadConfirmPage(CheckoutConfirmPageLoadedEvent $event): void
    {
        $session = $event->getRequest()->getSession();

        // Only for AmazonPay!
        if ($event->getSalesChannelContext()->getPaymentMethod()->getId() !== PaymentMethodInstaller::AMAZON_PAYMENT_ID && $event->getRequest()->get('amazonPayAction') === 'reset') {
            // Remove the "on hold" checkout session id from the session just to clear things up.
            // Will be automatically set again if Amazon Pay is active
            $session->remove(SwagAmazonPay::CHECKOUT_SESSION_KEY);

            return;
        }

        // Use URL param for checkout determination first and fallback to the value inside the session.
        $checkoutSessionId = (string)$event->getRequest()->query->get('amazonPayCheckoutId', $session->get(SwagAmazonPay::CHECKOUT_SESSION_KEY));
        $isOneClickCheckout = $event->getRequest()->query->getBoolean('oneClickCheckout');

        // No session id but one click checkout requested?
        if (!$checkoutSessionId && $isOneClickCheckout) {
            return;
        }

        // Keep the checkoutSessionId on hold for the case that the customer e.g. changes the shipping method on the confirm page or returns to confirm page later.
        if ($isOneClickCheckout) {
            $checkoutSession = $this->clientProvider->getClient($event->getSalesChannelContext()->getSalesChannelId())->getCheckoutSession($checkoutSessionId);
            //TODO handle wrong status
            $session->set(SwagAmazonPay::CHECKOUT_SESSION_KEY, $checkoutSessionId);
            $this->addConfirmPageExtension($event, $checkoutSession);
            $this->removeOtherPaymentOptions($event);
            return;
        }

        // Reset from One-Click checkout to the regular Amazon Pay checkout
        $session->remove(SwagAmazonPay::CHECKOUT_SESSION_KEY);
    }

    private function addConfirmPageExtension(
        CheckoutConfirmPageLoadedEvent $event,
        CheckoutSession                $checkoutSession
    ): void
    {
        $confirmExtension = new AmazonPayConfirmExtension();
        $confirmExtension->setCheckoutSessionId($checkoutSession->getCheckoutSessionId());
        foreach ($checkoutSession->getPaymentPreferences() as $paymentPreference) {
            if (is_array($paymentPreference) && isset($paymentPreference['paymentDescriptor'])) {
                $confirmExtension->setPaymentDescriptor($paymentPreference['paymentDescriptor']);
                $amazonPayPaymentMethod = $event->getPage()->getPaymentMethods()->get(PaymentMethodInstaller::AMAZON_PAYMENT_ID);
                if ($amazonPayPaymentMethod) {
                    $translations = $amazonPayPaymentMethod->getTranslated();
                    $translations['description'] = $paymentPreference['paymentDescriptor'];
                    $amazonPayPaymentMethod->setTranslated($translations);
                }
                break;
            }
        }
        $confirmExtension->setIsOneClickCheckout(true);
        $event->getPage()->addExtension(AmazonPayConfirmExtension::EXTENSION_NAME, $confirmExtension);
    }

    private function removeOtherPaymentOptions(CheckoutConfirmPageLoadedEvent $event): void
    {
        $filtered = $event->getPage()->getPaymentMethods()->filter(function ($paymentMethod) {
            return $paymentMethod->getId() === PaymentMethodInstaller::AMAZON_PAYMENT_ID;
        });
        if ($filtered->count() > 0) {
            $event->getPage()->setPaymentMethods($filtered);
        }
    }
}
