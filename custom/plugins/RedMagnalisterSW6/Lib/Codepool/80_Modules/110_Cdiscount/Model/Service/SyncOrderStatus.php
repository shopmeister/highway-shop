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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class ML_Cdiscount_Model_Service_SyncOrderStatus extends ML_Modul_Model_Service_SyncOrderStatus_Abstract {
    protected $cancelApiAction = 'CancelOrder';

    protected function postProcessError($aError, &$aModels) {
        $sMarketplaceOrderId = null;
        if (isset($aError['DETAILS']) && isset($aError['DETAILS'][$this->sOrderIdentifier])) {
            $sMarketplaceOrderId = $aError['DETAILS'][$this->sOrderIdentifier];
        }
        if (empty($sMarketplaceOrderId)) {
            return;
        }

        if (   isset($aError['DETAILS']['ErrorType'])
            && in_array ($aError['DETAILS']['ErrorType'], array(
                'OrderStateIncoherent', // OrderStateIncoherent: Shipped state is not possible to set at this stage
                'UnexpectedException', // could be many errors of cdiscount
            ))
        ) {
            $this->saveOrderData($aModels[$sMarketplaceOrderId]);
            unset($aModels[$sMarketplaceOrderId]);
        }
    }

    public function getOrderstatusCarrier() {
        $sConfigCarrier = MLModule::gi()->getConfig('orderstatus.carrier.select');
        return $sConfigCarrier;
    }

    protected function findTheConfigKey($aConfigMatching, $sSearchedKey, $sKey) {
        foreach ($aConfigMatching as $iIndex => $aMatching) {
            if ($aMatching[$sKey] === (string)$sSearchedKey) {
                return $iIndex;
            }
        }
        return null;
    }

    /**
     * Manipulates the request data.
     *
     * * Adds cancellation reasons to cancelled shipments.
     *
     * @param array $aRequest
     * @return array
     */
    protected function manipulateRequest($aRequest)
    {
        $aConfig = MLModule::gi()->getConfig();
        if ($aRequest['ACTION'] == $this->cancelApiAction) {
            foreach ($aRequest['DATA'] as &$aOrder) {
                $aOrder['CancellationReason'] = $aConfig['orderstatus.cancellation_reason'];
            }
        }

        return $aRequest;
    }
}
