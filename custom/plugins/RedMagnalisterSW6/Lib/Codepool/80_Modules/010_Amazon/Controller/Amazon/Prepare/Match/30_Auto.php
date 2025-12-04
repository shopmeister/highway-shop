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

MLFilesystem::gi()->loadClass('Core_Controller_Abstract');

class ML_Amazon_Controller_Amazon_Prepare_Match_Auto extends ML_Core_Controller_Abstract {

    protected $aParameters = array('controller');

    public function callAjaxAmazonAutoMatching() {
        $autoMatchingStats = $this->AutoMatchProduct();
        $blFinalStep = $autoMatchingStats['iCurrent'] ===  (int)$autoMatchingStats['aStatistic']['iCountTotal'] && isset($autoMatchingStats['aStatistic']['iCountTotal']);
        $aMessage = array(
            'success' =>
                MLRequest::gi()->data('saveSelection') == 'true'?
                    false :$blFinalStep,
            'error' => false,
            'offset' => $autoMatchingStats['iCurrent'],
            'info' => array(
                'current' => $autoMatchingStats['iCurrent'],
                'total' => $autoMatchingStats['aStatistic']['iCountTotal'],
            ),
        );

        if ($blFinalStep) {
            $ids = explode(',', $autoMatchingStats['productIds']);
            foreach ($ids as $id) {
                if($id !== '') {
                    MLDatabase::factory('selection')->set('pid', $id)->getList()->getQueryObject()->doDelete();
                }
            }
            $aMessage['message'] =  trim(sprintf(
                MLI18n::gi()->get('ML_AMAZON_TEXT_AUTOMATIC_MATCHING_SUMMARY'),
                (string)$autoMatchingStats['success'],
                (string)$autoMatchingStats['nosuccess'],
                (string)$autoMatchingStats['almost']
            ));
        }
        MLSetting::gi()->add('aAjax', $aMessage );

        $sAutoMatchStatistic = '<div id="ml-auto-matching-statistic"></div>';
        foreach ($autoMatchingStats as $key => $item) {
            if(!is_array($item)) {
                $sAutoMatchStatistic .= '<input type="hidden" value="' . $item . '"
                     name="' . MLHttp::gi()->parseFormFieldName('statistic[' . $key . ']') . '"/>';

            }
        }
        $sAutoMatchStatistic .= '</div>';
        MLSetting::gi()->add('aAjaxPlugin', array('dom' => array('#ml-auto-matching-statistic' => array('content' => $sAutoMatchStatistic, 'action' => 'html'))));

    }

