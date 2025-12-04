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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;

class ML_Shopware6_Model_Shop extends ML_Shop_Model_Shop_Abstract {

    protected static $sSession;
    public function getShopSystemName() {
        return 'shopware6';
    }

    public function getDbConnection() {
        $sMlConnection = MagnalisterController::getShopwareConnection()->getParams();
        $sMlConnection['database'] = $sMlConnection['dbname'];
        return $sMlConnection;
    }

    public function initializeDatabase () {
        MLDatabase::getDbInstance()->setCharset(MagnalisterController::getShopwareConnection()->getParams()['charset']);
        return $this;
    }

    public function getOrderSatatistic($sDateBack) {
        $result = MLDatabase::getDbInstance()->fetchArray("
           SELECT * FROM (
                SELECT so.`order_date_time`, mo.`platform` as `platform`, HEX(so.`id`) AS `id`
                  FROM `order` so
            INNER JOIN `magnalister_orders` mo ON HEX(so.`id`) = mo.`current_orders_id`
                 WHERE (so.`order_date_time` BETWEEN '$sDateBack' AND NOW()) AND so.`version_id` =  UNHEX('" . Context::createDefaultContext()->getVersionId() . "')
                 
                 UNION all
                 
                SELECT so.`order_date_time`, null as`platform`, HEX(so.`id`) AS `id`
                  FROM `order` so
                 WHERE (so.`order_date_time` BETWEEN '$sDateBack' AND NOW())  AND so.`version_id` =  UNHEX('" . Context::createDefaultContext()->getVersionId() . "')
            ) AS T
            Group by T.id
        ");

        return $result;
    }

    public function getSessionId() {
        if (self::$sSession === null) {
            if (MagnalisterController::getShopwareUserId() !== null) {
                self::$sSession = md5(session_id());
            } else {
                self::$sSession = 'shopware6sessionid____6';
            }
        }
        return self::$sSession;
    }



    public function getProductsWithWrongSku() {
        return array();
    }

    /**
     * will be triggered after plugin update for shop-spec. stuff
     * eg. clean shop-cache
     * @param bool $blExternal if true external files (outside of plugin-folder) was updated
     * @return $this
     */
    public function triggerAfterUpdate($blExternal) {
        return $this;
    }

    public function getDBCollationTableInfo() {
        return array(
            'table'=> MagnalisterController::getShopwareMyContainer()->get('product.repository')->getDefinition()->getEntityName(),
            'field'=> 'product_number',
        );
    }

    /**
     * Returns the Shopware 6 Plugin Version based on the composer.json file of shopspecific
     *
     * @return string
     */
    public function getPluginVersion(){
        $path = MagnalisterController::getShopwareMyContainer()->get('kernel')->locateResource('@RedMagnalisterSW6');
        $composerFile = realpath($path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'composer.json');

        if (file_exists($composerFile)) {
            $jsonString = file_get_contents($composerFile);
            $jsonObject = json_decode($jsonString);
            return str_replace('v', '', $jsonObject->version);
        }

        return parent::getPluginVersion();
    }

    /**
     * @return ML_Shopware6_Helper_Model_Cache_Filesystem|Object
     */
    public function getCacheObject() {
        return MLHelper::gi('model_cache_filesystem');
    }
    public function checkShopConfiguredValue($sCustomerGroupConfigurationKey, $sControllerPostfix) {
        $result = true;

        try {
            if (MLRequest::gi()->data('controller') !== null) {
                if ($sCustomerGroupConfigurationKey == 'orderimport.paymentmethod') {
                    //Check the option of payment method in configuration Order Import-> payment method field is configured in configured sales channel
                    $result = $this->paymentMethodIsConfiguredInSalesChannel($result);
                } elseif ($sCustomerGroupConfigurationKey == 'orderimport.shippingmethod') {
                    //Check the option of Shipping method in configuration Order Import-> Shipping method field is configured in configured sales channel
                    $result = $this->shippingMethodIsConfiguredInSalesChannel($result);
                } elseif ('lang' == $sCustomerGroupConfigurationKey) {
                    $aConfig = MLModule::gi()->getConfig();
                    $result = isset($aConfig['lang']) && $aConfig['lang'] != NULL && Uuid::isValid($aConfig['lang']);
                } elseif ('langs' == $sCustomerGroupConfigurationKey) {
                    $aConfig = MLModule::gi()->getConfig();
                    if (isset($aConfig['langs']) && is_array($aConfig['langs'])) {
                        foreach ($aConfig['langs'] as $languageId) {
                            if (!Uuid::isValid($languageId)) {
                                $result = false;

                                break;
                            }
                        }
                    }
                }
            }
        } catch (InvalidUuidException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $SalesChannelID
     * @return mixed
     */
    protected function getSalesChannelMagnalisterConfig($SalesChannelID) {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $SalesChannelID));
        $criteria->addAssociations(['shippingMethods']);
        $oSalesChannelMagnalisterConfig = MagnalisterController::getShopwareMyContainer()->get('sales_channel.repository')->search($criteria, Context::createDefaultContext())->first();
        return $oSalesChannelMagnalisterConfig;
    }

    static $paymentConfigCache = [];
    /**
     * @param bool $result
     * @return bool
     */
    protected function paymentMethodIsConfiguredInSalesChannel($result) {
        if (!isset(self::$paymentConfigCache[MLModule::gi()->getMarketPlaceId()])) {
            $aSalesChannelConfigPaymentMethod = array();
            if (MLModule::gi()->getConfig('orderimport.shop') != NULL) {
                $oSalesChannelMagnalisterConfig = $this->getSalesChannelMagnalisterConfig(MLModule::gi()->getConfig('orderimport.shop'));
                if(is_object($oSalesChannelMagnalisterConfig)){
                    $aSalesChannelConfigPaymentMethod = !empty($oSalesChannelMagnalisterConfig->getPaymentMethodIds()) ? $oSalesChannelMagnalisterConfig->getPaymentMethodIds() : array();
                }
            }
            $PaymentMethodConfiguredId = MLModule::gi()->getConfig('orderimport.paymentmethod');
            if (ctype_xdigit($PaymentMethodConfiguredId)) {
                $oPayments = MagnalisterController::getShopwareMyContainer()->get('payment_method.repository')->search(new Criteria(['id' => $PaymentMethodConfiguredId]), Context::createDefaultContext())->getEntities();
                foreach ($oPayments as $aPayment) {
                    if (!in_array($aPayment->getId(), $aSalesChannelConfigPaymentMethod)) {
                        $result = false;
                    }
                }
            }
//            elseif (
//                $PaymentMethodConfiguredId !== MLModule::gi()->getMarketPlaceName(false)
//                && $PaymentMethodConfiguredId !== 'matching'//in ebay by atomatic allocation
//            ) { //it is a old behavior that should throw any error somebody used that
//                $result = false;
//            }
            self::$paymentConfigCache[MLModule::gi()->getMarketPlaceId()] = $result;
        }
        return self::$paymentConfigCache[MLModule::gi()->getMarketPlaceId()];
    }

    static $shippingConfigCache = [];
    /**
     * @param bool $result
     * @return bool
     */
    protected function shippingMethodIsConfiguredInSalesChannel($result) {
        if (!isset(self::$shippingConfigCache[MLModule::gi()->getMarketPlaceId()])) {
            $aSalesChannelConfigShippingMethod = array();
            if (MLModule::gi()->getConfig('orderimport.shop') != NULL) {
                $oSalesChannelMagnalisterConfig = $this->getSalesChannelMagnalisterConfig(MLModule::gi()->getConfig('orderimport.shop'));
                if (is_object($oSalesChannelMagnalisterConfig)) {
                    foreach ($oSalesChannelMagnalisterConfig->getShippingMethods() as $value) {
                        $aSalesChannelConfigShippingMethod[] = $value->getId();
                    }
                }
            }
            $ShippingMethodConfiguredId = MLModule::gi()->getConfig('orderimport.shippingmethod');
            if (ctype_xdigit($ShippingMethodConfiguredId)) {
                $oShipping = MagnalisterController::getShopwareMyContainer()->get('shipping_method.repository')->search(new Criteria(['id' => $ShippingMethodConfiguredId]), Context::createDefaultContext())->getEntities();
                foreach ($oShipping as $aShipping) {
                    if (!in_array($aShipping->getId(), $aSalesChannelConfigShippingMethod)) {
                        $result = false;
                    }
                }
            }
//            elseif (
//                $ShippingMethodConfiguredId !== MLModule::gi()->getMarketPlaceName(false)
//                && $ShippingMethodConfiguredId !== 'matching'//in ebay by atomatic allocation
//            ) {
//                MLMessage::gi()->addDebug(__LINE__.':'.microtime(true), array($ShippingMethodConfiguredId, MLModule::gi()->getMarketPlaceName(false)));
//                $result = false;
//            }
            self::$shippingConfigCache[MLModule::gi()->getMarketPlaceId()] = $result;
        }
        return self::$shippingConfigCache[MLModule::gi()->getMarketPlaceId()];
    }
    public function getShopVersion() {
        return MLSHOPWAREVERSION;
    }

    public function getTimeZoneOnlyForShow() {
        /**
         * @var $oUser \Shopware\Core\System\User\UserEntity
         */
        $oUser = MagnalisterController::getShopwareMyContainer()->get('user.repository')
            ->search((new Criteria())->addFilter(new EqualsFilter('id', MagnalisterController::getShopwareUserId())), Context::createDefaultContext())->getEntities()->last();
        if ($oUser !== null) {
            return $oUser->getTimeZone();
        } else {
            return null;
        }
    }
}
