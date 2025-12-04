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

class ML_Obi_Model_Service_SyncInventory extends ML_Modul_Model_Service_SyncInventory_Abstract {



    protected $iPriceNumberOfDecimalPlace = 2;
    /**
     * no need to upload
     * @return ML_Modul_Model_Service_SyncInventory_Abstract|void
     */
    protected function uploadItems() {

    }

    protected function extendUpdateItemDataForItem(&$aUpdate) {
        $config = MLModule::gi()->getConfig('deliverytime');
        if (!empty($config)) {
            $aUpdate['DeliveryTime'] = ($this->oProduct->getAttributeValue(MLModule::gi()->getConfig('deliverytime')));
        }

        if (empty($aUpdate['DeliveryTime']) && MLModule::gi()->getConfig('deliverytime_default') !== null) {
            $aUpdate['DeliveryTime'] = MLModule::gi()->getConfig('deliverytime_default');
        }
    }

    /**
     * @param ML_Shop_Model_Product_Abstract $oProduct
     * @param array $aResponse api-response of current product
     * @return array for request eg. array('price' => (float))
     *
     *
     * The price can be null at OBI then isset returns false, therefore OBI checks whether the array key exists
     */
    protected function getPrice(ML_Shop_Model_Product_Abstract $oProduct, $aResponse) {
        $aPrice = array();
        if (array_key_exists('Price', $aResponse)) {
            $aPrice['Price'] = $oProduct->getSuggestedMarketplacePrice(MLModule::gi()->getPriceObject(), true);
        }
        return $aPrice;
    }

    /**
     * @param mixed $fPriceValue
     * @param $aItem
     * @param int|string $sPriceType
     * @param bool $blPriceChanged
     */
    public function compareProductPrice($fPriceValue, $aItem, $sPriceType, $blPriceChanged) {
        if (array_key_exists($sPriceType, $aItem) && ($aItem[$sPriceType] != $fPriceValue)) {
            $blPriceChanged = true;
        }
        return $blPriceChanged;
    }
}
