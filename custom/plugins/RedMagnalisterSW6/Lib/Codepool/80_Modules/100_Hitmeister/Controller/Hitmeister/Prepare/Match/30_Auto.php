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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_PrepareAbstract');

class ML_Hitmeister_Controller_Hitmeister_Prepare_Match_Auto extends ML_Form_Controller_Widget_Form_PrepareAbstract {

    protected $aParameters = array('controller');

    protected $iPrepareProductsLimit = 25;

    public function construct() {
        parent::construct();
        $this->oPrepareHelper->bIsSinglePrepare = $this->oSelectList->getCountTotal() === '1';
    }

    public function getRequestField($sName = null, $blOptional = false) {
        if (count($this->aRequestFields) == 0) {
            $this->aRequestFields = $this->getRequest($this->sFieldPrefix);
            $this->aRequestFields = is_array($this->aRequestFields) ? $this->aRequestFields : array();
        }

        return parent::getRequestField($sName, $blOptional);
    }

    protected function getSelectionNameValue() {
        return 'match';
    }

    protected function triggerBeforeFinalizePrepareAction() {
        $this->oPrepareList->set('preparetype', 'auto');
        $this->oPrepareList->set('verified', 'OK');

        return true;
    }

    /**
     * @return void
     */
    protected function callAjaxAutoMatching() {
        $stats = $this->insertAutoMatchedProduct();
        MLSetting::gi()->add('aAjax', array(
            'Data' => $stats,
            'message' => trim(sprintf(
                MLI18n::gi()->get('Hitmeister_Productlist_Match_Auto_Summary'),
                $stats['success'],
                $stats['nosuccess'],
                $stats['almost']
            )),
        ));
    }

    /**
     * @return array
     * @throws MLAbstract_Exception
     */
    protected function insertAutoMatchedProduct() {
        if (is_numeric(MLRequest::gi()->get('total'))) {
            $total = MLRequest::gi()->get('total');
        } else {
            $total = $this->oSelectList->getCountTotal();
        }
        $stats = array(
            'success' => (int)MLRequest::gi()->get('success') ?: 0,
            'almost' => (int)MLRequest::gi()->get('almost') ?: 0,
            'offset' => (int)MLRequest::gi()->get('offset') ?: 0,
            'total' => (int)$total,
            'nosuccess' => (int)MLRequest::gi()->get('nosuccess') ?: 0,
            '_timer' => microtime(true),
            'itemsPerCall' => $this->iPrepareProductsLimit,
        );

        // Since the list will be limited by the iPrepareProductsLimit property, we can just iterate over the complete list
        foreach ($this->oSelectList->getList() as $selectedProduct) {
            $product = $this->oPrepareHelper->getProductInfoById($selectedProduct->pID);

            $iMatchedArrayKey = null;
            if (!empty($product['Results'])) {
                foreach ($product['Results'] as $sKey => $searchResult) {
                    if ($searchResult['ean_match'] === true) {
                        $iMatchedArrayKey = $sKey;
                        break;
                    }
                }
            } else {
                $product['Results'] = array();
            }

            if (   $iMatchedArrayKey === null
                && count($product['Results']) != 1
            ) {
                if (count($product['Results']) > 0) {
                    $stats['almost']++;
                } else {
                    $stats['nosuccess']++;
                }
                MLDatabase::getDbInstance()->delete(TABLE_MAGNA_SELECTION, array(
                    'pID' => $product['Id'],
                    'mpID' => MLModule::gi()->getMarketPlaceId(),
                    'selectionname' => 'match',
                    'session_id' => MLShop::gi()->getSessionId()
                ));
                continue;
            } elseif ($iMatchedArrayKey === null) {
                $iMatchedArrayKey = 0;
            }
            $oModul = MLModule::gi();
            $matchedProduct = array(
                'mpID' => $oModul->getMarketPlaceId(),
                'products_id' => $product['Id'],
                'Title' => $product['Results'][$iMatchedArrayKey]['title'],
                'EAN' => reset($product['Results'][$iMatchedArrayKey]['eans']),
                'ItemCondition' => $oModul->getPrepareDefaultConfig('itemcondition'),
                'HandlingTime' => $oModul->getPrepareDefaultConfig('handlingtime'),
                'ItemCountry' => $oModul->getPrepareDefaultConfig('itemcountry'),
                'Comment' => '',
                'PrepareType' => 'auto',
                'PreparedTS' => date('Y-m-d H:i:s'),
                'Verified' => 'OK'
            );

            MLDatabase::getDbInstance()->insert(TABLE_MAGNA_HITMEISTER_PREPARE, $matchedProduct, true);

            MLDatabase::getDbInstance()->delete(TABLE_MAGNA_SELECTION, array(
                'pID' => $product['Id'],
                'mpID' => MLModule::gi()->getMarketPlaceId(),
                'selectionname' => 'match',
                'session_id' => MLShop::gi()->getSessionId()
            ));

            $stats['success']++;
        }

        return $stats;
    }

}
