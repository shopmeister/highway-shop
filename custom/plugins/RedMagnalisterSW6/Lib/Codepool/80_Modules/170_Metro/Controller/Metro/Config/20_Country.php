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

/**
 * Controller to configure country settings.
 */
class ML_Metro_Controller_Metro_Config_Country extends ML_Form_Controller_Widget_Form_ConfigAbstract {
    /**
     * Original shipping origin value before saving the new one.
     *
     * @var string|null
     */
    protected $shippingOriginOrig = null;

    /**
     * Return the tab title.
     *
     * @return string
     * @throws MLAbstract_Exception
     */
    public static function getTabTitle() {
        return MLI18n::gi()->get('metro_config_country_title');
    }

    /**
     * Return if the tab is active.
     *
     * @return bool
     * @throws Exception
     */
    public static function getTabActive() {
        return self::calcConfigTabActive(__class__, false);
    }

    /**
     * Save the original value for shipping origin.
     *
     * It will be used to determine if stock fields will be enabled for this marketplace.
     */
    public function construct() {
        parent::construct();

        /** @var ML_Metro_Model_Modul $module */
        $module = MLModule::gi();
        $this->shippingOriginOrig = $module->getConfig('shippingorigin');
    }

    /**
     * Save the form data.
     *
     * It will also check to correctly set metro cross border settings between multiple tabs with the same client and
     * origin settings.
     *
     * @param bool $blExecute
     * @return array[]|ML_Metro_Controller_Metro_Config_Country
     */
    public function saveAction($blExecute = true) {
        $return = parent::saveAction($blExecute);

        if (!$blExecute) {
            return $return;
        }

        /** @var ML_Metro_Model_Modul $module */
        $module = MLModule::gi();
        // check if another marketplace tab is responsible for the stock setting options
        /** @var ML_Metro_Helper_Model_CrossBordersConfiguration $crossBorders */
        $crossBorders = MLHelper::gi('Model_CrossBordersConfiguration');
        $config = $crossBorders->getMarketplace($module->getMarketPlaceId());
        $stockOptionMarketplace = $crossBorders->getOtherStockMarketplaceForSettings(
            $module->getMarketPlaceId(), $config['clientkey'],
            $this->aRequestFields['shippingorigin']);

        // if there is another marketplace responsible for stock setting options, save settings from that
        // marketplace configuration and turn off stock synchronization
        if ($stockOptionMarketplace) {
            $data = array(
                'stocksync.tomarketplace' => 'no',
                'quantity.type' => $stockOptionMarketplace['quantity.type'],
                'quantity.value' => $stockOptionMarketplace['quantity.value'],
                'maxquantity' => $stockOptionMarketplace['maxquantity'],
            );
            foreach ($data as $key => $value) {
                MLDatabase::getDbInstance()->update('magnalister_config', array(
                    'value' => $value,
                ), array(
                    'mpID' => $module->getMarketPlaceId(),
                    'mkey' => $key
                ));
            }
        } else {
            // if the shipping origin changed from a country where the stock fields were disabled to a country where
            // they will be active again, redirect the user to the price and stock tab and highlight the fields
            $shippingOrigin = $module->getConfig('shippingorigin');
            if ($this->shippingOriginOrig && $shippingOrigin != $this->shippingOriginOrig) {
                $crossBordersSettings = $crossBorders->getCrossBorderSettings($module->getMarketPlaceId());
                if (array_key_exists($config['clientkey'].'|'.$this->shippingOriginOrig, $crossBordersSettings)
                    && !array_key_exists($config['clientkey'].'|'.$shippingOrigin, $crossBordersSettings)
                ) {
                    MLHttp::gi()->redirect(array(
                        'controller' => 'metro:'.$module->getMarketPlaceId().'_config_priceandstock',
                        'highlight_fields' => 'quantity.type,quantity.value,maxquantity,stocksync.tomarketplace',
                    ));
                }
            }
        }

        return $return;
    }
}
