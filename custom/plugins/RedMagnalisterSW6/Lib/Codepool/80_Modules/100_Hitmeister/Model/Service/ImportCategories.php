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

MLFilesystem::gi()->loadClassWithPrefix('ML_Hitmeister_Model_Table_Hitmeister_CategoriesMarketplace');

class ML_Hitmeister_Model_Service_ImportCategories extends ML_Modul_Model_Service_ImportCategories_Abstract {

    protected $sTableName = 'magnalister_hitmeister_categories_marketplace';

    protected $aTableColumns = array(
        'CategoryID',
        'CategoryName',
        'ParentID',
        'LeafCategory',
        'Selectable',
        'Fee',
        'FeeCurrency',
        'Site',
        'ImportOrUpdateTime'
    );

    protected function getGetCategoriesRequest() {
        $aRequest = array(
            'ACTION' => 'GetCategories',
            'OFFSET' => (ctype_digit(MLRequest::gi()->data('offset'))) ? (int)MLRequest::gi()->data('offset') : 0,
            'LIMIT' => ((int)MLRequest::gi()->data('maxitems') > 0) ? (int)MLRequest::gi()->data('maxitems') : $this->iImportCategoriesLimit,
        );

        if ((int)MLRequest::gi()->data('steps') > 0) {
            $aRequest['steps'] = (int)MLRequest::gi()->data('steps');
        }

        return $aRequest;
    }

    protected function extendCategoryData(&$dCategories, $timestamp) {
        $sSite = ML_Hitmeister_Model_Table_Hitmeister_CategoriesMarketplace::getSite();

        foreach ($dCategories as &$dCategory) {
            $dCategory['ImportOrUpdateTime'] = $timestamp;
            $dCategory['Site'] = $sSite;
            $dCategory['Expires'] = date('Y-m-d H:i:s', time() + ML_Hitmeister_Model_Table_Hitmeister_CategoriesMarketplace::iExpiresLiveTime);
        }
    }

    protected function deleteOldCategories($timestamp) {
        $sSite = ML_Hitmeister_Model_Table_Hitmeister_CategoriesMarketplace::getSite();

        // Only delete old categories for the current site
        MLDatabase::getDbInstance()->query(
            'DELETE FROM `'.$this->sTableName.'`
             WHERE ImportOrUpdateTime <> "'.$timestamp.'"
             AND Site = "'.MLDatabase::getDbInstance()->escape($sSite).'"'
        );
    }
}
