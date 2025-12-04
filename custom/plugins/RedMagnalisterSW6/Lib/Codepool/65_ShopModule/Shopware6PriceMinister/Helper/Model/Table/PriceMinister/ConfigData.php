<?php
/**
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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLFilesystem::gi()->loadClass('PriceMinister_Helper_Model_Table_PriceMinister_ConfigData');

class ML_Shopware6PriceMinister_Helper_Model_Table_PriceMinister_ConfigData extends ML_PriceMinister_Helper_Model_Table_PriceMinister_ConfigData {
    
    public function paymentstatusField (&$aField) {
        $aField['values'] = MLFormHelper::getShopInstance()->getPaymentStatusValues();
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

    public function orderstatus_carrierField (&$aField) {
        $aField = $this->selectWithMatchingOptionsFromTypeValueGenerator(array(
            'marketplaceCarrier',
            'shopFreeTextField', // only shopware 5 & 6
            'matchShopShippingOptions',
            'orderFreeTextField',
//            'freeText',
        ), $aField, 'carrier');
    }
}
