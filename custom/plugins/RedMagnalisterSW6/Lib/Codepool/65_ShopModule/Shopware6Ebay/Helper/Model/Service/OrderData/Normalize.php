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
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Ebay_Helper_Model_Service_OrderData_Normalize');

class ML_Shopware6Ebay_Helper_Model_Service_OrderData_Normalize extends ML_Ebay_Helper_Model_Service_OrderData_Normalize {

    protected function normalizeOrder() {
        parent::normalizeOrder();
        if (isset($this->aOrder['Order']['Payed']) && $this->aOrder['Order']['Payed']) {
             $this->aOrder['Order']['PaymentStatus'] = MLModule::gi()->getConfig('paymentstatus.paid');
        }elseif(MLModule::gi()->getConfig('orderimport.paymentstatus') !== null){
            $this->aOrder['Order']['PaymentStatus'] = MLModule::gi()->getConfig('orderimport.paymentstatus');
        }else{
            $this->aOrder['Order']['PaymentStatus'] = 17;//deprecated code , just use for user who configured ebay before
        }
        return $this;
    }
    protected function normalizeAddressSets () {
        $address = !empty($this->aOrder['AddressSets']['Shipping']['StreetAddress']) 
            ? $this->aOrder['AddressSets']['Shipping']['StreetAddress'] 
            : $this->aOrder['AddressSets']['Shipping']['Street'] . ' ' . $this->aOrder['AddressSets']['Shipping']['Housenumber'];
        if (strpos($address, 'Packstation') === 0) {
            $this->aOrder['AddressSets']['Shipping']['Street'] = $address;
            $this->aOrder['AddressSets']['Shipping']['Housenumber'] = '0';
        }
        parent::normalizeAddressSets();
        return $this;
    }
    
}
