<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLSetting::gi()->get('configuration');//throws exception if not exists
MLSetting::gi()->add('configuration__sku__fields__shopware6sku',
    array(
        'i18n'    => '{#i18n:general_shopware6_master_sku_migration_options#}',
        'name'    => 'general.shopware6.master.sku.migration.options',
        'type'    => 'bool',
        'default' => false,
    )
);
MLSetting::gi()->add('configuration__orderimport__fields__shopware6flowskipped',
    array(
        'i18n'    => '{#i18n:general_shopware6_flow_skipped#}',
        'name'    => 'general.shopware6flowskipped',
        'type'    => 'bool',
        'default' => false,
        'expert'  => true
    )
);