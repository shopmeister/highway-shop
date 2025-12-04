<?php
if (!class_exists('ML', false)) {
    throw new Exception();
}
/**
 * @var $aField
 */
$aMyField = $aField;
$aMyField['type'] = 'select';
$aMyField['multiple'] = true;
$aMyField['select2'] = true;
$aMyField['name'] = $aField['name'];
$aMyField['value'] = isset($aField['value']) ? $aField['value'] : array();
$aMyField['placeholder'] = $this->__('ML_AMAZON_LABEL_APPLY_PLEASE_SELECT');
$this->includeType($aMyField);
