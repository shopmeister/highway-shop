<?php
if (!class_exists('ML', false))
    throw new Exception();

$expires = '';
try {
    $expires .= MLModule::gi()->getConfig($aField['realname'].'.expires');

    $firstToken = '';
    if (!empty($expires)) {
        if (is_numeric($expires))
            $expires = sprintf($this->__('ML_EBAY_TEXT_TOKEN_EXPIRES_AT'), date('d.m.Y H:i:s', $expires));
        else
            $expires = sprintf($this->__('ML_EBAY_TEXT_TOKEN_EXPIRES_AT'), date('d.m.Y H:i:s', unix_timestamp($expires)));
    } else {
        $firstToken = ' action';
    }
} catch (Exception $oExc) {

}
?>
<input class="mlbtn<?php echo $firstToken ?> action text" type="button" value="<?php echo $this->__('ML_EBAY_BUTTON_TOKEN_NEW') ?>" id="requestToken<?php echo $aField['id'] ?>"/>
<?php echo $expires ?>
<div id="js-ml-modal-stockWarning" class="dialog2" title="<?php echo MLI18n::gi()->get('ebay_config_token_popup_header'); ?>">
    <?php echo MLI18n::gi()->get('ebay_config_token_popup_content'); ?>
</div>

<script type="text/javascript">/*<![CDATA[*/
    jqml(document).ready(function () {
        jqml('#requestToken<?php echo $aField['id']?>').click(function () {
            var eModal = jqml('#js-ml-modal-stockWarning');
            eModal.dialog({
                modal: true,
                width: '600px',
                buttons: [
                    {
                        text: "<?php echo $this->__('ML_BUTTON_LABEL_ABORT'); ?>",
                        click: function () {
                            jqml(this).dialog("close");
                            return false;
                        }
                    },
                    {
                        text: "<?php echo $this->__('ML_BUTTON_LABEL_CONTINUE'); ?>",
                        click: function () {
                            jqml(this).dialog("close");
                            openIframe();
                            return false;
                        }
                    }
                ]
            });

            function openIframe() {
                jqml.blockUI(blockUILoading);
                jqml.ajax({
                    'method': 'get',
                    'url': '<?php echo MLHttp::gi()->getCurrentUrl(array('method' => 'GetTokenCreationLink', 'what' => $aField['realname'], 'kind' => 'ajax')) ?>',
                    'success': function (data) {
                        jqml.unblockUI();
                        let error = false;
                        try {
                            var data = jqml.parseJSON(data);
                            console.log(data)
                        } catch (e) {
                            console.log(e);
                            error = true;
                        }
                        if (error || data.error + '' !== '') {
                            jqml('<div></div>')
                                .attr('title', '<?php echo $this->__s('ML_EBAY_ERROR_CREATE_TOKEN_LINK_HEADLINE', array('\'', "\n", "\r")) ?>')
                                .html('<?php echo $this->__s('ML_EBAY_ERROR_CREATE_TOKEN_LINK_TEXT', array('\'', "\n", "\r"));  ?>')
                                .jDialog();
                        } else {
                            var hwin = window.open(data.iframeUrl, "popup", "resizable=yes,scrollbars=yes");
                            if (hwin.focus) {
                                hwin.focus();
                            }
                        }
                    }
                });
            }
        });
    });
    /*]]>*/</script>
