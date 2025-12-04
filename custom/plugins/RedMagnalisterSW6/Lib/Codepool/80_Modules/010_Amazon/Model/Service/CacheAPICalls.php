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

class ML_Amazon_Model_Service_CacheAPICalls extends ML_Modul_Model_Service_CacheAPICalls_Abstract {
    protected $aCachedCalls = array(
        array('action' => 'GetMarketplaces'),/* 24 * 60 * 60 */
        array('action' => 'IsAuthed'),/* 8 * 60 * 60 hours */
        //array('action' => 'GetShopInfo'),
        array('action' => 'GetConditionTypes'),
        array('action' => 'GetB2BProductTaxCode', 'parameters' => array('CATEGORY' => "")),/* 8 * 60 * 60 hours */
        array('action' => 'GetAllProductTypes'),/* 8 * 60 * 60 hours */
    );

    protected function getListOfAPICallAndParameters() {
        $requests = parent::getListOfAPICallAndParameters();

        // Huge performance impact on API because of thousands of requests and a lot of data that will be cached like (~1500 categories)
//        foreach (MLModule::gi()->getMainCategories() as $category => $label) {
//            $requests[] = array(
//                'action' => 'GetProductTypesAndAttributes',
//                'parameters' => array('CATEGORY' =>  $category)
//            );
//            $requests[] = array(
//                'action' => 'GetCategoryDetails',
//                'parameters' => array('PRODUCTTYPE' =>  $category)
//            );
//            $requests[] = array(
//                'action' => 'GetBrowseNodes',
//                'parameters' => array(
//                    'CATEGORY' =>  $category,
//                    'NewResponse' =>  'ALL',
//                    'Version' => 2,
//                )
//            );
//        }

        // only cache the most used categories (top 10)
        $categoryAndProductType = MLDatabase::getDbInstance()->fetchArray("
            SELECT `topMainCategory`
              FROM `magnalister_amazon_prepare`
             WHERE      `mpID` = '".MLModule::gi()->getMarketPlaceId()."'
                    AND `topMainCategory` <> '0'
          GROUP BY `topMainCategory`
          ORDER BY COUNT(*) DESC
             LIMIT 10
        ");

        if (is_array($categoryAndProductType)) {
            foreach ($categoryAndProductType as $row) {
                $requests[] = array(
                    'action' => 'GetProductTypesAndAttributes',
                    'parameters' => array(
                        'CATEGORY' => $row['topMainCategory']
                    )
                );
                $requests[] = array(
                    'action' => 'GetCategoryDetails',
                    'parameters' => array(
                        'PRODUCTTYPE' => $row['topMainCategory']
                    )
                );
                $requests[] = array(
                    'action' => 'GetBrowseNodes',
                    'parameters' => array(
                        'CATEGORY' =>  $row['topMainCategory'],
                        'NewResponse' =>  'ALL',
                        'Version' => 2,
                    )
                );
            }
        }
        return $requests;
    }


}
