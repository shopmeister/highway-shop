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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Form_Helper_Model_Table_ConfigData_Abstract');

class ML_Metro_Helper_Model_Table_Metro_ConfigData extends ML_Form_Helper_Model_Table_ConfigData_Abstract {
    /**
     * @var array|null
     */
    private $crossBorderDefaultSettings = null;

    /**
     * Disable the field, if stock options can't be set due to cross borders limitation.
     *
     * @param array $aField
     * @param string $tooltip
     * @param string|null $default
     * @return void
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     * @throws MagnaException
     */
    private function disableStockOptionWithTooltip(&$aField, $tooltip, $default = null) {
        $module = MLModule::gi();
        /** @var $module ML_Metro_Model_Modul */
        if (!$module->canSetStockOptions()) {
            if (!array_key_exists('i18n', $aField)) {
                $aField['i18n'] = array();
            }
            $aField['i18n']['tooltip'] = $tooltip;
            $aField['disabled'] = true;

            if (null !== $default) {
                $aField['value'] = $default;
            }
        }
    }

    /**
     * Return the default value from the marketplace configuration which is responsible for stock options, if the
     * current is not.
     *
     * @param string $key
     * @return string|null
     */
    private function getStockOptionDisabledDefaultValue($key) {
        $default = null;
        /** @var ML_Metro_Model_Modul $module */
        $module = MLModule::gi();
        if (!$module->canSetStockOptions()) {
            if (null === $this->crossBorderDefaultSettings) {
                /** @var ML_Metro_Helper_Model_CrossBordersConfiguration $crossBorders */
                $crossBorders = MLHelper::gi('Model_CrossBordersConfiguration');
                $this->crossBorderDefaultSettings = $crossBorders->getMarketplace(
                    $crossBorders->getCrossBordersStockOptionsMarketplaceId(
                        $module->getMarketPlaceId()));
            }
            $default = $this->crossBorderDefaultSettings[$key];
        }

        return $default;
    }

    public function processingTimeField(&$aField) {
        for ($i = 0; $i < 100; $i++) {
            $aField['values'][$i] = $i;
        }
    }

    /**
     * Get Values for Configuration - use same as processing time
     *
     * @param $aField
     * @return void
     */
    public function maxProcessingTimeField(&$aField) {
        $this->processingTimeField($aField);

        // remove 0 as value because max processing time needs to be at least 1
        unset($aField['values'][0]);
    }

    /**
     * Adds the combinations for cross borders in the data attributes.
     *
     * @param array $aField
     * @return void
     * @throws MagnaException
     */
    public function shippingdestinationField(&$aField) {
        $response = MagnaConnector::gi()->submitRequest(array(
            'SUBSYSTEM' => 'METRO',
            'ACTION' => 'GetOriginDestinationCombinations'
        ));
        // we got no valid data, stop here
        if (!is_array($response) || !array_key_exists('STATUS', $response)
            || 'SUCCESS' != $response['STATUS']
        ) {
            return;
        }

        if (!array_key_exists('html-data', $aField)) {
            $aField['html-data'] = array();
        }
        $aField['html-data']['origin-destination-combinations'] = htmlspecialchars(json_encode($response['DATA']));
    }

    /**
     * Adds the cross borders information for the JavaScript widget to the shipping origin field.
     *
     * @param array $aField
     * @return void
     */
    public function shippingoriginField(&$aField) {
        /** @var ML_Metro_Model_Modul $module */
        $module = MLModule::gi();

        $config = $module->getConfig();
        $mpID = (int)$module->getMarketPlaceId();;
        /** @var ML_Metro_Helper_Model_CrossBordersConfiguration $crossBorders */
        $crossBorders = MLHelper::gi('Model_CrossBordersConfiguration');
        $crossBordersSettings = $crossBorders->getCrossBorderSettings($mpID);
        foreach ($crossBordersSettings as $key => $marketplace) {
            $tabLabel = '';
            if (!empty($config['tabident'][$marketplace['mpID']])) {
                $tabLabel = $config['tabident'][$marketplace['mpID']];
            }
            if (!$tabLabel) {
                $tabConfig = $crossBorders->getMarketplace($marketplace['mpID']);
                $tabLabel = MLSetting::gi()->get('formgroups_metro__country__fields__shippingdestination__values__'.$tabConfig['shippingdestination']);
            }
            $crossBordersSettings[$key]['tab_label'] = $tabLabel;
            $crossBordersSettings[$key]['tab_link'] = MLHttp::gi()->getUrl(array(
                'controller' => 'metro:'.$marketplace['mpID'].'_config_priceandstock'
            ));
        }

        $aField['clientKey'] = $module->getConfig('clientkey');
        $aField['crossBorders'] = $crossBordersSettings;
    }

    /**
     * Change the parameters for the quantity type field in price/stock settings.
     *
     * Disable the field with a tooltip, if it's not the first metro tab with the same account and origin setting.
     * (Cross borders limitation)
     *
     * @param array $aField
     * @return void
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     * @throws MagnaException
     */
    public function quantity_typeField(&$aField) {
        parent::quantity_typeField($aField);

        $this->disableStockOptionWithTooltip($aField,
            MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP'),
            $this->getStockOptionDisabledDefaultValue('quantity.type'));
    }

