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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class ML_Etsy_Model_Modul extends ML_Modul_Model_Modul_Abstract {

    public function getMarketPlaceName($blIntern = true) {
        return $blIntern ? 'etsy' : MLI18n::gi()->get('sModuleNameEtsy');
    }

    public function isConfigured() {
        $bReturn = parent::isConfigured();
        $sCurrency = $this->getConfig('currency');
        $aFields = MLRequest::gi()->data('field');
        if(!MLHttp::gi()->isAjax() && $aFields !== null && isset($aFields['currency']) ){ // saving new site in configuration
            $sCurrency = $aFields['currency'];
        }

        if (!empty($sCurrency) && !in_array($sCurrency, array_keys(MLCurrency::gi()->getList()))) {
            MLMessage::gi()->addWarn(sprintf(MLI18n::gi()->ML_GENERIC_ERROR_CURRENCY_NOT_IN_SHOP , $sCurrency));
            return false;
        }

        return $bReturn;
    }

    protected function getDefaultConfigValues() {
        return array_merge(parent::getDefaultConfigValues(), array('customersync' => 1));
    }

    public function getStockConfig($sType = null) {
        $max = $this->getConfig('maxquantity');
        return array(
            'type' => $this->getConfig('quantity.type'),
            'value' => $this->getConfig('quantity.value'),
            'max' => $max > 0 ? $max : null
        );
    }

    /**
     * @return array('configKeyName'=>array('api'=>'apiKeyName', 'value'=>'currentSantizedValue'))
     */
    protected function getConfigApiKeysTranslation() {
        $sDate = $this->getConfig('preimport.start');
        //magento tip to find empty date
        $sDate = (preg_replace('#[ 0:-]#', '', $sDate) === '') ? date('Y-m-d') : $sDate;
        $sDate = date('Y-m-d', strtotime($sDate));
        $sSync = $this->getConfig('stocksync.tomarketplace');
        return array(
            'import' => array('api' => 'Orders.Import', 'value' => ($this->getConfig('import') ? 'true' : 'false')),
            'preimport.start' => array('api' => 'Orders.Import.Start', 'value' => $sDate),
            'stocksync.tomarketplace' => array('api' => 'Callback.SyncInventory', 'value' => isset($sSync) ? $sSync : 'no'),
        );
    }

    public function getEtsyShippingSettings() {
        $request = array(
            'ACTION' => 'GetShippingOptions'
        );

        try {
            $result = MagnaConnector::gi()->submitRequestCached($request);

            if (isset($result['DATA'])) {
                return $result['DATA'];
            }
        } catch (MagnaException $e) {
        }
        return array('noselection' => MLI18n::gi()->ML_ERROR_API);
    }

    public function saveShippingProfile() {
        $aData = MLRequest::gi()->data();
        $results = array(
            'title'               => $aData['title'],
            'origin_country_iso'   => $aData['originCountry'],
            'destination_country_iso' => $aData['destinationCountry'],
            'destination_region'  => $aData['destinationRegion'],
            'primary_cost'        => $aData['primaryCost'],
            'secondary_cost'      => $aData['secondaryCost'],
            'min_processing_time' => $aData['minProcessingTime'],
            'max_processing_time' => $aData['maxProcessingTime'],
            'min_delivery_days'   => $aData['minDeliveryDays'],
            'max_delivery_days'   => $aData['maxDeliveryDays'],
            'origin_postal_code'  => $aData['originPostalCode'],
        );
        MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'SaveShippingProfile',
            'DATA'   => $results
        ));
    }

    public function isAuthed($blResetCache = false) {
        if (MLSetting::gi()->blSkipMarketplaceIsAuthed) {
            return true;
        }
        $isAuthed = parent::isAuthed($blResetCache) && $this->tokenAvailable();

        // Show processing profile update hint to all authenticated customers
        if ($isAuthed && !$this->getConfig('processingprofile.update.hint.dismissed')) {
            $sHintText = MLI18n::gi()->get('ML_ETSY_PROCESSING_PROFILE_UPDATE_HINT');
            MLMessage::gi()->addNotice($sHintText, array('md5' => 'etsy_processing_profile_update_hint'));
        }

        return $isAuthed;
    }

    public function tokenAvailable($blResetCache = false) {
        $sCacheKey = strtoupper(__class__).'__'.$this->getMarketPlaceId().'_ebaytoken';
        $oCache = MLCache::gi();
        if ($blResetCache) {
            $oCache->delete($sCacheKey);
        }
        if (!$oCache->exists($sCacheKey) || !((bool)$oCache->get($sCacheKey))) {
            $blToken = false;
            try {
                $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'CheckIfTokenAvailable'
                ));
                if ('true' == $result['DATA']['TokenAvailable']) {
                    $this->setConfig('token', '__saved__');
                    $this->setConfig('token.expires', $result['DATA']['TokenExpirationTime']);
                    if (array_key_exists('OauthTokenExpirationTime', $result['DATA'])) {
                        // actually, it's the expiration time for the "refresh token" - but we handle these things within the API (the customer only needs to know when it's time to renew the auth process)
                        $this->setConfig('oauth.token.expires', $result['DATA']['OauthTokenExpirationTime']);
                    }
                    $blToken = true;
                }
            } catch (MagnaException $e) {
            }
            $oCache->set($sCacheKey, $blToken, 60 * 15);
        }
        return (bool)$oCache->get($sCacheKey);
    }

    public function getCategoryDetails($sCategoryId) {
        $aCategoryDetails = MagnaConnector::gi()->submitRequestCached(
            array(
                'ACTION' => 'GetCategoryDetails',
                'DATA' => array(
                    'CategoryID' => $sCategoryId,
                    'Language' => $this->getConfig('shop.language'),
                )
            ), 60 * 60 * 24
        );

        $attributesCopy = $aCategoryDetails['DATA']['attributes'];
        foreach ($attributesCopy as $key => $aAttribute) {
            if (empty($aAttribute['values'])) {
                $aCategoryDetails['DATA']['attributes'][$key]['type'] = 'text';
            }
            if (isset($aAttribute['supportsVariations']) && isset($aAttribute['supportsAttributes'])) {
                if ($aAttribute['supportsAttributes']) {
                    $aCategoryDetails['DATA']['attributes']['Extra_'.$key] = $aAttribute;
                }
                if (!$aAttribute['supportsVariations']) {
                    unset($aCategoryDetails['DATA']['attributes'][$key]);
                }
            }
        }
        return $aCategoryDetails;
    }
    public function getListOfConfigurationKeysNeedShopValidationOnlyActive() {
        return array(
            'orderimport.paymentmethod' => 'config_orderimport' ,
            'orderimport.shippingmethod'=> 'config_orderimport' ,
            'lang' => 'config_prepare',
        );
    }

    public function getListOfReadinessStates() {
        return array(
            'ready_to_ship' => MLI18n::gi()->{'etsy_config_item_preparation_readiness_state_ready_to_ship'},
            'made_to_order' => MLI18n::gi()->{'etsy_config_item_preparation_readiness_state_made_to_order'}
        );
    }

    public function saveProcessingProfile() {
        $aData = MLRequest::gi()->data();
        $results = array(
            'readiness_state'     => $aData['readinessState'],
            'min_processing_time' => $aData['minProcessingTime'],
            'max_processing_time' => $aData['maxProcessingTime']
        );
        MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'SaveProcessingProfile',
            'DATA'   => $results
        ));
    }
}
