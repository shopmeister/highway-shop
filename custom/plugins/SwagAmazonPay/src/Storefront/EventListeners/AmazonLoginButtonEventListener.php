<?php declare(strict_types=1);

namespace Swag\AmazonPay\Storefront\EventListeners;

use Shopware\Storefront\Page\Account\Login\AccountLoginPageLoadedEvent;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Swag\AmazonPay\Components\Button\ButtonProviderInterface;
use Swag\AmazonPay\Storefront\Page\Extension\AbstractAmazonButtonExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AmazonLoginButtonEventListener implements EventSubscriberInterface
{
    private ButtonProviderInterface $buttonProvider;

    public function __construct(
        ButtonProviderInterface $buttonProvider
    ) {
        $this->buttonProvider = $buttonProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AccountLoginPageLoadedEvent::class => 'addAmazonLoginButtonExtension',
            AccountProfilePageLoadedEvent::class => 'addAmazonLoginButtonExtension',
        ];
    }

    /**
     * @param AccountLoginPageLoadedEvent|AccountProfilePageLoadedEvent $event
     */
    public function addAmazonLoginButtonExtension(PageLoadedEvent $event): void
    {
        $buttonExtension = $this->buttonProvider->getAmazonLoginButton($event);

        if ($buttonExtension !== null) {
            $event->getPage()->addExtension(AbstractAmazonButtonExtension::EXTENSION_NAME, $buttonExtension);
        }
    }
}
