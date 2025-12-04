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

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

abstract class ML_Shopware6_Model_ProductList_Abstract extends ML_Productlist_Model_ProductList_ShopAbstract {
    protected $sPrefix = 'ml_';

    /**
     * filter
     * @var ML_Shopware6_Helper_Model_ProductList_Filter $oFilter
     */
    protected $oFilter = null;

    /**
     * list/result
     * @var ML_Shopware6_Helper_Model_ProductList_List $oList
     */
    protected $oList = null;
    protected $sOrder = '';

    /**
     * @var ML_Database_Model_Query_Select $oSelectQuery
     */
    protected $oSelectQuery = null;

    public function __construct() {
        $oSelectquery = MLHelper::gi("model_product")->getProductSelectQuery();
        try {
            $aConfig = MLModule::gi()->getConfig();
            $sCurrentController = MLRequest::gi()->get('controller');
            foreach (MLModule::gi()->getPriceGroupKeys() as $sGroupKey) {
                $oCustomerGroupCriteria = new Criteria();
                $oCustomerGroup = MagnalisterController::getShopwareMyContainer()->get('customer_group.repository')->search($oCustomerGroupCriteria->addFilter(new EqualsFilter('id', $aConfig[$sGroupKey])), Context::createDefaultContext())->getEntities();
                if (!is_object($oCustomerGroup)) {
                    MLHttp::gi()->redirect(MLHttp::gi()->getUrl(array(
                        'controller' => substr($sCurrentController, 0, strpos($sCurrentController, '_')).'_config_price'
                    )));
                }
            }
            $iLangId = MLFormHelper::getShopInstance()->getLanguage();
            $Language = MLShopware6Alias::getRepository('language.repository')->search((new Criteria())->addFilter(new EqualsFilter('id', $iLangId)), Context::createDefaultContext())->first();
            if ($Language === null) {
                MLHttp::gi()->redirect(MLHttp::gi()->getUrl(array(
                    'controller' => substr($sCurrentController, 0, strpos($sCurrentController, '_')) . '_config_prepare'
                )));
            }
        } catch (Exception $oExc) {

        }

        $this->oSelectQuery = $oSelectquery;
        $this->oFilter = MLHelper::gi('model_productlist_filter')
            ->clear()
            ->setCollection($oSelectquery)
            ->setPrefix($this->sPrefix);
        $this->initList();
        $this->oList
            ->clear()
            ->setCollection($oSelectquery);
    }

    protected function initList() {
        $this->oList = MLHelper::gi('model_productlist_list');
    }

    /**
     * Set Filters of products list (like manufacturer, category, page, offset, sort order)
     *
     * @param $aFilter
     * @return $this|ML_Shopware6_Model_ProductList_Abstract
     */
    public function setFilters($aFilter) {
        if (is_array($aFilter)) {
            $this->oFilter
                ->setFilter($aFilter)
                ->setPage(isset($aFilter['meta']['page']) ? $aFilter['meta']['page'] : 0)
                ->setOffset(isset($aFilter['meta']['offset']) ? $aFilter['meta']['offset'] : 0)
                ->setOrder(isset($aFilter['meta']['order']) ? $aFilter['meta']['order'] : 'p.`created_at`_DESC');
        } else {
            $this->oFilter->setOrder('p.`created_at`_DESC');
        }
        $this->sOrder = isset($aFilter['meta']['order']) ? $aFilter['meta']['order'] : '';
        $this->executeList();
        $this->executeFilter();
        return $this;
    }

    public function getFilters() {
        return $this->oFilter->getOutput();
    }

    public function getStatistic() {
        return $this->oFilter->getStatistic();
    }

    public function getMasterIds($blPage = false) {
        $aMainIds = array();
        if ($blPage) {
            $aMainIds = $this->oList->getLoadedList();
        } else {
            $aIdArrays = $this->oSelectQuery->getAll();
            foreach ($aIdArrays as $aItem) {
                $aMainIds[] = current($aItem);
            }
        }
        $aMainIds = array_map('strtolower', $aMainIds);
        $aIds = array();
        if (!empty($aMainIds)) {
            foreach (MLShopware6Alias::getRepository('product')
                         ->search(new Criteria($aMainIds), Context::createDefaultContext())
                         ->getEntities() as $oProduct) {
                $aIds[] = MLProduct::factory()->loadByShopProduct($oProduct)->get('id');
            }
        }
        return $aIds;
    }

    abstract protected function executeFilter();

    public function getHead() {
        return $this->oList->getHeader();
    }

    /**
     *
     * @var array
     */
    protected $aListOfProduct = null;

    public function getList() {
        return new ArrayIterator($this->getArrayList());
    }

    public function getArrayList() {
        if ($this->aListOfProduct === null) {
            $this->aListOfProduct = $this->oList->getList();
        }
        return $this->aListOfProduct;
    }

    public function additionalRows(ML_Shop_Model_Product_Abstract $oProduct) {
        return array();
    }

    public function getMixedData(ML_Shop_Model_Product_Abstract $oProduct, $sKey) {
        return $this->oList->getMixedData($oProduct, $sKey);
    }

    public function variantInList(ML_Shop_Model_Product_Abstract $oProduct) {
        return $this->oFilter->variantInList($oProduct);
    }

    public function setLimit($iFrom, $iCount) {
        $this->oSelectQuery->limit($iFrom, $iCount);
        return $this;
    }

}
