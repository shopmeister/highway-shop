<?php
$aField['type'] = 'bool';
/**
 * @var ML_Shop_View_Widget_Form_Type_Abstract $this
 */
$this->includeType($aField);
?>
<script type="text/javascript">/*<![CDATA[*/
    (function ($) {
        $(document).ready(function () {
            $('<?php echo '#'.$aField['id'] ?>').change(function (event, rec) {
                var blProp = $(this).prop('checked');//actual state
                $('<?php
                    foreach ($aField['importonlypaid']['disablefields'] as &$sField) {
                        $sField = $this->getField($sField, 'id');
                    }
                    echo '#'.implode(', #', $aField['importonlypaid']['disablefields']);
                    ?>').attr('disabled', blProp);
                if ($(this).is(':checked') === true) {
                    $('#ebay_config_orderimport_field_orderstatus_paid').val($('#ebay_config_orderimport_field_orderstatus_open').val());
                }
            }).trigger('change');
            $('#ebay_config_orderimport_field_orderstatus_open').on('change', function () {
                if ($('<?php echo '#'.$aField['id'] ?>').is(':checked') === true) {
                    console.log($(this).val());
                    $('#ebay_config_orderimport_field_orderstatus_paid').val($(this).val());
                }
            });
        });
    })(jqml);
    /*]]>*/</script>