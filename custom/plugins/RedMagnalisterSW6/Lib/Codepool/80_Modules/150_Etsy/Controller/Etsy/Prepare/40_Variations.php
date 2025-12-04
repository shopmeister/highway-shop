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
MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_VariationsAbstract');

class ML_Etsy_Controller_Etsy_Prepare_Variations extends ML_Form_Controller_Widget_Form_VariationsAbstract {
    protected function callGetCategoryDetails($sCategoryId) {
        return MLModule::gi()->getCategoryDetails($sCategoryId);
    }

    protected function getExtraFieldset($mParentValue) {
        $i18n = $this->getFormArray('aI18n');
        $translation = isset($i18n['legend']['variationmatchingoptionalextra']) ? $i18n['legend']['variationmatchingoptionalextra'] : '';
        return MLFormHelper::getPrepareAMCommonInstance()->getExtraFieldset($mParentValue, $translation, $this->getIdent());
    }

    protected function populateExtraFieldsetFields($aSubfield, $aSubfieldExtra, $aAjaxField){
        return MLFormHelper::getPrepareAMCommonInstance()->populateExtraFieldsetFields($aSubfield, $aSubfieldExtra, $aAjaxField);
    }

    protected function getExtraFieldsetView($aExtraFieldsetOptional) {
        return MLFormHelper::getPrepareAMCommonInstance()->getExtraFieldsetView($aExtraFieldsetOptional, $this);
    }

    protected function getExtraFieldsetType() {
        return MLFormHelper::getPrepareAMCommonInstance()->getExtraFieldsetType();
    }

    protected function isAttributeExtra($key) {
        return MLFormHelper::getPrepareAMCommonInstance()->isAttributeExtra($key);
    }
}
