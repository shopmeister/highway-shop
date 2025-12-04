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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
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
use Shopware\Core\System\Currency\CurrencyFormatter;

class ML_Shopware6_Helper_Model_Price {

    public function getPriceByCurrency($mValue, $sCurrency = null, $blFormatted = false) {
        if (MLModule::gi()->getConfig('lang') !== null) {
            $lang = MLModule::gi()->getConfig('lang');
        } else {
            $lang = Defaults::LANGUAGE_SYSTEM;
        }
        if ($sCurrency === null) {
            $format = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search(new Criteria(['id' => Defaults::CURRENCY]), Context::createDefaultContext())->first();
        } else {
            $criteria = new Criteria();
            $format = MagnalisterController::getShopwareMyContainer()->get('currency.repository')->search($criteria->addFilter(new EqualsFilter('isoCode', (string) $sCurrency)), Context::createDefaultContext())->first();
            if ($format == null) {
                throw new Exception('Currency ' . $sCurrency . ' doesn\'t exist in your shop ');
            }
        }

        $mValue = MagnalisterController::getShopwareMyContainer()->get(CurrencyFormatter::class)->formatCurrencyByLanguage($mValue, $format->getIsoCode(), $lang , Context::createDefaultContext());
        if (!$blFormatted) {
            $mValue = (float) MLShopware6Alias::getPriceModel()->unformat((string) $mValue);
        }

        return $mValue;
    }

}