    /**
     * Change the parameters for the quantity type field in price/stock settings.
     *
     * Disable the field with a tooltip, if it's not the first metro tab with the same account and origin setting.
     * (Cross borders limitation)
     *
     * @param array $aField
     * @return void
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     * @throws MagnaException
     */
    public function quantity_valueField(&$aField) {
        parent::quantity_valueField($aField);

        $this->disableStockOptionWithTooltip($aField,
            MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP'),
            $this->getStockOptionDisabledDefaultValue('quantity.value'));
    }

    /**
     * Change the parameters for the max quantity stock field in price/stock settings.
     *
     * Disable the field with a tooltip, if it's not the first metro tab with the same account and origin setting.
     * (Cross borders limitation)
     *
     * @param $aField
     * @return void
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     * @throws MagnaException
     */
    public function maxquantityField(&$aField) {
        $this->disableStockOptionWithTooltip($aField,
            MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP'),
            $this->getStockOptionDisabledDefaultValue('maxquantity'));
    }

    /**
     * Change the parameters for the stock sync to marketplace field in price/stock settings.
     *
     * Disable the field with a tooltip, if it's not the first metro tab with the same account and origin setting.
     * (Cross borders limitation)
     *
     * @param $aField
     * @return void
     * @throws MLAbstract_Exception
     * @throws ML_Filesystem_Exception
     * @throws MagnaException
     */
    public function stocksync_toMarketplaceField(&$aField) {
        parent::stocksync_toMarketplaceField($aField);

        $this->disableStockOptionWithTooltip($aField,
            MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP'), 'no');
    }

    public function businessModelField(&$aField) {
        $aField['values'] = array(
            '' => 'B2B / B2C',
            'B2B' => 'B2B',
        );
    }

    public function orderstatus_acceptedField(&$aField) {
        $this->orderstatus_canceledField($aField);
        $aField['values'] = array('auto' => 'Auto Acceptance') + $aField['values'];
    }

    public function orderstatus_cancellationreasonField(&$aField) {
        $aField['values'] = MLModule::gi()->getMetroCancellationReasons();
    }

    public function primaryCategoryField(&$aField) {
        $aRequest = MLRequest::gi()->data();
        if (MLModule::gi()->getMarketPlaceName().':'.MLModule::gi()->getMarketPlaceId().'_prepare_variations' === $aRequest['controller']) {
            $aField['values'] = MLDatabase::factory(MLModule::gi()->getMarketPlaceName().'_variantmatching')->getTopPrimaryCategories();
        } else {
            $aField['values'] = MLDatabase::factory(MLModule::gi()->getMarketPlaceName().'_prepare')->getTopPrimaryCategories();
        }
    }

    public function orderstatus_carrierField(&$aField) {
        $aField['type'] = 'string';
    }

    /**
     * For all shopsystem's without volume prices remove webshop functionality
     *
     * @param $field
     * @return void
     */
    public function volumepricesEnableField(&$field) {
        MLMessage::gi()->addDebug('xy', MLShop::gi()->getShopSystemName());
        if (!in_array(MLShop::gi()->getShopSystemName(), array('shopware', 'shopware6', 'magento', 'magento2', 'prestashop'))) {
            unset($field['values']['webshop']);
        }
    }

    public function volumepricePrice2AddKindField(&$field) {
        if (in_array(MLShop::gi()->getShopSystemName(), array('shopify', 'woocommerce'))) {
            unset($field['values']['customergroup']);
        }
    }

    public function volumepricePrice3AddKindField(&$field) {
        if (in_array(MLShop::gi()->getShopSystemName(), array('shopify', 'woocommerce'))) {
            unset($field['values']['customergroup']);
        }
    }

    public function volumepricePrice4AddKindField(&$field) {
        if (in_array(MLShop::gi()->getShopSystemName(), array('shopify', 'woocommerce'))) {
            unset($field['values']['customergroup']);
        }
    }

    public function volumepricePrice5AddKindField(&$field) {
        if (in_array(MLShop::gi()->getShopSystemName(), array('shopify', 'woocommerce'))) {
            unset($field['values']['customergroup']);
        }
    }

    public function volumepricePriceAAddKindField(&$field) {
        if (in_array(MLShop::gi()->getShopSystemName(), array('shopify', 'woocommerce'))) {
            unset($field['values']['customergroup']);
        }
    }

    public function volumepricePriceBAddKindField(&$field) {
        if (in_array(MLShop::gi()->getShopSystemName(), array('shopify', 'woocommerce'))) {
            unset($field['values']['customergroup']);
        }
    }

    public function volumepricesWebshopCustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

    public function volumepricePrice2CustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

    public function VolumepricePrice3CustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

    public function VolumepricePrice4CustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

    public function VolumepricePrice5CustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

    public function VolumepricePriceACustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

    public function VolumepricePriceBCustomerGroupField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getCustomerGroupValues();
    }

}
