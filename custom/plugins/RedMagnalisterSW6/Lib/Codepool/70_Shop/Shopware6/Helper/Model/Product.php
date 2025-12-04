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

class ML_Shopware6_Helper_Model_Product {

    public function getProductSelectQuery() {
        return MLDatabase::factorySelectClass()
            ->select('HEX(p.`id`) AS `id`')
            ->from(MagnalisterController::getShopwareMyContainer()->get('product.repository')->getDefinition()->getEntityName(), 'p')
            ->where("p.`parent_id` IS NULL AND p.`version_id` = UNHEX('".Context::createDefaultContext()->getVersionId()."')");
    }

    /**
     * if product has any variation it will return sum of quantity of all variation, otherwise it will return quantity of the product
     * @param object $oArticle
     * @return int $iStock
     */
    public function getTotalCount($oArticle) {
        $iStock = 0;

        try {
            $iStock = (int)$oArticle->getAvailableStock();
        } catch (Exception $oExc) {
        }

        return $iStock;
    }

    public function getDescription($oCorrespondingProductEntity, $oMasterProductEntity, $oShopwareContext) {
        if ($oCorrespondingProductEntity->getDescription() !== NULL) {
            $mDescription = $oCorrespondingProductEntity->getDescription();
        } else {
            $criteria1 = MLHelper::gi("model_product")->initiateCriteriaObject();
            $oProduct = MLShopware6Alias::getRepository('product')
                ->search($criteria1->addFilter(new EqualsFilter('product.id', $oMasterProductEntity->getId())), $oShopwareContext)->first();
            /** @var $oProduct ProductEntity */
            $mDescription = $oProduct->getDescription();
        }
        return $mDescription;
    }

    public function initiateCriteriaObjectWithMedia() {
        $oCriteria = new Criteria();
        $oCriteria->addAssociation('media');
        return $oCriteria;
    }

}
