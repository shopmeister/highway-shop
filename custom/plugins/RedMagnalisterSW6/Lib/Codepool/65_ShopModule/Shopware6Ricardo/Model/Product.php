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

use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;

MLFilesystem::gi()->loadClass('Shopware6_Model_Product');

class ML_Shopware6Ricardo_Model_Product extends ML_Shopware6_Model_Product {

    public function priceAdjustment($fPrice) {
        $fPrice = round($fPrice, 2);
        //Check if last digit (second decimal) is 0 or 5. If not set 5 as default last digit
        $fPrice =
            (((int)(string)($fPrice * 100)) % 5) == 0 // cast to string because it seems php have float precision in background
                ? $fPrice
                : (((int)(string)($fPrice * 10)) / 10) + 0.05;
        // round again, to be sure that precision is 2
        return round($fPrice, 2);
    }

    public function getProperties($sSeparator = null) {
        if ($sSeparator === null) {
            return parent::getProperties(':');
        }
        return parent::getProperties($sSeparator);
    }

}
