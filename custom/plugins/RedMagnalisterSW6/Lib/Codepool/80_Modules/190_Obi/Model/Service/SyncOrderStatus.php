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

class ML_Obi_Model_Service_SyncOrderStatus extends ML_Modul_Model_Service_SyncOrderStatus_Abstract {

    protected $sOrderIdentifier = 'ObiOrderId';

    protected $cancelApiAction = 'CancelOrder';


    protected function manipulateRequest($aRequest) {
        $aConfig = MLModule::gi()->getConfig();
        if ($aRequest['ACTION'] == 'CancelOrder') {
            foreach ($aRequest['DATA'] as &$aOrder) {
                $aOrder['CancellationReason'] = $aConfig['orderstatus.cancelreason'];
            }
        }
        return $aRequest;
    }

    protected function otherActions($oOrder) {
        $sShopStatus = $oOrder->getShopOrderStatus();
        if ($this->isReturned($sShopStatus)) {
            $aData = $oOrder->get('data');
            $sOrderId = $aData[$this->sOrderIdentifier];
            $aReturnModels[$sOrderId] = $oOrder;
            $sCarrier = $this->getCarrier($oOrder);
            $aReturnOrderRequest[$sOrderId] = array(
                $this->sOrderIdentifier => $sOrderId,
                'ShippingDate' => $this->getShippingDate($oOrder),
                'Carrier' => $sCarrier,
                'TrackingCode' =>$oOrder->getShippingTrackingCode(),
                'ShopOrderId' =>$oOrder->getShopOrderId(),

            );

            $this->submitRequestAndProcessResult('ReturnOrder', $aReturnOrderRequest, $aReturnModels);
        }
    }

    protected function isReturned($sShopStatus) {
        $oModule = MLModule::gi();
        $sReturnState = $oModule->getConfig('orderstatus.return');

        return $sShopStatus == $sReturnState;
    }

    protected function getCarrier($oOrder) {
        $sCarrier = $this->getCarrierValue('orderstatus.sendcarrier', $oOrder);

        return $sCarrier;
    }

    /**
     * Returns carrier value based on configuration settings and shipping type in shop system
     *
     * @param $carrierType
     * @param ML_Shop_Model_Order_Abstract $oOrder
     * @param string $marketplaceCarrierType
     * @return string
     */
    private function getCarrierValue($carrierType, $oOrder = null) {
        $oModule = MLModule::gi();
        $selectValue = $oModule->getConfig($carrierType. '.select');
        $carrierValue = null;
        switch ($selectValue) {
            case 'sendCarrierMatching':
                // checks if carrier is set as matching
                $sCarrierId = $oOrder->getShopOrderCarrierOrShippingMethodId();
                $carrierMatches = $oModule->getConfig($carrierType . '.matching');
                foreach ($carrierMatches as $carrierMatch) {
                    if ($carrierMatch['shopCarrier'] == $sCarrierId) {
                        $carrierValue = $carrierMatch['marketplaceCarrier'];
                        break;
                    }
                }
                break;
            case 'orderFreeTextField':
                $carrierValue = $oOrder->getAdditionalOrderField('returnCarrier');
                break;
            default:
            {
                //order attribute freetext field(only in Shopware 5)
                if (strpos($selectValue, 'a_') === 0) {
                    $carrierValue = $oOrder->getAttributeValue($selectValue);
                    break;
                }

                //checks if carrier is set as marketplace value
                $carrierValue = $this->getMarketplaceCarrier($selectValue);
                break;
            }
        }

        if ($carrierValue === null) {
            MLLog::gi()->add('SyncOrderStatus_'.MLModule::gi()->getMarketPlaceId().'_WrongConfiguration', array(
                'Problem' => $carrierType . ' is not configured!',
            ));
        }

        return $carrierValue;
    }

    /**
     * Returns marketplace carrier value based on predefined carrier list provided by OBI
     *
     * @param $marketplaceCarrierType
     * @param $value
     * @return string
     */
    private function getMarketplaceCarrier($value, $filter = 'Carriers') {
        $carrierValue = null;
        $apiMarketplaceCarriers = MLModule::gi()->getObiOptionsConfiguration($filter);

        foreach ($apiMarketplaceCarriers[$filter] as $key => $marketplaceCarrier) {
            if ($value === $key) {
                $carrierValue = $key;
                break;
            }
        }
        return $carrierValue;
    }
}
