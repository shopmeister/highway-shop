<?php

namespace ShopmasterZalandoConnectorSix\Services\Config;

use ShopmasterZalandoConnectorSix\Struct\Config\Client\ZalandoApiConfigStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormOrderImportStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormPriceReportSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormPriceSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormStockSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormListingStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService implements ConfigServiceInterface
{
    private ?ZalandoApiConfigStruct $zalandoApiConfig = null;

    public function __construct(
        private readonly string              $pluginName,
        private readonly SystemConfigService $configService,
        private readonly EntityRepository    $repositoryCustomField
    )
    {
    }

    public function getZalandoApiConfig(): ZalandoApiConfigStruct
    {
        if (!$this->zalandoApiConfig) {
            $sandbox = $this->configService->getString($this->pluginName . '.config.zalandoSandboxMode');
            $prefix = ($sandbox) ? 'sandboxZalando' : 'zalando';
            $struct = new ZalandoApiConfigStruct();
            $struct->setSandbox($sandbox);
            $struct->setClientId($this->configService->getString($this->pluginName . '.config.' . $prefix . 'ClientId'));
            $struct->setClientSecret($this->configService->getString($this->pluginName . '.config.' . $prefix . 'ClientSecret'));
            $struct->setMerchantId($this->configService->getString($this->pluginName . '.config.' . $prefix . 'MerchantId'));
            $this->zalandoApiConfig = $struct;
        }
        return $this->zalandoApiConfig;
    }

    public static function getSalesChannelIdByCountryCode(string $getCountryCode): string
    {
        return self::SALES_CHANNELS[$getCountryCode];
    }

    public function getImportOrderConfigBySalesChannelId(string $salesChannelId): SettingFormOrderImportStruct
    {
        $key = SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId(SettingsFormStruct::FORM_TYPES['ORDER_IMPORT'], $salesChannelId);
        $config = $this->configService->get($key);
        if (!empty($config['returnTrackingCustomField'])) {
            $customFieldEntity = $this->getCustomFieldById($config['returnTrackingCustomField']);
            if ($customFieldEntity) {
                $config['returnTrackingCustomField'] = $customFieldEntity->getName();
            }
        }

        return new SettingFormOrderImportStruct($config);
    }

    public function getStockSyncConfig(string $salesChannelId = 'global'): SettingFormStockSyncStruct
    {
        $key = SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId(SettingsFormStruct::FORM_TYPES['STOCK_SYNC'], $salesChannelId);
        $config = $this->configService->get($key);
        return new SettingFormStockSyncStruct($config);
    }

    public function getPriceSyncConfig(string $salesChannelId): SettingFormPriceSyncStruct
    {
        $key = SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId(SettingsFormStruct::FORM_TYPES['PRICE_SYNC'], $salesChannelId);
        $config = $this->configService->get($key);
        return new SettingFormPriceSyncStruct($config);
    }

    public function getPriceReportSyncConfig(string $salesChannelId): SettingFormPriceReportSyncStruct
    {
        $key = SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId(SettingsFormStruct::FORM_TYPES['PRICE_REPORT_SYNC'], $salesChannelId);
        $config = $this->configService->get($key);
        return new SettingFormPriceReportSyncStruct($config);
    }

    public function getListingConfig(string $salesChannelId): SettingFormListingStruct
    {
        $key = SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId(SettingsFormStruct::FORM_TYPES['LISTING'], $salesChannelId);
        $config = $this->configService->get($key);
        return new SettingFormListingStruct($config);
    }

    public function getFormBySalesChannelAndType(string $salesChannelId, string $type): ?array
    {
        $key = SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId(
            SettingsFormStruct::FORM_TYPES[strtoupper($type)] ?? $type, 
            $salesChannelId
        );
        return $this->configService->get($key);
    }

    private function getCustomFieldById(string $returnTrackingCustomField): ?CustomFieldEntity
    {
        return $this->repositoryCustomField->search(new Criteria([$returnTrackingCustomField]), Context::createDefaultContext())->first();
    }

}