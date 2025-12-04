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
MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract');

class ML_Etsy_Controller_Etsy_Prepare_Apply_Form extends ML_Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract {

    protected function shippingProfileField(&$aField) {
        $shippingProfiles = $this->callApi('GetShippingProfiles');
        foreach ($shippingProfiles['ShippingProfiles'] as $shippingProfile) {
            $aField['values'][$shippingProfile['shippingProfileId'].''] = $shippingProfile['title'];
        }
    }

    protected function processingProfileField(&$aField) {
        $processingProfiles = $this->callApi('GetProcessingProfiles', array(), 120);
        if (isset($processingProfiles['ProcessingProfiles'])) {
            foreach ($processingProfiles['ProcessingProfiles'] as $readinessState => $groupedProcessingProfiles) {
                if ($readinessState == 'ready_to_ship') {
                    $aField['values'][MLI18n::gi()->{'etsy_config_item_preparation_readiness_state_ready_to_ship'}] =
                        $this->getProcessingProfileKeyPairValues($groupedProcessingProfiles);
                } else {
                    $aField['values'][MLI18n::gi()->{'etsy_config_item_preparation_readiness_state_made_to_order'}] =
                        $this->getProcessingProfileKeyPairValues($groupedProcessingProfiles);
                }
            }
        } else {
            $aField['values'][] = MLI18n::gi()->{'etsy_prepare_empty_list_processing_profiles'};;
        }
    }

    private function getProcessingProfileKeyPairValues($processingProfiles) {
        $result = array();
        foreach ($processingProfiles as $processingProfile) {
            $key = $processingProfile['readinessStateId'];
            $value = $processingProfile['processingDaysDisplayLabel'];
            $result[$key] = $value;
        }

        return $result;
    }

    public function processingprofilereadinessstateField(&$aField) {
        $aField['values'] = MLModule::gi()->getListOfReadinessStates();
    }

    protected function callGetCategoryDetails($sCategoryId) {
        return MLModule::gi()->getCategoryDetails($sCategoryId);
    }

    public function callAjaxSaveShippingProfile() {
        try {
            MLModule::gi()->saveShippingProfile();
            MLCache::gi()->flush();
            $aField = $this->getField('shippingprofile');
            $aField['type'] = 'select';//type seems missing from getField
            $sField = $this->includeTypeBuffered($aField);
            MLMessage::gi()->addSuccess(MLI18n::gi()->ML_LABEL_SAVED_SUCCESSFULLY);
            MLSetting::gi()->add('aAjaxPlugin', array(
                'dom' => array(
                    '#etsy_prepare_apply_form_field_shippingprofile' => $sField,
                ),
            ));
        } catch (MagnaException $e) {
            MLMessage::gi()->addError($e->getMessage());
        } catch (Exception $e) {
            MLMessage::gi()->addDebug($e);
        }
    }

    public function callAjaxSaveProcessingProfile() {
        try {
            MLModule::gi()->saveProcessingProfile();
            MLCache::gi()->flush();
            $aField = $this->getField('processingprofile');
            $aField['type'] = 'select';//type seems missing from getField
            $sField = $this->includeTypeBuffered($aField);
            MLMessage::gi()->addSuccess(MLI18n::gi()->ML_LABEL_SAVED_SUCCESSFULLY);
            MLSetting::gi()->add('aAjaxPlugin', array(
                'dom' => array(
                    '#etsy_prepare_apply_form_field_processingprofile' => $sField,
                ),
            ));
        } catch (MagnaException $e) {
            MLMessage::gi()->addError($e->getMessage());
        } catch (Exception $e) {
            MLMessage::gi()->addDebug($e);
        }
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

    protected function triggerBeforeFinalizePrepareAction() {
        $blReturn = parent::triggerBeforeFinalizePrepareAction();
        $oPreparedProduct = current($this->oPrepareList->getList());
        if (is_object($oPreparedProduct)) {
            foreach ($this->oPrepareList->getList() as $oVariant) {
                if ($oVariant->data()['shopvariation']) {
                    $validationErrorMessage = MLFormHelper::getPrepareAMCommonInstance()->validateMatchedAttributes($oVariant->data()['shopvariation']);
                    if ($validationErrorMessage !== null) {
                        MLMessage::gi()->addError($validationErrorMessage, null, !MLHttp::gi()->isAjax());
                        $this->oPrepareList->set('verified', 'ERROR');
                        $blReturn = false;
                    }
                }
                break;
            }
        } else {
            MLMessage::gi()->addDebug("One of products is not existed , please try again");
            $blReturn = false;
        }

        return $blReturn;
    }

}
