<?php

namespace ShopmasterZalandoConnectorSix\Controllers\Api\Settings;

use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormOrderImportStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormPriceReportSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormPriceSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormStockSyncStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\Form\SettingFormListingStruct;
use ShopmasterZalandoConnectorSix\Struct\Config\Settings\SettingsFormStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RoutePrefix(/api/_action/shopmaster_zalando_connector)
 */
#[Route(defaults: ['_routeScope' => ["administration", "api"]])]
class FormSettingsController extends AbstractController
{
    /**
     * @param SystemConfigService $configService
     */
    public function __construct(
        readonly private SystemConfigService $configService
    )
    {
    }

    #[Route('/settings/form/{salesChannelId}/{type}',
        name: 'api.action.shopmaster_zalando_connector.settings.form.getFormBySalesChannelAndType',
        methods: ['GET'],
    )]
    public function getFormBySalesChannelAndType(Request $request, Context $context): JsonResponse
    {
        $form = $this->getForm($request);
        return new JsonResponse($form);
    }

    #[Route('/settings/form/{salesChannelId}/{type}',
        name: 'api.action.shopmaster_zalando_connector.settings.form.saveForm',
        methods: ['POST'],
    )]
    public function saveForm(Request $request, Context $context): JsonResponse
    {
        $key = $this->getConfigKey($request);
        $data = json_decode($request->getContent(), true);
        $this->configService->set($key, $data);
        return new JsonResponse(null, 204);
    }

    /**
     * @param Request $request
     * @return SettingsFormStruct|null
     */
    private function getForm(Request $request): ?SettingsFormStruct
    {
        $key = $this->getConfigKey($request);
        $config = $this->configService->get($key);
        $type = $request->get('type');
        return $this->dataToFormStruct($config, $type);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getConfigKey(Request $request): string
    {
        $salesChannelId = $request->get('salesChannelId');
        $type = $request->get('type');
        return SettingsFormStruct::getConfigKeyByTypeAndSalesChannelId($type, $salesChannelId);
    }

    /**
     * @param array|null $config
     * @param string $type
     * @return SettingsFormStruct|null
     */
    private function dataToFormStruct(?array $config, string $type): ?SettingsFormStruct
    {
        return match ($type) {
            SettingsFormStruct::FORM_TYPES['ORDER_IMPORT'] => new SettingFormOrderImportStruct($config),
            SettingsFormStruct::FORM_TYPES['STOCK_SYNC'] => new SettingFormStockSyncStruct($config),
            SettingsFormStruct::FORM_TYPES['PRICE_SYNC'] => new SettingFormPriceSyncStruct($config),
            SettingsFormStruct::FORM_TYPES['PRICE_REPORT_SYNC'] => new SettingFormPriceReportSyncStruct($config),
            SettingsFormStruct::FORM_TYPES['LISTING'] => new SettingFormListingStruct($config),
            default => null,
        };
    }


}