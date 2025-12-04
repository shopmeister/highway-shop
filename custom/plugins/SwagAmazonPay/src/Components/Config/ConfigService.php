<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\Config;

use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\System\SystemConfig\Exception\InvalidDomainException;
use Shopware\Core\System\SystemConfig\SystemConfigEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\AmazonPay\Components\Config\Hydrator\ConfigHydratorInterface;
use Swag\AmazonPay\Components\Config\Struct\AmazonPayConfigStruct;
use Swag\AmazonPay\Components\Config\Validation\ConfigValidatorInterface;

readonly class ConfigService implements ConfigServiceInterface
{
    public function __construct(
        private SystemConfigService      $configService,
        private ConfigValidatorInterface $configValidator,
        private ConfigHydratorInterface  $configHydrator,
        private EntityRepository         $systemConfigRepository
    )
    {
    }

    private function hasInheritance(): bool
    {
        return !$this->configService->get('SwagAmazonPay.settings.noInheritance');
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConfigValidationException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     * @throws InvalidDomainException
     */
    public function getPluginConfig(?string $salesChannelId = null, bool $skipValidation = false): AmazonPayConfigStruct
    {
        if(!$this->hasInheritance()) {
            $salesChannelId = null;
        }

        $config = $this->getConfigTraceable($salesChannelId);
        $config = \array_merge(self::DEFAULT_CONFIG, $config);

        $inheritConfigKey = 'inheritFromDefault';

        if ($salesChannelId !== null && \array_key_exists($inheritConfigKey, $config) && $config[$inheritConfigKey]) {
            $config = $this->getConfigTraceable(null);
            $config = \array_merge(self::DEFAULT_CONFIG, $config);
        }

        if ($skipValidation) {
            $this->configValidator->validate($config);
        }

        return $this->configHydrator->hydrate($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigEntityByMerchantId(string $merchantId, Context $context): ?SystemConfigEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('configurationKey', ConfigServiceInterface::CONFIG_DOMAIN . '.merchantId'),
            new EqualsFilter('configurationValue', $merchantId)
        );

        return $this->systemConfigRepository->search($criteria, $context)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemConfig(string $key, ?string $salesChannelId = null): mixed
    {
        return $this->configService->get($key, $salesChannelId);
    }

    public function getSoftDescriptor(?string $salesChannelId = null): string
    {
        $pluginConfig = $this->getPluginConfig($salesChannelId);
        $softDescriptor = $pluginConfig->getSoftDescriptor();

        if (!empty($softDescriptor)) {
            return $softDescriptor;
        }

        return \sprintf('AMZ* %s', $this->getSystemConfig('core.basicInformation.shopName'));
    }

    /**
     * Uses \Shopware\Core\System\SystemConfig\SystemConfigService::get to get the plugin config traceable for the \Shopware\Core\Framework\Adapter\Cache\CacheTracer
     */
    private function getConfigTraceable(?string $salesChannelId): array
    {
        $config = [];
        foreach (\array_keys(self::DEFAULT_CONFIG) as $key) {
            $config[$key] = $this->configService->get(
                \sprintf(
                    '%s.%s',
                    self::CONFIG_DOMAIN,
                    $key
                ),
                $salesChannelId
            );
        }

        return $config;
    }
}
