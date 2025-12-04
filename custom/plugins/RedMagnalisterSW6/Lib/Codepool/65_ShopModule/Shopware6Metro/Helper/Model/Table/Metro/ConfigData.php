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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Metro_Helper_Model_Table_Metro_ConfigData');

class ML_Shopware6Metro_Helper_Model_Table_Metro_ConfigData extends ML_Metro_Helper_Model_Table_Metro_ConfigData {

    public function orderstatus_carrierField(&$aField) {
        $aField = $this->selectWithMatchingOptionsFromTypeValueGenerator(array(
//            'marketplaceCarrier',
            'shopFreeTextField', // only shopware 5 & 6
//            'matchShopShippingOptions',
//            'orderFreeTextField',
//            'freeText',
            'defaultFieldValue',
        ), $aField, 'carrier', 'GetCarriers');

    }

    /**
     * Set config values - shop document types
     *
     * @param $aField
     * @return void
     */
    public function invoice_invoice_documentTypeField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getDocumentTypeValues();
    }

    /**
     * Set config values - shop document types
     *
     * @param $aField
     * @return void
     */
    public function invoice_creditNote_documentTypeField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getDocumentTypeValues();
    }
    public function orderimport_paymentmethodField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getPaymentMethodValues();
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' .$this->getFieldId('orderimport.shop'),
            'trigger' => 'change',
            'field' => array(
                'disableditems' =>MLFormHelper::getShopInstance()->getPaymentMethodValuesNotConfiguredInSalesChannel(),
                'type' => 'select'
            ),

        );
    }

    public function orderimport_shippingmethodField(&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getShippingMethodValues();
        $aField['type'] = 'ajax';
        $aField['ajax'] = array(
            'selector' => '#' .$this->getFieldId('orderimport.shop'),
            'trigger' => 'change',
            'field' => array(
                'disableditems' =>MLFormHelper::getShopInstance()->getShopShippingModuleValuesNotConfiguredInSalesChannel(),
                'type' => 'select'
            ),

        );
    }

}
