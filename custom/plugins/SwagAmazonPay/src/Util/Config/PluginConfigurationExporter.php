<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Config;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\System\SystemConfig\Exception\InvalidDomainException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;

readonly class PluginConfigurationExporter implements PluginConfigurationExporterInterface
{
    public function __construct(
        private EntityRepository $salesChannelRepository,
        private SystemConfigService $configService,
        private VersionProviderInterface $versionHelper
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     * @throws InvalidDomainException
     */
    public function export(Context $context): array
    {
        $activeSalesChannelIds = $this->getActiveSalesChannelIds();

        $result = [
            'fallback' => $this->filterExport(
                $this->configService->getDomain(ConfigServiceInterface::CONFIG_DOMAIN)
            ),
            'versionInfo' => $this->versionHelper->getVersions($context),
        ];

        foreach ($activeSalesChannelIds as $salesChannelId) {
            if (!\is_string($salesChannelId)) {
                continue;
            }

            $result[$salesChannelId] = $this->filterExport(
                $this->configService->getDomain(ConfigServiceInterface::CONFIG_DOMAIN, $salesChannelId, true)
            );
        }

        return $result;
    }

    /**
     * Removes unwanted elements from config export
     */
    private function filterExport(array $config): array
    {
        unset($config[ConfigServiceInterface::CONFIG_DOMAIN . '.privateKey']);

        return $config;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     *
     * @return array[]|string[]
     */
    private function getActiveSalesChannelIds(): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', '1'));

        return $this->salesChannelRepository->searchIds($criteria, Context::createDefaultContext())->getIds();
    }
}
