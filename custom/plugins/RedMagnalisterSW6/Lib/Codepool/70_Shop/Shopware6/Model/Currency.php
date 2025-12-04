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

use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;

class ML_Shopware6_Model_Currency extends ML_Shop_Model_Currency_Abstract {

    static protected $aListOfCurrencies = null;

    public function getList() {
        if (self::$aListOfCurrencies === null) {
            //echo print_m(MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search(new Criteria(),Context::createDefaultContext())->getEntities());
            $aCurrencyList = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search(new Criteria(), Context::createDefaultContext())->getEntities();
            foreach ($aCurrencyList as $aCurrency) {
                self::$aListOfCurrencies[$aCurrency->getisoCode()] = array(
                    'title' => $aCurrency->getisoCode(),
                    'symbol_left' => '',
                    'symbol_right' => $aCurrency->getsymbol(),
                    'decimal_point' => '.',
                    'thousands_point' => '',
                    'decimal_places' => 2,
                    'value' => $aCurrency->getfactor(),
                );
            }
        }
        return self::$aListOfCurrencies;
    }

    public function getDefaultIso() {
        $oCurrency = $this->getDefaultCurrency();
        if ($oCurrency == null) {
            return 'EUR';
        } else {
            return $oCurrency->getIsoCode();
        }

    }

    /**
     * @return CurrencyEntity|null
     */
    public function getDefaultCurrency() {
        $oSearchCriteria = new Criteria();
        $oSearchCriteria->addFilter(new EqualsFilter('factor', '1'));
        return MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($oSearchCriteria, Context::createDefaultContext())->getEntities()->last();
    }

    /**
     * @return boolean
     */
    public function isDefaultCurrency($sCurrencyId) {
        $oSearchCriteria = new Criteria([$sCurrencyId]);
        /** @var CurrencyEntity $oCurrency */
        $oCurrency = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($oSearchCriteria, Context::createDefaultContext())->getEntities()->first();

        return $oCurrency !== null && $oCurrency->getFactor() === 1.00;
    }

    public function updateCurrencyRate($sCurrency) {
        $sDefaultCurrency = $this->getDefaultIso();
        if ($sDefaultCurrency != $sCurrency) {
            try {
                $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION'    => 'GetExchangeRate',
                    'SUBSYSTEM' => 'Core',
                    'FROM'      => strtoupper($sDefaultCurrency),
                    'TO'        => strtoupper($sCurrency),
                ));
                if ($result['EXCHANGERATE'] > 0) {
                    MLDatabase::getDbInstance()->query('UPDATE `' . MagnalisterController::getShopwareMyContainer()->get('currency.repository')->getDefinition()->getEntityName() . "` SET `factor` = '" . $result['EXCHANGERATE'] . "' WHERE `iso_code` = '" . $sCurrency . "'");
                }
            } catch (MagnaException $e) {                
                throw new Exception('One Problem occured in updating Currency Rate');
            }
        }
        return $this;
    }

    public function getCurrencyRate($sCurrency, $sTargetCurrencyCode) {

        $sCurrencyCriteria = new Criteria();
        $sCurrencyCriteria->addFilter(new EqualsFilter('isoCode', $sCurrency));
        $oCurrency = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($sCurrencyCriteria, Context::createDefaultContext())->getEntities()->last();

        $TargetCurrencyCriteria = new Criteria();
        $TargetCurrencyCriteria->addFilter(new EqualsFilter('isoCode', $sTargetCurrencyCode));
        $oTargetCurrency = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($TargetCurrencyCriteria, Context::createDefaultContext())->getEntities()->last();
        if ($oTargetCurrency->getFactor() == 0) {
            throw new Exception('Currency rate cannot be 0');
        }
        $fRate = round((float) ($oCurrency->getFactor() / $oTargetCurrency->getFactor()), 2);
        return $fRate;
    }

    public function getShopCurrency($iShopId = null) {
        if ($iShopId !== null) {
            $SalesChannelCriteria = new Criteria();
            $SalesChannelCriteria->addFilter(new EqualsFilter('id', $iShopId));
            $SalesChannel = MagnalisterController::getShopwareMyContainer()->get('sales_channel.repository')->search($SalesChannelCriteria, Context::createDefaultContext())->getEntities()->last();
            $IsoCode = $SalesChannel->GetCurrencyId();
        } else {
            $IsoCode = Context::createDefaultContext()->getCurrencyId();
        }

        $oShopCurrencyCriteria = new Criteria();
        $oShopCurrencyCriteria->addFilter(new EqualsFilter('id', $IsoCode));
        $oShopCurrency = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($oShopCurrencyCriteria, Context::createDefaultContext())->getEntities()->last();

        return $oShopCurrency->getIsoCode();
    }

}
