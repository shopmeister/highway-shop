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

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Redgecko\Magnalister\Controller\MagnalisterController;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Defaults;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\System\Currency\CurrencyFormatter;

class ML_Shopware6_Model_Price extends ML_Shop_Model_Price_Abstract implements ML_Shop_Model_Price_Interface {

    public function format($fPrice, $sCode, $blConvert = true) { 
   
        if (!isset($sCode) || $sCode == null) {
            throw new Exception("the sCode should not be empty");
         }
        if ($blConvert) {    
                $criteria = new Criteria();
                $oCurrency = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($criteria->addFilter(new EqualsFilter('isoCode',(string)$sCode)), Context::createDefaultContext())->first();                      
                //@hex2bin
                if ($oCurrency->getFactor()) {
                    $fPrice = (float)$fPrice * (float)$oCurrency->getFactor();
                }                               
        }
        $mPrice= MLShopware6Alias::getPriceHelper()->getPriceByCurrency($fPrice, $sCode, true);
        return $mPrice;
    }

}