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

var mlPrepareRecursiveAjax = null;
(function ($) {
    mlPrepareRecursiveAjax = {
        triggerPrepareRecursiveAjax: function (form, aExtraData) {
            //aExtraData = ml[action][prepareaction]:...
            // check if the form is product prepare form to trigger recursive ajax
            if(
                form.attr('id').endsWith('_prepare_apply_form')
                || form.attr('id').endsWith('_prepare_form')
            ) {
                var currentA = form,
                    ajaxData = $("#magnalister_recursive_ajax_data"),
                    redirect= true;
                //add specific title to popup progress bar
                try {
                    var attributeTitle = ajaxData.data('stitle-attribute');
                    var variationThemeTitle = ajaxData.data('stitle-variation');
                    if (attributeTitle && aExtraData['ml[action][prepareaction]'] === 'variation_theme') {
                        $('.magnalisterForm').attr('title', variationThemeTitle);
                    } else if (attributeTitle && aExtraData['ml[action][prepareaction]'] === '0') {
                        $('.magnalisterForm').attr('title', attributeTitle);
                    } else {
                        $('.magnalisterForm').attr('title', ajaxData.data('stitle'));
                    }
                }catch(e){
                    console.log(e);
                    $('.magnalisterForm').attr('title', ajaxData.data('stitle'));
                }

                currentA.magnalisterRecursiveAjax({
                    sOffset: ajaxData.data('offset'),
                    sAddParam: ajaxData.data('ajax')+'=true',
                    aAddParam: [
                        "mlSerialize"
                    ],
                    oI18n:{
                        sProcess    : ajaxData.data('sprocess'),
                        sError      : ajaxData.data('serror'),
                        sSuccess    : ajaxData.data('ssuccess')
                    },
                    onFinalize: function(blError){
                        if (redirect) {
                            window.location = ajaxData.data('redirect');
                        } else {
                            window.location = window.location;
                        }
                    },
                    onResponse          : function(requestData){
                        redirect = JSON.parse( requestData ).info.redirect;
                    },
                    onProgessBarClick:function(data){
                        console.dir({data:data});
                    },
                    blDebug: ajaxData.data('bldebug'),
                    sDebugLoopParam: ajaxData.data('saveselection')+'=true'
                });

                $(".ui-dialog").css({
                    'position': 'fixed',
                    'top': '50%',
                    'left': '50%',
                    'transform': 'translate(-50%, -50%)'
                });
                //fix overlay in case we have errors in the form
                $(".ui-widget-overlay").css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                });
                return true;
            } else {
                return false
            }

        },
    }
})(jqml);
