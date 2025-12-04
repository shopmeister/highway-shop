<script type="text/javascript">/*<![CDATA[*/

    jqml(document).ready(function(){
        jqml('#globalButtonBox a.cron').click(function(){
            var cronWarning = JSON.parse(jqml(this).attr('data-warning-text'));
            var cronTitle = JSON.parse(jqml(this).attr('data-warning-title'));
            var hasCron = jqml(this).hasClass('cron');
            var steps = jqml(this).data('steps');
            var currentA = jqml(this);
            jqml('<div class="ml-modal dialog2" title="' + cronTitle + '">' + cronWarning + '</div>').jDialog({
                width: '500px',
                buttons: {
                    Cancel: {
                        'text': '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_ABORT')); ?>',
                        click: function () {
                            jqml(this).dialog('close');
                        }
                    },
                    Ok: {
                        'text': '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_OK')); ?>',
                        click: function () {

                            var aSteps=[];
                            if(hasCron){
                                aSteps = steps;
                            }
                            currentA.magnalisterRecursiveAjax({
                                sOffset:'<?php echo MLHttp::gi()->parseFormFieldName('offset') ?>',
                                sAddParam:'<?php echo MLHttp::gi()->parseFormFieldName('ajax') ?>=true',
                                aSteps: aSteps,
                                oI18n:{
                                    sProcess    : '<?php echo $this->__s('ML_STATUS_FILTER_SYNC_CONTENT',array('\'')) ?>',
                                    sError      : '<?php echo $this->__s('ML_ERROR_LABEL',array('\'')) ?>',
                                    sSuccess    : '<?php echo $this->__s('ML_STATUS_FILTER_SYNC_SUCCESS',array('\'')) ?>'
                                },
                                onFinalize: function(){
                                    window.location=window.location;//reload without post
                                },
                                onProgessBarClick:function(data){
                                    console.dir({data:data});
                                },
                            });
                            jqml(this).dialog('close');
                        }
                    }
                }
            });
            return false;
        });

        jqml('#ml-show-update-admission').click(function(event) {
            event.preventDefault();
            jqml('<div class="ml-modal dialog2" title="<?php echo addslashes(MLI18n::gi()->get('ML_MESSAGE_BEFORE_UPDATE_TITLE')) ?>"><?php echo addslashes(MLI18n::gi()->get('ML_MESSAGE_BEFORE_UPDATE_TEXT')) ?></div>').jDialog({
                width: '500px',
                buttons: {
                    Cancel: {
                        'text': '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_ABORT')); ?>',
                        click: function () {
                            jqml(this).dialog('close');
                        }
                    },
                    Ok: {
                        'text': '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_OK')); ?>',
                        click: function () {
                            jqml('#ml-run-update').click();
                            jqml(this).dialog('close');
                        }
                    }
                }
            });
        });
    });
    /*]]>*/</script>
