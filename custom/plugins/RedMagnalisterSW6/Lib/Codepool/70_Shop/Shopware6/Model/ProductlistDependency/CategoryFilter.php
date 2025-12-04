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

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Defaults;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;

MLFilesystem::gi()->loadClass('Shop_Model_ProductListDependency_CategoryFilter_Abstract');

class ML_Shopware6_Model_ProductListDependency_CategoryFilter extends ML_Shop_Model_ProductListDependency_CategoryFilter_Abstract {

    /**
     * key=>value for filtering (eg. validation and form-select)
     * @var array|null
     */
    protected $aFilterValues = null;
    
    /**
     * all categories
     * @var array|null
     */
    protected $aCategories = null;

    /**
     * @param ML_Database_Model_Query_Select $mQuery
     * @return void
     * @throws Exception
     */
    public function manipulateQuery($mQuery) { 
        $sFilterValue = (string)$this->getFilterValue();
        if (
            !empty($sFilterValue)
            && $sFilterValue !== 1 //root-category
        ) {
            $mQuery
                ->where(' HEX(cp.`category_id`)=\''.(string)$sFilterValue.'\'')
                ->join(array('product_category_tree','cp' , 'p.`id` = cp.`product_id`'), ML_Database_Model_Query_Select::JOIN_TYPE_INNER);
        }
    }


    /**
     * key=>value for categories
     * @return array
     */
    protected function getFilterValues() {
        if ($this->aFilterValues === null) {
            $aCats = array(
                '' =>
                    array(
                        'value' => '',
                        'label' => 'Filter ('.MLI18n::gi()->Shopware_Productlist_Filter_sCategory.')',
                    ),
            );

            //cache category tree
            $cacheString = __CLASS__.__FUNCTION__.'ShopwareCategories';
            if (!MLCache::gi()->exists($cacheString)) {
                $categories = $this->getShopwareCategories();
                MLCache::gi()->set(__CLASS__.__FUNCTION__.'ShopwareCategories', $categories, 60*15);
            } else {
                $categories = MLCache::gi()->get(__CLASS__.__FUNCTION__.'ShopwareCategories');
            }

            foreach ($categories as $aCat) {
                $aCats[$aCat['value']] = array(
                    'value' => $aCat['value'],
                    'label' => $aCat['label'],
                );
            }
            $this->aFilterValues = $aCats;
        }
        return $this->aFilterValues;
    }

    /**
     * gets all categories
     * @param array|null $aCats nested cats
     * @return array
     */
    protected function getShopwareCategories($iParentId = null) {
        $CategoryCriteria = new Criteria();
        $aCats = MagnalisterController::getShopwareMyContainer()->get('category.repository')
            ->search($CategoryCriteria
                ->addFilter(new EqualsFilter('parentId', $iParentId)), Context::createDefaultContext())
            ->getEntities();

        foreach ($aCats as $aCat) {
            $this->aCategories[$aCat->getId()] = array(
                'value' => $aCat->getId(),
                'label' => str_repeat('&nbsp;', substr_count($aCat->getPath(), '|') * 2).$aCat->getName(),
            );
            if (
                empty($this->aCatsFilter)
                || $aCat['parentid'] == $this->getFilterValue()
                || in_array($aCat->getId(), $this->aCatsFilter)
            ) {
                $this->getShopwareCategories($aCat->getId());
            } else {
                $CategoryCountCriteria = new Criteria();
                $CategoryCount = MagnalisterController::getShopwareMyContainer()->get('category.repository')
                    ->search($CategoryCountCriteria
                        ->addFilter(new EqualsFilter('parentId', $aCat->getId())), Context::createDefaultContext())
                    ->getEntities();
                $this->aCategories[$aCat->getId()]['label'] .= (count($CategoryCount) > 0) ? '  ' : '';
            }
        }

        if ($iParentId === null) {
            return $this->aCategories;
        } else {
            return array();
        }
    }
    
    /**
     * some wrong subcategory, that is not deleted correctly by Prestashop made problem for default category filter
     * so we always set Root category as default
     * @return string
     */
    public function getFilterValue() {
        $sValue = parent::getFilterValue();
        if ($sValue === null) {
            if (array_key_exists('1', $this->getFilterValues())) {
                return '1';
            }
        } else {
            return $sValue;
        }
    }

}
