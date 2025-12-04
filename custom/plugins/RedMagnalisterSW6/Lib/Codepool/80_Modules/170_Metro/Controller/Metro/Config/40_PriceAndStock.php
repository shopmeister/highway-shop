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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_ConfigAbstract');

class ML_Metro_Controller_Metro_Config_PriceAndStock extends ML_Form_Controller_Widget_Form_ConfigAbstract {
    /**
     * Creates the cross borders limitation warning, if necessary.
     *
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     */
    public function __construct() {
        parent::__construct();

        /** @var $module ML_Metro_Model_Modul */
        $module = MLModule::gi();
        if (!$module->canSetStockOptions()) {
            $config = $module->getConfig();
            $mpID = $module->getCrossBordersStockOptionsMpid($module->getMarketPlaceId());

            MLMessage::gi()->addWarn(MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_WARNING', [
                'TAB_LABEL' => !empty($config['tabident'][$mpID])
                    ? $config['tabident'][$mpID]
                    // fallback to the shipping destination country label, if no tab label is set
                    : MLSetting::gi()->get('formgroups_metro__country__fields__shippingdestination__values__'.$config['shippingdestination']),
                'TAB_LINK' => MLHttp::gi()->getUrl(array(
                    'controller' => 'metro:'.$mpID.'_config_priceandstock'
                ))
            ]));
        }
    }


    public static function getTabTitle() {
        return MLI18n::gi()->get('metro_config_account_sync');
    }

    public static function getTabActive() {
        return self::calcConfigTabActive(__class__, false);
    }

    /**
     * Save the form data.
     *
     * It will also check to correctly set metro cross border settings between multiple tabs with the same client and
     *  origin settings.
     *
     * @param bool $blExecute
     * @return array[]|ML_Metro_Controller_Metro_Config_PriceAndStock
     * @throws MagnaException
     */
    public function saveAction($blExecute = true) {
        $return = parent::saveAction($blExecute);

        if (!$blExecute) {
            return $return;
        }


        /** @var $module ML_Metro_Model_Modul */
        $module = MLModule::gi();
        /** @var ML_Metro_Helper_Model_CrossBordersConfiguration $crossBorders */
        $crossBorders = MLHelper::gi('Model_CrossBordersConfiguration');
        // if the current marketplace tab is responsible for stock settings, it needs to update every marketplace
        // tab configuration with the same client and origin settings
        if ($module->canSetStockOptions() && 'auto' == $this->aRequestFields['stocksync.tomarketplace']) {
            // update cross border settings from form input
            $crossBorders
                ->set($module->getMarketPlaceId(), 'maxquantity', $this->aRequestFields['maxquantity'])
                ->set($module->getMarketPlaceId(), 'quantity.type', $this->aRequestFields['quantity.type'])
                ->set($module->getMarketPlaceId(), 'quantity.value', $this->aRequestFields['quantity.value']);
            $cross_border_settings = $crossBorders
                ->getMarketplace($module->getMarketPlaceId());
            foreach ($crossBorders->iterateSameCrossBorderMarketplaces($module->getMarketPlaceId()) as $marketplace) {
                // update settings in database
                foreach (array('maxquantity', 'quantity.type', 'quantity.value') as $key) {
                    MLDatabase::getDbInstance()->update('magnalister_config', [
                        'value' => $cross_border_settings[$key]
                    ], [
                        'mpID' => $marketplace['mpID'],
                        'mkey' => $key
                    ]);
                }

                // update settings in api
                MagnaConnector::gi()->submitRequest(array(
                    'MARKETPLACEID' => $marketplace['mpID'],
                    'ACTION' => 'SavePluginConfig',
                    'DATA' => loadDBConfig($marketplace['mpID']),
                ));
            }
        }

        return $return;
    }

}
