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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/**
 * @var array $aField
 */

if (!class_exists('ML', false)) {
    throw new Exception();
}

$data_attributes = '';
if (array_key_exists('html-data', $aField) && is_array($aField['html-data'])) {
    foreach ($aField['html-data'] as $key => $value) {
        $data_attributes .= ' data-'.$key.'="'.$value.'"';
    }
}
if (empty($aField['cssclasses'])) {
    $aField['cssclasses'] = array();
}
if (!empty($aField['cssclass'])) {
    $aField['cssclasses'][] = $aField['cssclass'];
}
$highlightClass = '';
if (MLRequest::gi()->has('highlight_fields')) {
    $highlightFields = explode(',', MLRequest::gi()->get('highlight_fields'));
    if (in_array($aField['realname'], $highlightFields)) {
    
        $aField['cssclasses'][] = 'highlight';
    }
}
?>
<input class="fullwidth<?php echo ((isset($aField['required']) && empty($aField['value'])) ? ' ml-error' : '').(isset($aField['cssclasses']) ? ' '.implode(' ', $aField['cssclasses']) : '') ?>"
       type="text" <?php echo isset($aField['id']) ? "id='{$aField['id']}'" : ''; ?>
       name="<?php echo MLHttp::gi()->parseFormFieldName($aField['name']) ?>"
       placeholder="<?php echo isset($aField['placeholder']) ? $aField['placeholder'] : (!empty($aField['i18n']['placeholder']) ? $aField['i18n']['placeholder'] : ''); ?>"<?php echo $data_attributes; ?>
    <?php echo(isset($aField['value']) && is_scalar($aField['value']) ? 'value="'.htmlspecialchars($aField['value'], ENT_COMPAT).'"' : '') ?>
    <?php if (array_key_exists('disabled', $aField) && $aField['disabled']) {echo 'disabled="disabled"';} ?>
    <?php echo isset($aField['maxlength']) ? "maxlength='{$aField['maxlength']}'" : ''; ?> />

<?php
if (array_key_exists('i18n', $aField) && is_array($aField['i18n'])
    && array_key_exists('tooltip', $aField['i18n']) && $aField['i18n']['tooltip']
) {
    $tooltipField = $aField;
    $tooltipField['type'] = 'tooltip';
    $this->includeType($tooltipField);
}
