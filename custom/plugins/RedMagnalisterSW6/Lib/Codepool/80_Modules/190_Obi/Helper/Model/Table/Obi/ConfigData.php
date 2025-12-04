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

MLFilesystem::gi()->loadClass('Form_Helper_Model_Table_ConfigData_Abstract');

class ML_Obi_Helper_Model_Table_Obi_ConfigData extends ML_Form_Helper_Model_Table_ConfigData_Abstract {

    public function processingTimeField(&$aField) {
        for ($i = 0; $i < 100; $i++) {
            $aField['values'][$i] = $i;
        }
    }

    public function stocksync_fromMarketplaceField(&$aField) {
        $aField['values'] = MLI18n::gi()->get('obi_configform_stocksync_values');
    }

    public function orderstatus_carrierField(&$aField) {
        if (isset($aField['matching'])) {
            $aField = $this->carrierSelect($aField['matching'], $aField);
        }
    }

    public function orderstatus_canceledField(&$aField) {
        $aField['values'] = array(
                '' => MLI18n::gi()->get('ML_LABEL_DONT_USE')) +
            MLFormHelper::getShopInstance()->getOrderStatusValues();

    }

    /**
     * Required cancellation reason if an order should be canceled
     *      @ToDo: Reasons could be fetched from API using GetOptionsConfiguration
     *
     * @param $aField
     * @return void
     * @throws MLAbstract_Exception
     */
    public function orderstatus_cancelReasonField(&$aField) {
        $aField['values'] = MLI18n::gi()->get('obi_configform_orderstatus_cancelreason');
    }

    /**
     * Optional Configuration if order should be set as returned
     *
     * @param $aField
     * @return void
     * @throws MLAbstract_Exception
     */
    public function orderstatus_returnField(&$aField) {
        $aField['values'] = array(
                '' => MLI18n::gi()->get('ML_LABEL_DONT_USE')) +
            MLFormHelper::getShopInstance()->getOrderStatusValues();
    }

    public function deliverytimeField(&$aField) {
        $aField['values'] = array(
                '' => MLI18n::gi()->get('obi_configform_pricaandstock_deliverytime')) +
            MLFormHelper::getShopInstance()->getGroupedAttributesForMatching();
    }

    public function deliverytime_defaultField(&$aField) {
        for ($i=1 ; $i<=100 ; $i++) {
            $aDefaultDeliveryDay[$i] = $i;
        }
        $aField['values'] = $aDefaultDeliveryDay;
    }

    /**
     * Carrier Selection in order config
     *
     * @param $aField
     * @return void
     * @throws MLAbstract_Exception
     */
    public function orderstatus_sendcarrier_selectField(&$aField) {
        if (isset($aField['matching'])) {
            $aField = $this->carrierSelect($aField['matching'], $aField);
        }
    }

    /**
     * Carrier Matching in order config
     *
     * @param $aField
     * @return void
     * @throws MLAbstract_Exception
     */
    public function orderstatus_sendcarrier_matchingField(&$aField) {
        $aField = $this->carrierMatching($aField);
    }

    public function orderstatus_carrier_defaultField(&$aField) {
        $aField['values'] = array('auto' => 'no entry');
    }

