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

class ML_Ebay_Model_Service_CacheAPICalls extends ML_Modul_Model_Service_CacheAPICalls_Abstract {
    protected $aCachedCalls = array(
        array('action' => 'IsAuthed'),/* 8 * 60 * 60 hours */
        array('action' => 'CheckIfTokenAvailable'),/* 8 * 60 * 60 hours */
        array('action' => 'HasStore'),/* 8 * 60 * 60 hours */
        array('action' => 'GetListingDurations', 'parameters' => array('DATA' => array('ListingType' => 'Chinese'))),/* 8 * 60 * 60 hours */
        array('action' => 'GetListingDurations', 'parameters' => array('DATA' => array('ListingType' => 'FixedPriceItem'))),/* 8 * 60 * 60 hours */
        array('action' => 'GetListingDurations','parameters' => array('DATA' => array('ListingType' => 'StoresFixedPrice'))),/* 8 * 60 * 60 hours */
        array('action' => 'GeteBayAccountSettings'),/* 8 * 60 * 60 hours */
        array('action' => 'GeteBayOfficialTime'),/* 8 * 60 * 60 hours */
        array('action' => 'GetSellerProfiles'),/* 60 * 60 hours */
        array('action' => 'GetSellerProfileContents'),/* 60 * 60 hours */
        array('action' => 'GetPaymentOptions'),/* 8 * 60 * 60 hours */
        array('action' => 'GetReturnPolicyDetails'),/* 8 * 60 * 60 hours */
        array('action' => 'GetShippingServiceDetails'),/* 8 * 60 * 60 hours */
        array('action' => 'GetShippingDiscountProfiles'),/* 8 * 60 * 60 hours */
        array('action' => 'GetStoreCategories'),/* 8 * 60 * 60 hours */
        array('action' => 'GetCarriers'),/* 8 * 60 * 60 hours */
        array('action' => 'CheckPaymentProgramAvailability'),/* 8 * 60 * 60 hours */
        array('action' => 'GetChildCategories','parameters' => array('DATA' => array("ParentID" => "0")))/* 8 * 60 * 60 hours */
        );

    protected function manipulateParameters($action, $parameters) {
        if ($action === 'GetReturnPolicyDetails' || $action === 'GetShippingServiceDetails' || $action === 'GetPaymentOptions') {
            if (empty($parameters['DATA'])) {
                $parameters['DATA'] = array('Site' => MLModule::gi()->getConfig('site'));
            } else {
                $parameters['DATA'] = array_merge($parameters['DATA'], array('Site' => MLModule::gi()->getConfig('site')));
            }
        }

        return $parameters;
    }

    protected function getListOfAPICallAndParameters() {
        $requests = parent::getListOfAPICallAndParameters();
        $ebayCategoryModel = MLDatabase::factory('ebay_categories', ML_Ebay_Model_Table_Ebay_Categories::class);
        foreach (
            MLDatabase::getDbInstance()->fetchArray($ebayCategoryModel->getTopTenQuery('topPrimaryCategory'), true)
            as $categoryId) {
            $requests[] = array(
                'action' => 'GetConditionValues',
                'parameters' => array('DATA' => array("CategoryID" => $categoryId, 'Site' => MLModule::gi()->getConfig('site')))
            );/* 8 * 60 * 60 hours */
            $requests[] = array(
                'action' => 'GetConditionPolicies',
                'parameters' => array('DATA' => array("CategoryID" => $categoryId, 'Site' => MLModule::gi()->getConfig('site')))
            );/* 8 * 60 * 60 hours */
            $requests[] = array(
                'action' => 'VariationsEnabled',
                'parameters' => array('DATA' => array("CategoryID" => $categoryId, 'Site' => MLModule::gi()->getConfig('site')))
            );/* 8 * 60 * 60 hours */
            $requests[] = array(
                'action' => 'ProductRequired',
                'parameters' => array('DATA' => array("CategoryID" => $categoryId, 'Site' => MLModule::gi()->getConfig('site')))
            );/* 8 * 60 * 60 hours */
            $requests[] = array(
                'action' => 'GetCategoryDetails',
                'parameters' => array('DATA' => array("CategoryID" => $categoryId)));/* 8 * 60 * 60 hours */
        }
        return $requests;
    }
}