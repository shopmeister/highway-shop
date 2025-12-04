<?php

namespace ShopmasterZalandoConnectorSix\Struct\Config\Settings;

use ShopmasterZalandoConnectorSix\Struct\Struct;

class SettingsFormStruct extends Struct
{
    const FORM_TYPES = [
        'ORDER_IMPORT' => 'orderImport',
        'STOCK_SYNC' => 'stockSync',
        'PRICE_SYNC' => 'priceSync',
        'PRICE_REPORT_SYNC' => 'priceReportSync',
        'LISTING' => 'listing',
    ];

    public function __construct(?array $data = null)
    {
        if (!$data) {
            return;
        }
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * @param string $type
     * @param string $salesChannelId
     * @return string
     */
    public static function getConfigKeyByTypeAndSalesChannelId(string $type, string $salesChannelId): string
    {
        return 'ShopmasterZalandoConnectorSix.settings.form.' . $type . '.' . $salesChannelId;
    }
}