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

class ML_Otto_Model_Service_ImportOrders extends ML_Modul_Model_Service_ImportOrders_Abstract {
    protected $sBlackListedMailPrefix = 'blacklisted-';

    public function canDoOrder(ML_Shop_Model_Order_Abstract $oOrder,&$aOrder) {
        if ($oOrder->get('orders_id') === null) {//only new orders
            return 'Create order';
        } else {
            //throw new Exception('Order aleready exists');
            throw MLException::factory('Model_Service_ImportOrders_OrderExist')->setShopOrder($oOrder);
        }
    }

    protected function normalizeOrder($aOrder) {
        // check for "blacklisted-" prefix in email address
        $sBlacklistEmails = MLModule::gi()->getConfig('orderimport.blacklisting');
        if (isset($sBlacklistEmails) && $sBlacklistEmails === '1') { // in case of emails should  be blacklisted, add prefix (0 = do not blacklist, 1 = blacklist mail)
            if (array_key_exists('AddressSets', $aOrder)) {
                foreach ($aOrder['AddressSets'] as &$addressSet) {
                    if (!empty($addressSet['EMail'])) {
                        $addressSet['EMail'] = $this->sBlackListedMailPrefix.$addressSet['EMail'];
                    }
                }
            }
        }

        return parent::normalizeOrder($aOrder);
    }

    /**
     * E-Mail to Buyer (Overwrite function because of blacklisted email feature)
     *
     * @param array $aOrder
     * @param null $oOrder
     * @return bool|mixed
     */
    protected function sendPromotionMail($aOrder, $oOrder = null) {
        // check for "blacklisted-" prefix in email address
        $sBlacklistEmails = MLModule::gi()->getConfig('orderimport.blacklisting');
        if (isset($sBlacklistEmails) && $sBlacklistEmails === '1') { // in case of emails are blacklisted, remove prefix (0 = not blacklist, 1 = blacklisted mail)
            if (!empty($aOrder['AddressSets']['Main']['EMail'])
                && (substr($aOrder['AddressSets']['Main']['EMail'], 0, strlen($this->sBlackListedMailPrefix)) == $this->sBlackListedMailPrefix)
            ) {
                $aOrder['AddressSets']['Main']['EMail'] = substr($aOrder['AddressSets']['Main']['EMail'], strlen($this->sBlackListedMailPrefix));
            }
        }
        return parent::sendPromotionMail($aOrder, $oOrder);
    }

}
