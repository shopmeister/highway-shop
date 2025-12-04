<?php

/**
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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLFilesystem::gi()->loadClass('Form_Controller_Widget_Form_VariationsAbstract');

class ML_Hood_Controller_Hood_Prepare_Variations extends ML_Form_Controller_Widget_Form_VariationsAbstract
{
    protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

    public function saveAction($blExecute = true) {
        if ($blExecute) {
            $aActions = $this->getRequest($this->sActionPrefix);
            $savePrepare = $aActions['saveaction'] === '1';
            $aMatching = $this->getRequestField();

            //hood attribute matching does not have dependency with category  $aMatching['variationgroups.value']
            $sIdentifier = '1';
            $sCustomIdentifier = $this->getCustomIdentifier();
            if (isset($aMatching['attributename'])) {
                $sIdentifier = $aMatching['attributename'];
                if ($sIdentifier === 'none') {
                    MLMessage::gi()->addError(self::getMessage('_prepare_match_variations_attribute_missing'));
                    return;
                }
            }

            if (isset($aMatching['variationgroups'])) {
                $aMatching = $aMatching['variationgroups'][$sIdentifier];
                $oVariantMatching = $this->getVariationDb();
                $oVariantMatching->deleteVariation($sIdentifier, $sCustomIdentifier);
                if ($sIdentifier === 'new') {
                    $sIdentifier = $aMatching['variationgroups.code'];
                    unset($aMatching['variationgroups.code']);
                }
                $aErrors = array();
                $addNotAllValuesMatchedNotice = false;
                $previouslyMatchedAttributes = array();
                $emptyCustomName = false;
                foreach ($aMatching as $key => &$value) {
                    if (isset($value['Required'])) {
                        $value['Required'] = (bool)$value['Required'];
                    }

                    $value['Error'] = false;
                    $isSelectedAttribute = $key === $aActions['saveaction'];

                    if ($value['Code'] === '' || (empty($value['Values']) && $value['Values'] !== '0')) {
                        if (isset($value['Required']) && $value['Required']) {
                            if ($savePrepare || $isSelectedAttribute) {
                                if ($savePrepare) {
                                    $aErrors[] = self::getMessage('_prepare_variations_error_text',
                                        array('attribute_name' => $sAttributeName));
                                }
                                $value['Error'] = true;
                            }
                        }

                        // $key should be unset whenever condition (isset($value['Required']) && $value['Required'] && $savePrepare)
                        // is not true.
                        if (!isset($value['Required']) || !$value['Required'] || !$savePrepare) {
                            unset($aMatching[$key]);
                        }

                        continue;
                    }
                    // this field is only available on attributes that are FreeText Kind
                    // this is used to improve auto matching if checked no matched values will be saved
                    // we will use shop values and do the matching during product upload
                    if (isset($value['UseShopValues']) && $value['UseShopValues'] === '1') {
                        $value['Values'] = array();
                    } else {
                        $this->transformMatching($value);
                        $this->validateCustomAttributes($key, $value, $previouslyMatchedAttributes, $aErrors,
                            $emptyCustomName, $savePrepare, $isSelectedAttribute);
                        $this->removeCustomAttributeHint($value);
                        $sAttributeName = $value['AttributeName'];

                        if (!isset($value['Code'])) {
                            // this will happen only if attribute was matched and then it was deleted from the shop
                            $value['Code'] = '';
                        }

                        if (!is_array($value['Values']) || !isset($value['Values']['FreeText'])) {
                            continue;
                        }

                        $sInfo = self::getMessage('_prepare_variations_manualy_matched');
                        $sFreeText = $value['Values']['FreeText'];
                        unset($value['Values']['FreeText']);

                        if ($value['Values']['0']['Shop']['Key'] === 'noselection'
                            || $value['Values']['0']['Marketplace']['Key'] === 'noselection'
                        ) {
                            unset($value['Values']['0']);
                            if (empty($value['Values']) && $value['Required'] && ($savePrepare || $isSelectedAttribute)) {
                                if ($savePrepare) {
                                    $aErrors[] = self::getMessage('_prepare_variations_error_text',
                                        array('attribute_name' => $sAttributeName));
                                }
                                $value['Error'] = true;
                            }

                            foreach ($value['Values'] as $k => &$v) {
                                if (empty($v['Marketplace']['Info']) || $v['Marketplace']['Key'] === 'manual') {
                                    $v['Marketplace']['Info'] = $v['Marketplace']['Value'].
                                        self::getMessage('_prepare_variations_free_text_add');
                                }
                            }

                            continue;
                        }

                        if ($value['Values']['0']['Marketplace']['Key'] === 'reset') {
                            $aMatching[$key]['Values'] = array();
                            continue;
                        }

                        if ($value['Values']['0']['Marketplace']['Key'] === 'manual') {
                            $sInfo = self::getMessage('_prepare_variations_free_text_add');
                            if (empty($sFreeText)) {
                                if ($savePrepare || $isSelectedAttribute) {
                                    if ($savePrepare) {
                                        $aErrors[] = $sAttributeName.self::getMessage('_prepare_variations_error_free_text');
                                    }
                                    $value['Error'] = true;
                                }

                                unset($value['Values']['0']);
                                continue;
                            }

                            $value['Values']['0']['Marketplace']['Value'] = $sFreeText;
                        }

                        if ($value['Values']['0']['Marketplace']['Key'] === 'auto') {
                            $addNotAllValuesMatchedNotice = !$this->autoMatch($sIdentifier, $key, $value);
                            continue;
                        }

                        $this->checkNewMatchedCombination($value['Values']);
                        if ($value['Values']['0']['Shop']['Key'] === 'all') {
                            $newValue = array();
                            $i = 0;
                            $matchedMpValue = $value['Values']['0']['Marketplace']['Value'];

                            foreach ($this->getShopAttributeValues($value['Code']) as $keyAttribute => $valueAttribute) {
                                $newValue[$i]['Shop']['Key'] = $keyAttribute;
                                $newValue[$i]['Shop']['Value'] = $valueAttribute;
                                $newValue[$i]['Marketplace']['Key'] = $value['Values']['0']['Marketplace']['Key'];
                                $newValue[$i]['Marketplace']['Value'] = $value['Values']['0']['Marketplace']['Value'];
                                // $matchedMpValue can be array if it is multi value, so that`s why this is checked and converted to
                                // string if it is. That is done because this information will be displayed in matched table.
                                $newValue[$i]['Marketplace']['Info'] = (is_array($matchedMpValue) ? implode(', ', $matchedMpValue)
                                        : $matchedMpValue).$sInfo;
                                $i++;
                            }

                            $value['Values'] = $newValue;
                        } else {
                            foreach ($value['Values'] as $k => &$v) {
                                if (empty($v['Marketplace']['Info'])) {
                                    // $v['Marketplace']['Value'] can be array if it is multi value, so that`s why this is checked
                                    // and converted to string if it is. That is done because this information will be displayed in matched
                                    // table.
                                    $v['Marketplace']['Info'] = (is_array($v['Marketplace']['Value']) ?
                                            implode(', ', $v['Marketplace']['Value']) : $v['Marketplace']['Value']).$sInfo;
                                }
                            }
                        }
                    }
                }

                $oVariantMatching->set('Identifier', $sIdentifier)
                    ->set('CustomIdentifier', $sCustomIdentifier)
                    ->set('ShopVariation', json_encode($aMatching))
                    ->set('ModificationDate', date('Y-m-d H:i:s'))
                    ->save();

                if ($savePrepare) {
                    $showSuccess = empty($aErrors) && !$addNotAllValuesMatchedNotice;
                    if ($showSuccess) {
                        MLRequest::gi()->set('resetForm', true);
                        MLMessage::gi()->addSuccess(self::getMessage('_prepare_variations_saved'));
                    } else {
                        foreach ($aErrors as $sError) {
                            MLMessage::gi()->addError($sError);
                        }

                        if ($addNotAllValuesMatchedNotice) {
                            MLMessage::gi()->addNotice(self::getMessage('_prepare_match_notice_not_all_auto_matched'));
                        }
                    }
                } else if ($addNotAllValuesMatchedNotice) {
                    MLMessage::gi()->addNotice(self::getMessage('_prepare_match_notice_not_all_auto_matched'));
                }
            } else {
                MLMessage::gi()->addError(self::getMessage('_prepare_match_variations_no_selection'));
            }
        }
    }

}
