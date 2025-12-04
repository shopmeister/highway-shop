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

/**
 * @var array $aField
 */

if (!class_exists('ML', false))
    throw new Exception();
$sDataMlAlert = 
    (
        isset($aField['i18n']) && is_array($aField['i18n']) && array_key_exists('alert', $aField['i18n'])
        && is_array($aField['i18n']['alert']) 
        && !empty($aField['i18n']['alert'])
    ) 
    ? json_encode(array(
        'abort' => MLI18n::gi()->get('ML_BUTTON_LABEL_ABORT'), 
        'ok' => MLI18n::gi()->get('ML_BUTTON_LABEL_OK')
    ))
    : ''
;
$blMultiple = array_key_exists('multiple', $aField) && $aField['multiple'];

if(isset($aField['i18n']['firstoption']) && is_array($aField['i18n']['firstoption'])){
    $aField['values'] = $aField['i18n']['firstoption'] + $aField['values'];
}

$data_attributes = '';
if (array_key_exists('html-data', $aField) && is_array($aField['html-data'])) {
    foreach ($aField['html-data'] as $key => $value) {
        $data_attributes .= ' data-'.$key.'="'.$value.'"';
    }
}

$highlightClass = '';
if (MLRequest::gi()->has('highlight_fields')) {
    $highlightFields = explode(',', MLRequest::gi()->get('highlight_fields'));
    if (in_array($aField['realname'], $highlightFields)) {
        if (empty($aField['cssclasses'])) {
            $aField['cssclasses'] = array();
        }
        $aField['cssclasses'][] = 'highlight';
    }
}
?>
<select <?php echo $blMultiple ? 'multiple="multiple" size="12" ' : ''; ?><?php echo (isset($aField['cssclasses']) ? '' . 'class="'.implode(' ', $aField['cssclasses']).'"' : ' ') ?><?php echo empty($sDataMlAlert) ? '' : "data-ml-alert='".$sDataMlAlert."' " ?> name="<?php echo empty($aField['name']) ? '' : MLHttp::gi()->parseFormFieldName($aField['name']);?><?php echo $blMultiple ? '[]' : ''; ?>" <?php echo isset($aField['id']) ? 'id="'.$aField['id'].'"' : uniqid('autoprefix-option')?>
                <?php 
                    if (!array_key_exists('value', $aField) || empty($aField['value'])) {
                        $blValueExists = true;
                    } else {
                        $blValueExists = false;
                        $aNoNestedValues = array();
                        foreach (array_key_exists('values', $aField) ? $aField['values'] : array() as$sOptionKey => $sOptionValue) {
                            if (is_array($sOptionValue)) {
                                foreach (array_keys($sOptionValue) as $sCurrentOptionValue) {
                                    $aNoNestedValues[] = $sCurrentOptionValue;
                                }
                            } else {
                                $aNoNestedValues[] = $sOptionKey;
                            }
                        }
                        foreach ((array)$aField['value'] as $sCurrentValue) {
                            if (in_array($sCurrentValue, $aNoNestedValues)) {
                                $blValueExists = true;
                                break;
                            }
                        }
                    }
                    if (
                        isset($aField['cssclass']) 
                        || (isset($aField['required']) && empty($aField['value']))
                        || !$blValueExists
                    ) {
                        echo 'class="';
                        echo isset($aField['cssclass']) ? (is_array($aField['cssclass'])? implode(' ', $aField['cssclass']):$aField['cssclass']) : '';
                        if (
                            (isset($aField['required']) && empty($aField['value'])) 
                            || !$blValueExists
                        ) {
                            echo isset($aField['cssclass']) ? ' ' : '';
                            echo 'ml-error';
                        }
                        echo '"';
                    }
                ?>
        style="width:100%;" class="<?php echo !empty($aField['class']) ? $aField['class'] : '' ?>"<?php echo $data_attributes; ?>
        <?php if (array_key_exists('disabled', $aField) && $aField['disabled']) {echo 'disabled="disabled"';} ?>>
    <?php
        if (!$blValueExists && !$blMultiple) {
            ?><option selected="selected" disabled="disabled" value="<?php echo $aField['value']; ?>"><?php echo MLI18n::gi()->get('ML_LABEL_INVALID'); ?></option><?php
        }
        $aField['type']='select_optgroup';
        $this->includeType($aField);
    ?>
</select>
<?php
    if (!empty($sDataMlAlert)) {
        MLSettingRegistry::gi()->addJs('jquery.magnalister.form.select.js');
    }
?>
<?php
if (isset($aField['select2']) && $aField['select2']) {
?>
<script>
    jqml(document).ready(function () {
        let select2Options = {
            width: 'resolve',
            placeholder: '',
        };

        <?php if (isset($aField['placeholder'])) { ?>
        select2Options.placeholder = '<?php echo addslashes(html_entity_decode($aField['placeholder'])); ?>';
        <?php } ?>
        
        jqml('#' + '<?php echo $aField['id']?>').select2(select2Options);
        
        <?php 
        // Check if the original select element has ml-error class and apply it to Select2 container
        if ((isset($aField['cssclasses']) && in_array('ml-error', $aField['cssclasses'])) || 
            (isset($aField['cssclass']) && (
                (is_array($aField['cssclass']) && in_array('ml-error', $aField['cssclass'])) ||
                (is_string($aField['cssclass']) && strpos($aField['cssclass'], 'ml-error') !== false)
            )) ||
            ((isset($aField['required']) && empty($aField['value'])) || !$blValueExists)
        ) { ?>
        // Add ml-error class to Select2 container
        console.log('Adding ml-error to Select2 container for field: <?php echo $aField['id']; ?>');
        jqml('#select2-<?php echo $aField['id']?>-container').closest('.select2-container').addClass('ml-error');
        <?php } ?>
    });
</script>
<?php
}
?>

<?php
if (array_key_exists('i18n', $aField) && is_array($aField['i18n'])
    && array_key_exists('tooltip', $aField['i18n']) && $aField['i18n']['tooltip']
) {
    $tooltipField = $aField;
    $tooltipField['type'] = 'tooltip';
    $this->includeType($tooltipField);
}

if (array_key_exists('crossBorders', $aField) && is_array($aField['crossBorders'])) {
    $crossBordersField = $aField;
    $crossBordersField['type'] = 'crossBorders';
    $this->includeType($crossBordersField);
}
