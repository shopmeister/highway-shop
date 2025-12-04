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

MLFilesystem::gi()->loadClass('Core_Controller_Abstract');

class ML_Ebay_Model_Service_PrepareCache extends ML_Modul_Model_Service_Abstract {

    /**
     * Catches the API calls for ebay prepare form
     *
     * @return void
     */
    public function execute() {
        try {
            // setting cache for HasStore
            $this->log('StartHasStore'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array('ACTION' => 'HasStore'), 60 * 60 * 8);
            $this->log('FinishHasStore'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetListingDurations
            $this->log('StartGetListingDurations'."\n\n", self::LOG_LEVEL_LOW);
            foreach (array('Chinese') as $sListingType) {
                MagnaConnector::gi()->submitRequestCached(array(
                    'ACTION' => 'GetListingDurations',
                    'DATA' => array(
                        'ListingType' => $sListingType
                    )
                ), 60 * 60 * 8);
            }
            $this->log('FinishGetListingDurations'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetSellerProfiles
            $this->log('StartGetSellerProfiles'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetSellerProfiles',
            ), 60 * 60);
                $this->log('FinishGetSellerProfiles'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetSellerProfileContents
            $this->log('StartGetSellerProfileContents'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetSellerProfileContents',
            ), 60 * 60);
            $this->log('FinishGetSellerProfileContents'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetPaymentOptions
            $this->log('StartGetPaymentOptions'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetPaymentOptions',
                'DATA' => array('Site' => MLModule::gi()->getConfig('site')),
            ), 60 * 60 * 8);
            $this->log('FinishGetPaymentOptions'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GeteBayAccountSettings
            $this->log('StartGeteBayAccountSettings'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GeteBayAccountSettings',
            ), 60 * 60 * 8);
            $this->log('FinishGeteBayAccountSettings'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetStoreCategories
            $this->log('StartGetStoreCategories'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetStoreCategories',
            ), 60 * 60 * 8);
            $this->log('FinishGetStoreCategories'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetShippingServiceDetails
            $this->log('StartGetShippingServiceDetails'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetShippingServiceDetails',
                'DATA' => array('Site' => MLModule::gi()->getConfig('site')
                ),), 60 * 60 * 8);
            $this->log('FinishGetShippingServiceDetails'."\n\n", self::LOG_LEVEL_LOW);

            // setting cache for GetShippingDiscountProfiles
            $this->log('StartGetShippingDiscountProfiles'."\n\n", self::LOG_LEVEL_LOW);
            MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetShippingDiscountProfiles'
            ), 60 * 60 * 8);
            $this->log('FinishGetShippingDiscountProfiles'."\n\n", self::LOG_LEVEL_LOW);

        } catch (MagnaExeption $oEx) {
            $this->log($oEx->getMessage(), self::LOG_LEVEL_MEDIUM);
        }

    }


}