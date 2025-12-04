<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\Button\Pay\Hydrator;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\AmazonPay\Components\Button\Pay\AddressRestriction\AddressRestrictionServiceInterface;
use Swag\AmazonPay\Components\Button\Pay\Struct\AmazonPayButtonPayloadStruct;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class AmazonPayButtonPayloadHydrator implements AmazonPayButtonPayloadHydratorInterfaceV2
{
    public function __construct(
        private ConfigServiceInterface $configService,
        private RouterInterface $router,
        private LoggerInterface $logger,
        private AddressRestrictionServiceInterface $addressRestrictionService,
        private EntityRepository $currencyRepository
    ) {
    }

    public function hydrate(string $salesChannelId, bool $oneClickCheckout = true): string
    {
        $this->logger->warning('You are using the deprecated \Swag\AmazonPay\Components\Button\Pay\Hydrator\AmazonPayButtonPayloadHydrator::hydrate function use \Swag\AmazonPay\Components\Button\Pay\Hydrator\AmazonPayButtonPayloadHydrator::hydratePayload instead.');

        $payload = $this->hydratePayload($salesChannelId, Context::createDefaultContext(), $oneClickCheckout);

        return (string) \json_encode($payload, \JSON_UNESCAPED_SLASHES | \JSON_FORCE_OBJECT);
    }

    public function hydratePayload(string $salesChannelId, Context $context, bool $oneClickCheckout = true, ?string $customReviewUrl = null): ?AmazonPayButtonPayloadStruct
    {
        try {
            $pluginConfig = $this->configService->getPluginConfig($salesChannelId);
        } catch (ConfigValidationException $e) {
            $this->logger->error('Could not generate Amazon login button payload', ['Exception' => $e->getMessage()]);

            return null;
        }

        $payload = new AmazonPayButtonPayloadStruct();
        $payload->setCheckoutReviewReturnUrl($customReviewUrl ?: $this->router->generate('payment.swag_amazon_pay.review', ['oneClickCheckout' => $oneClickCheckout], UrlGeneratorInterface::ABSOLUTE_URL));
        $payload->setStoreId($pluginConfig->getClientId());
        $payload->setAddressRestrictions($this->addressRestrictionService->getAddressRestrictions($salesChannelId, $context));
        $currency = $this->currencyRepository->search(new Criteria([$context->getCurrencyId()]), $context)->first();
        $payload->setCurrency($currency->getIsoCode());

        return $payload;
    }
}
