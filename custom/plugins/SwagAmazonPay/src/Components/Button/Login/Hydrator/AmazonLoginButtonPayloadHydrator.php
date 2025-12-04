<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Components\Button\Login\Hydrator;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Storefront\Page\PageLoadedEvent;
use Swag\AmazonPay\Components\Button\Login\Struct\AmazonLoginButtonPayloadStruct;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\DataAbstractionLayer\Entity\SignUpToken\SignUpTokenServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AmazonLoginButtonPayloadHydrator implements AmazonLoginButtonPayloadHydratorInterfaceV3
{
    private ConfigServiceInterface $configService;

    private RouterInterface $router;

    private LoggerInterface $logger;

    private SignUpTokenServiceInterface $signUpTokenService;

    public function __construct(
        ConfigServiceInterface      $configService,
        RouterInterface             $router,
        LoggerInterface             $logger,
        SignUpTokenServiceInterface $signUpTokenService
    )
    {
        $this->configService = $configService;
        $this->router = $router;
        $this->logger = $logger;
        $this->signUpTokenService = $signUpTokenService;
    }

    public function hydratePayload(
        string  $salesChannelId,
        Context $context,
        array   $signInScopes = self::DEFAULT_SIGN_IN_SCOPES
    ): string
    {
        $this->logger->warning('\Swag\AmazonPay\Components\Button\Login\Hydrator\AmazonLoginButtonPayloadHydrator::hydratePayload is deprecated use \Swag\AmazonPay\Components\Button\Login\Hydrator\AmazonLoginButtonPayloadHydrator::hydrate instead.');

        $payload = $this->hydrate($salesChannelId, $context, null, $signInScopes);

        return (string)\json_encode($payload, \JSON_UNESCAPED_SLASHES);
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(
        string           $salesChannelId,
        Context          $context,
        ?PageLoadedEvent $event,
        array            $signInScopes = self::DEFAULT_SIGN_IN_SCOPES
    ): ?AmazonLoginButtonPayloadStruct
    {
        try {
            $pluginConfig = $this->configService->getPluginConfig($salesChannelId);
        } catch (ConfigValidationException $e) {
            $this->logger->error('Could not generate Amazon login button payload', ['Exception' => $e->getMessage()]);

            return null;
        }

        $returnUrl = $this->router->generate(
            'frontend.swag_amazon_pay.customer_sign_in',
            [
                'redirectTo' => 'frontend.account.home.page',
                'signUpTokenId' => $this->signUpTokenService->create($context),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $payload = new AmazonLoginButtonPayloadStruct();
        $payload->setSignInReturnUrl($returnUrl);
        $payload->setStoreId($pluginConfig->getClientId());
        $payload->setSignInScopes($signInScopes);

        return $payload;
    }
}
