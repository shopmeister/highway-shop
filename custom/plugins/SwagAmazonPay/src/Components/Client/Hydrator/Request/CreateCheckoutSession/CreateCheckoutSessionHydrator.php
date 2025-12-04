<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\Client\Hydrator\Request\CreateCheckoutSession;

use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CreateCheckoutSessionHydrator implements CreateCheckoutSessionHydratorInterface
{
    public const CUSTOMER_CANCELLED_PARAMETER = 'customerCancelled';

    private ConfigServiceInterface $configService;

    private RouterInterface $router;

    public function __construct(
        ConfigServiceInterface $configService,
        RouterInterface        $router
    )
    {
        $this->configService = $configService;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(bool $oneClickCheckout = false, string $storeId = '', ?string $salesChannelId = null): array
    {
        if (empty($storeId)) {
            $storeId = $this->configService->getPluginConfig($salesChannelId)->getClientId();
        }

        return [
            'storeId' => $storeId,
            'webCheckoutDetails' => $this->hydrateWebCheckoutDetails($oneClickCheckout),
            'platformId' => ConfigServiceInterface::PLATFORM_ID,
        ];
    }

    private function hydrateWebCheckoutDetails(bool $oneClickCheckout): array
    {
        return [
            'checkoutReviewReturnUrl' => $this->router->generate('payment.swag_amazon_pay.review', ['oneClickCheckout' => $oneClickCheckout], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }
}
