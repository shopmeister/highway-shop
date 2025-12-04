<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                            m a g n a l i s t e r
 *                                        boost your Online-Shop
 *
 *   -----------------------------------------------------------------------------
 *   @author magnalister
 *   @copyright 2010-2022 RedGecko GmbH -- http://www.redgecko.de
 *   @license Released under the MIT License (Expat)
 *   -----------------------------------------------------------------------------
 */

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;

class MLShopware6Alias {

    /**
     * get Shopware 6 product helper class
     * @return ML_Shopware6_Helper_Model_Product|Object
     */
    public static function getProductHelper() {
        return MLHelper::gi('model_product');
    }

    /**
     * get Shopware 6 currency model class
     * @return ML_Shopware6_Model_Currency|Object
     */
    public static function getCurrencyModel() {
        return MLCurrency::gi();
    }

    /**
     * get Shopware 6 price helper class
     * @return ML_Shopware6_Model_Price|Object
     */
    public static function getPriceModel() {
        return MLPrice::factory();
    }

    /**
     * get Shopware 6 price helper class
     * @return ML_Shopware6_Helper_Model_Price|Object
     */
    public static function getPriceHelper(){
        return MLHelper::gi('model_price');
    }
    /**
     * Get a new instace of magnalister Shopware 6 shop order helper class
     * @return ML_Shopware6_Helper_Model_ShopOrder|Object
     */
    public static function getShopOrderHelper(){
        return MLHelper::factory('model_shoporder');
    }


    /**
     * get Shopware 6 shop order helper class
     * @return ML_Shopware6_Model_Http|Object
     */
    public static function getHttpModel(){
        return MLHttp::gi();
    }

    /**
     * @return ML_Shopware6_Model_Product|ML_Shop_Model_Product_Abstract
     */
    public static function getProductModel(){
        return MLProduct::factory();
    }

    /**
     * @param $sRepositoryName string if ".repository" is not appended it will be appended automatically
     * @return EntityRepositoryInterface|SalesChannelRepositoryInterface|Shopware\Core\Framework\DataAbstractionLayer\EntityRepository
     */
    public static function getRepository(string $sRepositoryName) {
        if(strpos($sRepositoryName, '.repository') === false){
            $sRepositoryName .= '.repository';
        }
        return MagnalisterController::getShopwareMyContainer()->get($sRepositoryName);
    }

    /**
     * @return ML_Shopware6_Model_Order
     */
    public static function getOrderModel(): \ML_Shopware6_Model_Order {
        return MLOrder::factory();
    }

    /**
     * Description: Create context in specific language
     * @param null $languageId
     * @param null $currencyId
     * @return context object
     */
    public static function getContextByLanguageId($languageId = null, $currencyId = null) {
        if ($languageId == null) {
            $languageId = Defaults::LANGUAGE_SYSTEM;
        }
        if ($currencyId == null) {
            $currencyId = Defaults::CURRENCY;
        }
        $context = new Context(
            new SystemSource(), [], $currencyId, [$languageId], Defaults::LIVE_VERSION
        );
        return $context;
    }

    /**
     * Create context by language, currency or rules
     * @param null $languageId
     * @param null $currencyId
     * @param array $rules
     * @return context object
     */
    public static function getContext($languageId = null, $currencyId = null, array $rules = []) {
        if ($languageId == null) {
            $languageId = Defaults::LANGUAGE_SYSTEM;
        }
        if ($currencyId == null) {
            $currencyId = Defaults::CURRENCY;
        }
        $context = new Context(
            new SystemSource(), $rules, $currencyId, [$languageId], Defaults::LIVE_VERSION
        );
        return $context;
    }
}