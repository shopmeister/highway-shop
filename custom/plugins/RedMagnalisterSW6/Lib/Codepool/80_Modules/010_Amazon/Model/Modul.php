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
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class ML_Amazon_Model_Modul extends ML_Modul_Model_Modul_Abstract {

    protected $amazonSite = null;
    protected $currency = null;


    public function isConfigured() {
        $bReturn = parent::isConfigured();
        if ($bReturn) {
            if (!$this->ifCurrencyAvailable()) {
                return false;
            }
            $this->checkShippingTemplate();
        }

        return $bReturn;
    }
    
    /**
     *
     * @var ML_Shop_Model_Price_Interface $oPrice 
     */
    protected $oPrice = null;

    public function getMarketPlaceName($blIntern = true){
        return $blIntern ? 'amazon' : MLI18n::gi()->get('sModuleNameAmazon');
    }

    public function getStockConfig($sType = null) {
        return array(
            'type' => $this->getConfig('quantity.type'),
            'value' => $this->getConfig('quantity.value'),
            'max' => $this->getConfig('maxquantity'),
        );
    }

    public function getPublicDirLink(){
        $aResponse=MagnaConnector::gi()->submitRequestCached(array(
            'ACTION'=>'GetPublicDir',
        ), 8 * 60 * 60);
        if(isset($aResponse['DATA']) && $aResponse['STATUS']=='SUCCESS'){
            return $aResponse['DATA'];
        }else{
            throw new Exception('GetPublicDir');
        }
    }

    public function getMainCategories() {
        static $aCategoriesCache = null;
        if ($aCategoriesCache !== null) {
            return $aCategoriesCache;
        }
        $aCategories = array();
        try {
            $aResponse = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetAllProductTypes',
            ), 8 * 60 * 60);
            if (isset($aResponse['DATA'])) {
                $aCategories = $aResponse['DATA'];
            }
            $aCategoriesCache = $aCategories;
        } catch (MagnaException $e) {
            //echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
        }
        return $aCategories;
    }

    public function getProductTypesAndAttributes($sCategory) {
        try {
            $aRequest = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetProductTypesAndAttributes',
                'CATEGORY' => $sCategory
            ), 24 * 60 * 60);
        } catch (MagnaException $e) {
        }

        if (isset($aRequest['DATA'])) {
            $aOut = $aRequest['DATA'];
        } else {
            $aOut = array(
                'ProductTypes' => array('null' => MLI18n::gi()->ML_AMAZON_ERROR_APPLY_CANNOT_FETCH_SUBCATS),
                'Attributes' => false
            );
        }

        return $aOut;
    }

    /**
     * @param $productType
     * @param bool|string $mNewResponse (string "ALL")
     * @return array
     */
    public function getBrowseNodes($productType, $mNewResponse = 'ALL') {
        static $aBrowseNodesCache = array();
        if (isset($aBrowseNodesCache[$productType][$mNewResponse])) {
            return $aBrowseNodesCache[$productType][$mNewResponse];
        }
        $aRequest = array();

        try {
            $aRequest = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetBrowseNodes',
                'CATEGORY' => $productType,
                'NewResponse' => $mNewResponse,
                'Version' => 2
            ), 24 * 60 * 60);
            $aBrowseNodesCache[$productType][$mNewResponse] = isset($aRequest['DATA']) ? $aRequest['DATA'] : array();
        } catch (MagnaException $e) {
        }

        return isset($aRequest['DATA']) ? $aRequest['DATA'] : array();
    }
    
    /**
     * @return array('configKeyName'=>array('api'=>'apiKeyName', 'value'=>'currentSantizedValue'))
     */
    protected function getConfigApiKeysTranslation() {
        $sDate = $this->getConfig('preimport.start');
        //magento tip to find empty date
        $sDate = (preg_replace('#[ 0:-]#', '', $sDate) ==='') ? date('Y-m-d') : $sDate;
        $sDate = date('Y-m-d', strtotime($sDate));
        $sSync = $this->getConfig('stocksync.tomarketplace');

        return array(
            'import'                                       => array('api' => 'Orders.Import', 'value' => ($this->getConfig('import'))),
            'preimport.start'                              => array('api' => 'Orders.Import.TS', 'value' => $sDate),
            'stocksync.tomarketplace'                      => array('api' => 'Callback.SyncInventory', 'value' => isset($sSync) ? $sSync : 'no'),
            'amazonvcs.option'                             => array('api' => 'AmazonVCS.Option', 'value' => $this->getConfig('amazonvcs.option')),
            'amazonvcs.invoice'                            => array('api' => 'AmazonVCS.InvoiceOption', 'value' => $this->getConfig('amazonvcs.invoice')),
            'amazonvcsinvoice.mailcopy'                    => array('api' => 'AmazonVCS.MailCopy', 'value' => $this->getConfig('amazonvcsinvoice.mailcopy')),
            'amazonvcsinvoice.language'                    => array('api' => 'AmazonVCS.Language', 'value' => $this->getConfig('amazonvcsinvoice.language')),
            'amazonvcsinvoice.invoicenumberoption'         => array('api' => 'AmazonVCS.InvoiceNumberOption', 'value' => $this->getConfig('amazonvcsinvoice.invoicenumberoption')),
            'amazonvcsinvoice.invoiceprefix'               => array('api' => 'AmazonVCS.InvoicePrefix', 'value' => $this->getConfig('amazonvcsinvoice.invoiceprefix')),
            'amazonvcsinvoice.reversalinvoiceprefix'       => array('api' => 'AmazonVCS.ReversalInvoicePrefix', 'value' => $this->getConfig('amazonvcsinvoice.reversalinvoiceprefix')),
            'amazonvcsinvoice.reversalinvoicenumberoption' => array('api' => 'AmazonVCS.ReversalInvoiceNumberOption', 'value' => $this->getConfig('amazonvcsinvoice.reversalinvoicenumberoption')),
            'amazonvcsinvoice.companyadressleft'           => array('api' => 'AmazonVCS.CompanyAddressLeft', 'value' => $this->getConfig('amazonvcsinvoice.companyadressleft')),
            'amazonvcsinvoice.companyadressright'          => array('api' => 'AmazonVCS.CompanyAddressRight', 'value' => $this->getConfig('amazonvcsinvoice.companyadressright')),
            'amazonvcsinvoice.headline'                    => array('api' => 'AmazonVCS.Headline', 'value' => $this->getConfig('amazonvcsinvoice.headline')),
            'amazonvcsinvoice.invoicehintheadline'         => array('api' => 'AmazonVCS.InvoiceHintHeadline', 'value' => $this->getConfig('amazonvcsinvoice.invoicehintheadline')),
            'amazonvcsinvoice.invoicehinttext'             => array('api' => 'AmazonVCS.InvoiceHintText', 'value' => $this->getConfig('amazonvcsinvoice.invoicehinttext')),
            'amazonvcsinvoice.footercell1'                 => array('api' => 'AmazonVCS.FooterCell1', 'value' => $this->getConfig('amazonvcsinvoice.footercell1')),
            'amazonvcsinvoice.footercell2'         => array('api' => 'AmazonVCS.FooterCell2', 'value' => $this->getConfig('amazonvcsinvoice.footercell2')),
            'amazonvcsinvoice.footercell3'         => array('api' => 'AmazonVCS.FooterCell3', 'value' => $this->getConfig('amazonvcsinvoice.footercell3')),
            'amazonvcsinvoice.footercell4'         => array('api' => 'AmazonVCS.FooterCell4', 'value' => $this->getConfig('amazonvcsinvoice.footercell4')),
            'orderimport.fbablockimport'         => array('api' => 'Orders.Import.AmazonFbaBlockImport', 'value' => $this->getConfig('orderimport.fbablockimport')),
        );
    }
    
    public function getCurrencies(){
        $aCurrencies = array();
        foreach ($this->getMarketPlaces() as $aMarketplace) {
            $aCurrencies[$aMarketplace['Key']] = fixHTMLUTF8Entities($aMarketplace['Currency']);
        }
        return $aCurrencies;
    }

    public function getMarketPlaces() {
        static $aMarketplaces = null;
        try {
            if ($aMarketplaces === null) {
                $aRequest = MagnaConnector::gi()->submitRequestCached(array(
                    'ACTION' => 'GetMarketplaces'
                ), 24 * 60 * 60);
                $aMarketplaces = isset($aRequest['DATA']) ? $aRequest['DATA'] : array();
            }
        } catch (MagnaException $e) {
            $aMarketplaces = array();
        }

        return $aMarketplaces;
    }

    public function getCarrierCodes() {
        try {
            $aRequest = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION'        => 'GetCarrierCodes',
                'SUBSYSTEM'     => 'Amazon',
                'MARKETPLACEID' => $this->getMarketPlaceId(),
            ), 24 * 60 * 60);
        } catch (MagnaException $e) {
            
        }
        return isset($aRequest['DATA']) ? $aRequest['DATA'] : array();
    }
    
    public function amazonLookUp($sSearch) {
        $searchResults = array();
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'ItemLookup',
                'ASIN' => $sSearch
            ));
            if (!empty($result['DATA'])) {
                $searchResults = array_merge($searchResults, $result['DATA']);
            }
        } catch (MagnaException $e) {
            $e->setCriticalStatus(false);
        }
        return $searchResults ;
    }

    public function amazonSearch($sSearch) {
        $searchResults = array();
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                        'ACTION' => 'ItemSearch',
                        'NAME' => $sSearch
            ));
            if (!empty($result['DATA'])) {
                $searchResults = array_merge($searchResults, $result['DATA']);
            }
        } catch (MagnaException $e) {
            $e->setCriticalStatus(false);
        }
        return $searchResults ;
    }

    public function performItemSearch($asin, $ean, $productsName) {
        if (!is_string($ean)) {
            $ean = '';
        }
        $sCacheId = __FUNCTION__ . '_' . md5(json_encode(array($asin, $ean, $productsName)));
        try {
            $searchResults = MLCache::gi()->get($sCacheId);
        } catch (ML_Filesystem_Exception $oEx) {
            $searchResults = array();
            
            if (!empty($asin)) {
                $searchResults = $this->amazonSearch($asin);
            }

            $ean = str_replace(array(' ', '-'), '', $ean);
            if (!empty($ean)) {
                $searchResults = array_merge($searchResults, $this->amazonSearch($ean));
            }

            if (!empty($productsName)) {
                $searchResults = array_merge($searchResults, $this->amazonSearch($productsName));
            }
            if (!empty($searchResults)) {
                $searchResults = array_map('unserialize', array_unique(array_map('serialize', $searchResults)));

                //for testing without Amazon api
            //$searchResults = $this->getTestSearchResults();
                foreach ($searchResults as &$data) {
                    if (!empty($data['Author'])) {
                        $data['Title'] .= ' (' . $data['Author'] . ')';
                    }
                    if (!empty($data['LowestPrice']['Price']) && !empty($data['LowestPrice']['CurrencyCode']) && 
                    in_array($data['LowestPrice']['CurrencyCode'], array_keys(MLCurrency::gi()->getList()))) {
                        $data['LowestPriceFormated'] = MLPrice::factory()->format($data['LowestPrice']['Price'], $data['LowestPrice']['CurrencyCode']); //$price->format();
                        $data['LowestPrice'] = $data['LowestPrice']['Price'];
                    } {
                        $data['LowestPriceFormated'] = '&mdash;';
                        $data['LowestPrice'] = '-';
                    }
                }
            }
            MLCache::gi()->set($sCacheId, $searchResults, 60 * 60 * 2);
        }
        return $searchResults;
    }
    
    /**
     * Returns test search results for Amazon products
     * 
     * @return array Array of test Amazon product data
     */
    protected function getTestSearchResults() {
        return array(
            array(
                'ASIN' => 'B00TEST123',
                'Title' => 'The Complete Guide to Amazon Selling',
                'Author' => 'John Smith',
                'LowestPrice' => array(
                    'Price' => '19.99',
                    'CurrencyCode' => 'EUR'
                ),
                'EAN' => '9781234567890',
                'ISBN' => '1234567890123'
            ),
            array(
                'ASIN' => 'B00TEST456',
                'Title' => 'Mastering E-Commerce',
                'Author' => 'Jane Doe',
                'LowestPrice' => array(
                    'Price' => 0,
                    'CurrencyCode' => ''
                ),
                'EAN' => '9789876543210',
                'ISBN' => '9876543210123'
            ),
            array(
                'ASIN' => 'B00TEST789',
                'Title' => 'Product Listing Optimization',
                // No Author field to test that condition
                'LowestPrice' => array(
                    'Price' => 10,
                    // Missing Price to test that condition
                    'CurrencyCode' => 'ABC'
                ),
                'EAN' => '9780246813579',
                'ISBN' => '0246813579123'
            )
        );
    }
    
    public function MfsGetConfigurationValues($sType = null) {
        try {
            $aResponse = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'MFS_GetConfigurationValues',
            ), 6 * 60 * 60);
            if (array_key_exists('DATA', $aResponse)) {
                if ($sType === null) {
                    return $aResponse['DATA'];
                } elseif (array_key_exists($sType, $aResponse['DATA'])) {
                    return $aResponse['DATA'][$sType];
                } else {
                    return $sType;
                }
            } else {
                return array();
            }
        } catch (Exception $oEx) {
            return array();
        }
    }

    public function isMultiPrepareType() {
        return true;
    }

    /**
     * Hint: for Prestashop and Shopify we do not check for any other status during status sync then these below
     *
     * @return string[]
     */
    public function getStatusConfigurationKeyToBeConfirmedOrCanceled() {
        return array(
            'orderstatus.shippedaddress',
            'orderstatus.cancelled',
        );
    }

    //    public function isAttributeMatchingNotMatchOptionImplemented() {
    //        return true;
    //    }


    public function GetAmazonTimezones() {
        try {
            $aResponse = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetAmazonTimezones',
            ), 6 * 60 * 60);
            return $aResponse['DATA'];
        } catch (Exception $oEx) {
            return array();
        }
    }

    public function getMerchantDetails($blCachePurge = false) {
        $merchantDetails = MagnaConnector::gi()->submitRequestCached(array(
            'ACTION' => 'GetMerchantDetails',
        ), 60 * 60 * 24 * 30,
            $blCachePurge);
        if (isset($merchantDetails['DATA'])) {
            return $merchantDetails['DATA'];
        }
    }
    public function verifyItemByMarketplaceToGetMandatoryAttributes($category, $variationTheme = null) {
        if (empty($category) || $category === 'none') {

            MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array());
            return array();
        }

        try {
            static $aCache = array();
            if (isset($aCache[$category])) {
                return $aCache[$category];
            }
            //here we cannot use MagnaConnector::gi()->submitRequestCached
            //because we need to get errors by catch block,
            // and the request with exception won't be cached in MagnaConnector
            $cacheKey = __FUNCTION__ . '_' . md5($category.(!empty($variationTheme)?'_'.$variationTheme:''));
            if(MLCache::gi()->exists($cacheKey)){
                $data =  MLCache::gi()->get($cacheKey);
            }else {
                $data = array();
                $requestParams = array(
                    'ACTION'             => 'VerifyAddItems',
                    'MODE' => 'ATTRIBUTE_MATCHING',
                    'DATA' => array(
                        array(
                            'MainCategory'    => $category,
                            'variation_theme' => array(
                                $variationTheme => array()
                            ),
                        )
                    )
                );
                try {
                    MagnaConnector::gi()->submitRequest($requestParams);
                } catch (MagnaException $oEx) {
                    $data = $oEx->getResponse();
                    MLCache::gi()->set($cacheKey, $data, 60 * 60 * 24);
                }
            }
            $aCache[$category] = isset($data['ERRORS']) && is_array($data['ERRORS']) ? $data['ERRORS'] : array();
            return $aCache[$category];
        } catch (Exception $oEx) {
            MLMessage::gi()->addDebug($oEx);
        }
        return array();
    }
    /**
     * @return string
     * @throws Exception
     */
    protected function getCurrencyOfAmazonSite() {
        static $isCurrencySaved = false;
        $aFields = MLRequest::gi()->data('field');

        if ($this->amazonSite === null) {
            $this->amazonSite = MLDatabase::factory('config')->set('mpid', $this->getMarketPlaceId())->set('mkey', 'site')->get('value');
        }

        if (!MLHttp::gi()->isAjax() && $aFields !== null && isset($aFields['site'])) { // saving new site in configuration
            $this->amazonSite = $aFields['site'];
            $this->currency = null;
        }
        //get the currency from magnalister API
        $aCurrencies = $this->getCurrencies();
        $sCurrency = null;
        if (!empty($this->amazonSite) && array_key_exists($this->amazonSite, $aCurrencies)) {
            $sCurrency = $aCurrencies[$this->amazonSite];
        }
        if (!$isCurrencySaved && !empty($sCurrency)) {
            MLDatabase::factory('config')->set('mpId', $this->getMarketPlaceId())->set('mkey', 'currency')->set('value', $sCurrency)->save();
            if (MLDatabase::factory('config')->set('mpId', $this->getMarketPlaceId())->set('mkey', 'currency')->get('value', $sCurrency) === $sCurrency) {
                $isCurrencySaved = true;
            }
        }
        return $sCurrency;
    }

    public function getConfig($sName = null) {
        $mValue = parent::getConfig($sName);
        if ($mValue === null && $sName === 'currency') {
            $mValue = $this->getCurrencyOfAmazonSite();
        } else if ($sName === null && is_array($mValue)) {
            $mValue['currency'] = $this->getCurrencyOfAmazonSite();
        }
        return $mValue;
    }

    protected function ifCurrencyAvailable() {
        if ($this->currency === null) {
            $this->currency = MLDatabase::factory('config')->set('mpid', $this->getMarketPlaceId())->set('mkey', 'currency')->get('value');
        }

        if (empty($this->currency)) {
            $this->currency = $this->getCurrencyOfAmazonSite();
            if (!empty($this->currency)) {
                $this->setConfig('currency', $this->currency);
            }
        }
        if (!empty($this->currency) && !in_array($this->currency, array_keys(MLCurrency::gi()->getList()))) {
            MLMessage::gi()->addWarn(sprintf(MLI18n::gi()->ML_AMAZON_ERROR_CURRENCY_NOT_IN_SHOP, $this->currency));
            return false;
        } else {
            //Set the currency in to the config data and get it from MLModule::gi()->getConfig('currency');
            if ($this->getConfig('currency') !== $this->currency) {
                $this->setConfig('currency', $this->currency);
            }
        }
        return true;
    }

    protected function checkShippingTemplate() {
        $aTemplateName = MLModule::gi()->getConfig('shipping.template.name');
        if ($this->isShippingTemplateFilled($aTemplateName)) {
            $aFields = MLRequest::gi()->data('field');
            if (!MLHttp::gi()->isAjax() && $aFields !== null && isset($aFields['shipping.template.name'])) { // saving new shipping template in configuration
                $aTemplateName = $aFields['shipping.template.name'];
            }
        }
        if ($this->isShippingTemplateFilled($aTemplateName)) {
            $sMarketplace = MLModule::gi()->getModuleBaseUrl();
            MLMessage::gi()->addWarn('<a href="'.MLHTTP::gi()->getUrl(array('controller' => $sMarketplace . '_config_prepare')).'">'.MLI18n::gi()->ML_AMAZON_ERROR_TEMPLATENAME_EMPTY.'</a>');
        }
    }

    protected function isShippingTemplateFilled($aTemplateName) {
        $blEmptyTemplate = true;
        if (is_array($aTemplateName)) {
            foreach ($aTemplateName as $iKey => $sValue) {
                if ($sValue !== '') {
                    $blEmptyTemplate = false;
                    break;
                }
            }
        }
        return $blEmptyTemplate;
    }

    public function getPriceObject($sType = null) {
        if ($sType === 'b2b') {
            $sKind = $this->getConfig('b2b.price.addkind');
            if (isset($sKind)) {
                $fFactor = (float)$this->getConfig('b2b.price.factor');
                $iSignal = $this->getConfig('b2b.price.signal');
                $iSignal = $iSignal === '' ? null : (int)$iSignal;
                $blSpecial = $this->getConfig('b2b.price.usespecialoffer');
                $sGroup = $this->getConfig('b2b.price.group');
                $oPrice = MLPrice::factory()->setPriceConfig($sKind, $fFactor, $iSignal, $sGroup, $blSpecial);
                return $oPrice;
            }
        }
        return parent::getPriceObject($sType);
    }


    public function getListOfConfigurationKeysNeedShopValidationOnlyActive() {
        if (self::$aListOfConfigurationKeysNeedShopValidationOnlyActive === null) {
            $aReturn = array(
                'price.group' => 'config' . $this->getPriceConfigurationUrlPostfix(),
                'orderimport.paymentmethod' => 'config_orderimport',
                'orderimport.shippingmethod'=> 'config_orderimport',
                'customergroup' => 'config_orderimport',
                'lang' => 'config_prepare',
            );
            $aFields = MLRequest::gi()->data('field');
            if (!MLHttp::gi()->isAjax() && $aFields !== null && isset($aFields['b2bactive'])) {
                $blB2bPriceActive = $aFields['b2bactive'] === 'true';
            } else {
                $blB2bPriceActive = $this->getConfigAndDefaultConfig('b2bactive') === 'true';
            }
            if ($blB2bPriceActive) {
                $aReturn['b2b.price.group'] = 'config_price';
            }
            //MLMessage::gi()->addDebug(__LINE__ . ':' . microtime(true), array($blB2bPriceActive, $aReturn));
            self::$aListOfConfigurationKeysNeedShopValidationOnlyActive = $aReturn;
        }

        return self::$aListOfConfigurationKeysNeedShopValidationOnlyActive;
    }
}
