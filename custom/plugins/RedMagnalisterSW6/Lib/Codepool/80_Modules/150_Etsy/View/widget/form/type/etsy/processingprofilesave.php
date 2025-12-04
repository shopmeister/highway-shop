<?php if (!class_exists('ML', false))
    throw new Exception(); ?>
<input class="mlbtn action text" type="button" value="<?php echo $this->__('ML_ETSY_BUTTON_SAVE_PROCESSING_TEMPLATE') ?>"
       id="saveProcessingTemplate"/>
<script type="text/javascript">/*<![CDATA[*/

    function getAlertBox(message, type) {
        return '<div class="' + type + 'Box">' +
            '   <table style="width:100%;border-spacing: 0;">' +
            '       <tbody class="hideChild">' +
            '          <tr>' +
            '              <th colspan="7">' + message +
               '                   <a role="button" class="ml-js-noBlockUi close-message" href="#" style="">' +
               '                      <span class="close-message-icon">close</span>' +
               '                   </a>' +
               '              </th>' +
               '         </tr>' +
               '      </tbody>' +
               '   </table>' +
               '</div>';
    }

    function getNoticeBox(message) {
        return getAlertBox(message, 'notice');
    }

    function getSuccessBox(message) {
        return getAlertBox(message, 'success');
    }

    function getErrorBox(message) {
        return getAlertBox(message, 'error');
    }


    jqml(document).ready(function () {

        jqml('#saveProcessingTemplate').click(function (e) {

            e.preventDefault();

            var readinessState = jqml("[name='ml[field][processingprofilereadinessstate]']");
            var minProcessingTime = jqml("[name='ml[field][processingprofileminprocessingtime]']");
            var maxProcessingTime = jqml("[name='ml[field][processingprofilemaxprocessingtime]']");

            jqml.blockUI(blockUILoading);
            jqml.post('<?php echo MLHttp::gi()->getCurrentUrl(array('method' => 'SaveProcessingProfile')) ?>',
                {
                    '<?php echo MLSetting::gi()->get('sRequestPrefix')?>': {
                        readinessState: readinessState.val(),
                        minProcessingTime: minProcessingTime.val(),
                        maxProcessingTime: maxProcessingTime.val(),
                    }
                }
            ).done(function (response) {
                window.scrollTo(0, 0);
                jqml.unblockUI(blockUILoading);
                jqml('#processingprofileajax').click();
            }).fail(function () {
                message.empty().append(getErrorBox('Server error'));
                jqml.unblockUI(blockUILoading);
            });
        });

        jqml('#etsy_config_prepare_fieldset_processingprofile').hide();
        jqml('#etsy_prepare_apply_form_fieldset_processingprofile').hide();
        jqml('#processingprofileajax').click(function (e) {
            e.preventDefault();
            jqml('#etsy_config_prepare_fieldset_processingprofile').toggle('slow', function () {
                if (jqml(this).is(':visible')) {
                    jqml('#processingprofileajax').html('-');
                } else {
                    jqml('#processingprofileajax').html('+');
                }
            });
        });
        jqml('#processingprofileajax').click(function (e) {
            e.preventDefault();
            jqml('#etsy_prepare_apply_form_fieldset_processingprofile').toggle('slow', function () {
                if (jqml(this).is(':visible')) {
                    jqml('#processingprofileajax').html('-');
                } else {
                    jqml('#processingprofileajax').html('+');
                }
            });
        });
    });
    /*]]>*/</script>