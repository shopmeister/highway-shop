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

class ML_Shopware6_Model_Language extends ML_Shop_Model_Language_Abstract {

    public function getCurrentIsoCode() {

        if (MLSetting::gi()->sTranslationLanguage) {
            $IsoCode = MLSetting::gi()->sTranslationLanguage;
        } else {
            $sLangId = MagnalisterController::getShopwareLanguageId();
            $LanguageCriteria = new Criteria();
            $Language = MagnalisterController::getShopwareMyContainer()->get('language.repository')->search($LanguageCriteria->addFilter(new EqualsFilter('id', $sLangId)), Context::createDefaultContext())->first();
            if (!is_object($Language)) {
                $Language = MagnalisterController::getShopwareMyContainer()->get('language.repository')->search(new Criteria(), Context::createDefaultContext())->first();
            }
            if (is_object($Language)) {
                $localeCriteria = new Criteria();
                $locale = MagnalisterController::getShopwareMyContainer()->get('locale.repository')->search($localeCriteria->addFilter(new EqualsFilter('locale.id', $Language->getLocaleId())), Context::createDefaultContext())->first();
                if ($locale !== null) {
                    $IsoCode = substr($locale->getCode(), 0, strpos($locale->getCode(), "-"));
                }
            } else {
                $IsoCode = 'en';
            }
        }
        return $IsoCode;
    }

    public function getCurrentCharset() {
        return 'UTF-8';
    }

}
