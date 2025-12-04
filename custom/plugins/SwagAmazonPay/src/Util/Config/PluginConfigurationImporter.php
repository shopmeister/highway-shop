<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Config;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\AmazonPay\Administration\Controller\Exception\PluginConfigurationImportVersionMismatchException;

class PluginConfigurationImporter implements PluginConfigurationImporterInterface
{
    private EntityRepository $salesChannelRepository;

    private LoggerInterface $pluginLogger;

    private SystemConfigService $systemConfigService;

    private VersionProviderInterface $versionHelper;

    public function __construct(
        SystemConfigService      $systemConfigService,
        EntityRepository         $salesChannelRepository,
        LoggerInterface          $pluginLogger,
        VersionProviderInterface $versionHelper
    )
    {
        $this->salesChannelRepository = $salesChannelRepository;
        $this->pluginLogger = $pluginLogger;
        $this->systemConfigService = $systemConfigService;
        $this->versionHelper = $versionHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $config, Context $context, bool $ignoreVersions = false): void
    {
        if (\array_key_exists('versionInfo', $config) && $ignoreVersions === false) {
            if ($config['versionInfo'] !== $this->versionHelper->getVersions($context)) {
                throw new PluginConfigurationImportVersionMismatchException([
                    'current' => $this->versionHelper->getVersions($context),
                    'state' => $config['versionInfo'],
                ]);
            }
        }

        foreach ($config as $salesChannelId => $configurationCollection) {
            if ($salesChannelId === 'versionInfo' || \is_int($salesChannelId)) {
                continue;
            }

            $loggerContext = [
                'salesChannelId' => $salesChannelId,
                'config' => $configurationCollection,
            ];

            if ($salesChannelId === 'fallback') {
                $salesChannelId = null;
            }

            // Import configs only for currently existing sales channels.
            if (!$this->hasSalesChannel((string)$salesChannelId, $context)) {
                $this->pluginLogger->info(\sprintf('Skipping configuration import for not existing sales channel %s', (string)$salesChannelId), $loggerContext);

                continue;
            }

            $this->pluginLogger->info(\sprintf('Importing configuration for sales channel %s', (string)$salesChannelId), $loggerContext);

            $this->upsertConfig($configurationCollection, $salesChannelId);
        }
    }

    private function upsertConfig(array $config, ?string $salesChannelId): void
    {
        foreach ($config as $key => $value) {
            $this->systemConfigService->set($key, $value, $salesChannelId);
        }
    }

    private function hasSalesChannel(?string $salesChannelId, Context $context): bool
    {
        if (empty($salesChannelId)) {
            return true;
        }

        return $this->salesChannelRepository->searchIds(new Criteria([$salesChannelId]), $context)->getTotal() > 0;
    }
}