    /**
     * Populates the select for carriers
     *
     * @param $matchingElementValue - matching option key value "returnCarrierMatching" must be the same as "matching" value on form fields
     * @param $aField
     * @param $filter
     * @return mixed
     * @throws MLAbstract_Exception
     */
    public function carrierSelect($matchingElementValue, $aField, $filter = 'Carriers') {
        $optGroups = array();
        $marketplaceCarriers = array();
        $matchingElement = array();
        // First element is pure text that explains that nothing is selected so it should not be added
        $aFirstElement = array('' => MLI18n::gi()->get('ML_AMAZON_LABEL_APPLY_PLEASE_SELECT'));

        // Marketplace carriers
        $apiMarketplaceCarriers = MLModule::gi()->getObiOptionsConfiguration($filter);

        foreach ($apiMarketplaceCarriers[$filter] as $key => $marketplaceCarrier) {
            $marketplaceCarriers[$key] = $marketplaceCarrier;
        }
        if (!empty($apiMarketplaceCarriers)) {
            $marketplaceCarriers['optGroupClass'] = 'marketplaceCarriers';
            $optGroups += array(MLI18n::gi()->get('obi_config_carrier_option_group_marketplace_carrier') => $marketplaceCarriers);
        }

        // Free text fields - additional fields
        if (method_exists(MLFormHelper::getShopInstance(), 'getOrderFreeTextFieldsAttributes')) {
            $aShopFreeTextFieldsAttributes = MLFormHelper::getShopInstance()->getOrderFreeTextFieldsAttributes();
            if (!empty($aShopFreeTextFieldsAttributes)) {
                $aShopFreeTextFieldsAttributes['optGroupClass'] = 'freetext';
                $optGroups += array(MLI18n::gi()->get('obi_config_free_text_attributes_opt_group').':' => $aShopFreeTextFieldsAttributes);
            }
        }

        // matching option key value "returnCarrierMatching" must be the same as "matching" value on form fields
        $matchingElement[$matchingElementValue] = MLI18n::gi()->get('obi_config_carrier_option_matching_option');
        $matchingElement['optGroupClass'] = 'matching';
        $optGroups += array(MLI18n::gi()->get('obi_config_carrier_option_group_additional_option') => $matchingElement);

        $aField['values'] = $aFirstElement + $optGroups;
        return $aField;
    }

    /**
     * Populates two dropdowns for select
     *
     * @param $aField
     * @param $carrierType
     * @return mixed
     * @throws MLAbstract_Exception
     */
    public function carrierMatching($aField, $filter = 'Carriers') {
        $shopCarriers = MLFormHelper::getShopInstance()->getShopShippingModuleValues();
        $apiMarketplaceCarriers = MLModule::gi()->getObiOptionsConfiguration($filter);

        $aField['i18n']['matching'] = array(
            'titlesrc' => MLI18n::gi()->get('obi_config_carrier_matching_title_marketplace_carrier'),
            'titledst' => MLI18n::gi()->get('obi_config_carrier_matching_title_shop_carrier')
        );
        $aField['valuesdst'] = array('' => MLI18n::gi()->get('ML_LABEL_DONT_USE'));
        $aField['valuessrc'] = array('' => MLI18n::gi()->get('ML_LABEL_DONT_USE'));

        if (!empty($shopCarriers)) {
            $aField['valuesdst'] = $aField['valuesdst'] + $shopCarriers;

        }
        if (!empty($apiMarketplaceCarriers[$filter])) {
            $aField['valuessrc'] = $aField['valuessrc'] + $apiMarketplaceCarriers[$filter];
        }

        return $aField;
    }

    /**
     * TODO fix validation after fix on system validation
     * Validates matching case for carrier and shipping address options
     *
     * @param $aField
     * @throws MLAbstract_Exception
     */
    private function duplicateValidation($aField, $transKey) {
        if (isset($aField['value']) && is_array($aField['value'])) {
            foreach ($aField['value'] as $value) {
                if ((isset($value['marketplaceCarrier'], $value['shopCarrier']) && ($value['marketplaceCarrier'] == '' || $value['marketplaceCarrier'] == ''))) {
                    $filedSetTrans = MLI18n::gi()->get('obi_config_order');
                    $filedTrans = MLI18n::gi()->get('formfields_obi');

                    $sLegend = isset($filedSetTrans['legend']['orderstatus']) ? $filedSetTrans['legend']['orderstatus'].' > ' : '';
                    $sLabel = isset($filedTrans[$transKey]['label']) ? $filedTrans[$transKey]['label'].' > ' : '';
                    $filed = isset($aField['i18n']['label']) ? $aField['i18n']['label'].' > ' : '';
                    $tableHeaderFirst = isset($aField['i18n']['matching']['titlesrc']) ? $aField['i18n']['matching']['titlesrc'].', ' : '';
                    $tableHeaderSecond = isset($aField['i18n']['matching']['titledst']) ? $aField['i18n']['matching']['titledst'] : '';

                    MLMessage::gi()->addError(
                        MLModule::gi()->getMarketPlaceName(false).'('.MLModule::gi()->getMarketPlaceId().') '.sprintf(
                            ML_CONFIG_FIELD_EMPTY_OR_MISSING,
                            $sLegend.$sLabel.$filed.$tableHeaderFirst.$tableHeaderSecond
                        )
                    );
                    break;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function primaryCategoryField(&$aField) {
        // TODO: Implement primaryCategoryField() method.
    }
}
