<?php

MLFilesystem::gi()->loadClass('Hood_Helper_Model_Service_OrderData_Normalize');

class ML_Shopware6Hood_Helper_Model_Service_OrderData_Normalize extends ML_Hood_Helper_Model_Service_OrderData_Normalize {

    protected function normalizeOrder() {
        parent::normalizeOrder();
        if (isset($this->aOrder['Order']['Payed']) && $this->aOrder['Order']['Payed']) {
             $this->aOrder['Order']['PaymentStatus'] = MLModule::gi()->getConfig('paymentstatus.paid');
        }elseif(MLModule::gi()->getConfig('orderimport.paymentstatus') !== null){
            $this->aOrder['Order']['PaymentStatus'] = MLModule::gi()->getConfig('orderimport.paymentstatus');
        }else{
            $this->aOrder['Order']['PaymentStatus'] = 17;//deprecated code , just use for user who configured hood before
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
