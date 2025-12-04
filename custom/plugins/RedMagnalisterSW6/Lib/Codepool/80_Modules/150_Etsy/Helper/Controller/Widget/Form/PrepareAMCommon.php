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

MLFilesystem::gi()->loadClass('Form_Helper_Controller_Widget_Form_PrepareAMCommon');

class ML_Etsy_Helper_Controller_Widget_Form_PrepareAMCommon extends ML_Form_Helper_Controller_Widget_Form_PrepareAMCommon {
    public function getManipulateMarketplaceAttributeValues($values) {
        $aSelectValues = array();
        foreach ($values as $sKey => $sValue) {
            $aSelectValues[$sKey . '-' . $sValue] = $sValue;
        }
        return $aSelectValues;
    }

    public function getExtraFieldset($mParentValue, $translation, $ident) {
        return array(
            'id' => $ident . '_fieldset_optional_extra_' . $mParentValue,
            'legend' => array(
                'i18n' => $translation,
                'template' => 'two-columns',
            ),
            'row' => array(
                'template' => 'default',
            ),
            'fields' => array(),
        );
    }

    public function populateExtraFieldsetFields($aSubfield, $aSubfieldExtra, $aAjaxField){
        return array(
            'subFieldsContainer' => $aSubfield,
            'subFieldsContainerExtra' => $aSubfieldExtra,
            'ajax' => $aAjaxField,
        );
    }

    /**
     * @param $aExtraFieldsetOptional
     * @param ML_Form_Controller_Widget_Form_Abstract $oController
     * @return string
     */
    public function getExtraFieldsetView($aExtraFieldsetOptional, ML_Form_Controller_Widget_Form_Abstract $oController) {
        if (!empty($aExtraFieldsetOptional['fields'])) { ?>
            <table class="attributesTable ml-js-attribute-matching" id="attributesTableOptional">
                <?php  $oController->includeView('widget_form_type_attributefield', array('aFieldset' => $aExtraFieldsetOptional)); ?>
            </table> <?php
        }
    }

    public function getExtraFieldsetType() {
        return array();
    }

    public function isAttributeExtra($key) {
        if (strpos($key, 'Extra_') === 0) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $aMatchedAttributes
     * @return bool
     * @throws MLAbstract_Exception
     */
    public function validateMatchedAttributes($aMatchedAttributes) {
        $result = null;
        $categoryVariationAttributeCounter = 0;
        foreach ($aMatchedAttributes as $key => $attribute) {
            if (strpos($key, 'Extra_') === false && strpos($key, '_unit') === false) {
                $categoryVariationAttributeCounter++;
            }
        }
        if ($categoryVariationAttributeCounter > 2) {
            $result = MLI18n::gi()->get('etsy_prepare_verify_attributes_error_1727251173');
        }

        return $result;
    }
}