    protected function AutoMatchProduct()
    {
        $statistic = MLRequest::gi()->data('statistic');
        $autoMatchingStats = array(
            'success' => isset($statistic['success'])?(int)$statistic['success']:0,
            'almost' => isset($statistic['almost'])?(int)$statistic['almost']:0,
            'nosuccess' => isset($statistic['nosuccess'])?(int)$statistic['nosuccess']:0,
            'productIds' => isset($statistic['productIds'])?$statistic['productIds']:'',
            'iCurrent' => 0,
            'iOffset' =>0,
            'aStatistic' =>array(),
            'categories' => array(),
            '_timer' => microtime(true)
        );

        /** @var ML_Amazon_Model_ProductList_Amazon_Prepare_Match_Auto $oList */
        $oList = MLProductList::gi('amazon_prepare_match_auto');
        $autoMatchingStats['iOffset'] = (int) $this->getRequest('offset');
        $autoMatchingStats['iCurrent'] = $autoMatchingStats['iOffset'];
        $oList->setFilters(array('iOffset' => $autoMatchingStats['iOffset']));
        $aData = $this->getRequest('amazonProperties');
        if (!array_key_exists('B2BSellTo', $aData)) {
            // set B2B configuration default, if set, otherwise database default will be used
            $value = MLDatabase::factory('preparedefaults')->getValue('b2bsellto');
            if ($value) {
                $aData['B2BSellTo'] = $value;
            }
        }
        $autoMatchingStats['aStatistic'] = $oList->getStatisticWithVariants();
        foreach ($oList->getListWithVariants() as $oProduct){
            $autoMatchingStats['iCurrent']++;
            $variantStatistic = array(
                'success' => true,
                'nosuccess' => false,
                'almost' => false,
            );

            /** @var ML_Amazon_Helper_Model_Table_Amazon_PrepareData $oHelper */
            $oHelper = MLHelper::gi('Model_Table_Amazon_PrepareData');

            $aProduct = $oHelper->getProductArrayById($oProduct);
            $searchResult = null;
            if ($aProduct['EAN']) {
                $searchResult = $oHelper->searchEanAndAsinOnAmazon('', $aProduct['EAN'], '');
            } else {
                $variantStatistic['success'] &= false;
                $variantStatistic['nosuccess'] |= true;

            }
            //In popup message of magnalister it is mentioned that it try to find EAN and not the title, no doing that can cause unwanted matching
            //if ($aProduct['Title'] && (count($searchResult) != 1)) {
            //    $searchResult = MLHelper::gi('Model_Table_Amazon_PrepareData')->searchEanAndAsinOnAmazon($aProduct['Title'], '', '');
            //}
            if(is_array($searchResult)) {
                $ASIN = '';
                foreach ($searchResult[0] as $item => $value) {
                    if ($item == 'ASIN') {
                        $ASIN = $value;
                    }
                }
                $aData = array_merge($aData,
                    array(
                        'aidentid' => $ASIN,
                    ));
                if (count($searchResult) != 1) {

                    $variantStatistic['success'] &= false;
                    if (count($searchResult) > 0) {
                        $variantStatistic['almost'] |= true;
                    }
                    $variantStatistic['nosuccess'] |= true;
                    MLMessage::gi()->addDebug('$aData 1:',$aData);
                    MLHelper::gi('Model_Table_Amazon_Prepare_Product')->auto($oProduct, $aData, false)->getTableModel()->save();
                } else {
                    MLMessage::gi()->addDebug('$aData 2:',$aData);
                    MLHelper::gi('Model_Table_Amazon_Prepare_Product')->auto($oProduct, $aData, true)->getTableModel()->save();
                }
            }
            $autoMatchingStats['productIds'] .= $oProduct->get('id').',';
            if($variantStatistic['success']) {
                $autoMatchingStats['success']++;
            }
            if($variantStatistic['nosuccess']) {
                $autoMatchingStats['nosuccess']++;
            }
            if($variantStatistic['almost']) {
                $autoMatchingStats['almost']++;
            }
            break;
        }

        return $autoMatchingStats;
    }

    public function getCurrentProduct(){
        $oTable = MLDatabase::factory('selection')->set('selectionname', 'match');
        $oSelectList = $oTable->getList();
        /* @var $oSelectList ML_Database_Model_List */
        $aResult = $oSelectList->getQueryObject()->init('aSelect')->select('parentid')->join(array('magnalister_products','p','pid = p.id'))->getResult();
        $sParent = null;
        foreach ($aResult as $aRow){//show shippingtime , condition and ... if it is single preparation
            if($sParent !== null && $sParent != $aRow['parentid']){
                return null;
            }
            $sParent = $aRow['parentid'];
        }
        $aList = $oTable->getList()->getList();
        // if not products are selected do not go to matching
        if (empty($aList)) {
            MLHttp::gi()->redirect($this->getParentUrl());
            return null;
        }
        $oProduct = MLProduct::factory()->set('id', current($aList)->get('pid'));

        /** @var ML_Amazon_Helper_Model_Service_Product $productHelper */
        $productHelper = MLHelper::gi('Model_Service_Product');
        $productHelper->setProduct($oProduct);

        return $productHelper
            ->addVariant($oProduct)
            ->getData();
    }

    protected function getPrepareProductHelper() {
        return MLHelper::gi('Model_Table_Amazon_Prepare_Product');
    }


}
