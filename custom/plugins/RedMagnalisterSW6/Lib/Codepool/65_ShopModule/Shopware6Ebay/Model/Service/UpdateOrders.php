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

MLFilesystem::gi()->loadClass('Ebay_Model_Service_UpdateOrders');

class ML_Shopware6Ebay_Model_Service_UpdateOrders extends ML_Ebay_Model_Service_UpdateOrders {

    public function canDoOrder(ML_Shop_Model_Order_Abstract $oOrder, &$aOrder) {
        if ($oOrder->get('orders_id') !== null) { // only existing orders
            /* @var $oOrder ML_Shopware6_Model_Order */
            $aMessage = array();
            $aUpdatablePaymentStatus = MLModule::gi()->getConfig('updateable.paymentstatus');
            $aIsActive = MLModule::gi()->getConfig('update.paymentstatus');
            if (
                    !isset($aIsActive) //if someone didn't set this configuration, it doesn't update payment status
                    ||
                    ((bool)$aIsActive && is_array($aUpdatablePaymentStatus) && in_array($oOrder->getShopPaymentStatus(), MLModule::gi()->getConfig('updateable.paymentstatus'), true))
            ) {
                $oOrder->setUpdatablePaymentStatus(true);
                $aMessage[] = 'Update payment status';
            } else {
                $oOrder->setUpdatablePaymentStatus(false);
            }
            $aIsActive = MLModule::gi()->getConfig('update.orderstatus');
            $aUpdateableStatusses = MLModule::gi()->getConfig('updateable.orderstatus');
            if ((bool)$aIsActive && is_array($aUpdateableStatusses) && in_array($oOrder->getShopOrderStatus(), $aUpdateableStatusses, true)) {
                $oOrder->setUpdatableOrderStatus(true);
                $aMessage[] = 'Update order status';
            } else {
                $oOrder->setUpdatableOrderStatus(false);
            }

            return empty($aMessage) ? 'cannot update order and payment status' : implode(', ', $aMessage);
        } else {
            throw new Exception("Order doesn't exist");
        }
    }

}
