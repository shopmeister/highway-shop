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
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

    /* @var $this  ML_Productlist_Controller_Widget_ProductList_Abstract */
    /* @var $oList ML_Productlist_Model_ProductList_Abstract */
    /* @var $aStatistic array */
     if (!class_exists('ML', false))
         throw new Exception();
?>
<?php if ($this instanceof ML_Productlist_Controller_Widget_ProductList_Abstract) {
    $this->includeView('widget_productlist_action_resetButton');

    if (MLModule::gi()->getConfig('amazonAttributeMigrationTime') === null) {
        $sMpId = MLModule::gi()->getMarketPlaceId();
        $sMpName = MLModule::gi()->getMarketPlaceName();
        $sMigrateDataLink = MLHttp::gi()->getCurrentUrl(array('ajax' => true, 'method' => 'CheckKeys'))
        ?>

        <a style="visibility: hidden"
           class="ui-icon ui-corner-all ui-state-focus global-ajax ui-icon-arrowrefresh-wrap ml-js-noBlockUi"
           id="amazonAttributesMigration" title="<?php echo 'Amazon Attributes Migration' ?>"
           href="<?php echo MLHttp::gi()->getCurrentUrl(array('ajax' => true, 'method' => 'MigrateAttributes')) ?>">
            <span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>
        </a>
        <script>
            jqml(document).ready(function () {
                let currentA = jqml('#amazonAttributesMigration');
                let errorHappen = false;
                currentA.magnalisterRecursiveAjax({
                    sOffset: '<?php echo MLHttp::gi()->parseFormFieldName('offset') ?>',
                    oI18n: {
                        sProcess: '<?php echo $this->__s('ML_STATUS_FILTER_SYNC_CONTENT', array('\'')) ?>',
                        sError: '<?php echo $this->__s('ML_ERROR_LABEL', array('\'')) ?>',
                        sSuccess: '<?php echo $this->__s('Amazon_Productlist_Cell_aPreparedStatus__migration__title', array('\'')) ?>'
                    },
                    oFinalButtons: {
                        oError: [
                            {
                                text: 'Ok', click: function () {
                                    jqml(this).dialog('close');
                                }
                            }
                        ],
                    },
                    onFinalize: function () {
                        if (!errorHappen) {
                            setTimeout(function () {
                                window.location = window.location; //reload without post
                            }, 3000);
                        }
                    },
                    onResponse: function (data) {
                        try {
                            let response = JSON.parse(data);
                            if (response.error) {
                                let dialog = jqml('#recursiveAjaxDialog');

                                // Set custom error message
                                dialog.find('.errorBox').html(<?php echo json_encode($this->__s('ML_ERROR_GLOBAL')) ?>).show();
                                dialog.find('.successBoxBlue').hide();

                                console.error(response.message);
                                errorHappen = true;
                            }
                        } catch (e) {
                            // Handle JSON parsing errors
                            console.error('Response parsing error:', e);
                            errorHappen = true;
                        }
                    },
                    onProgessBarClick: function (data) {
                        console.dir({data: data});
                    },
                    blDebug: <?php echo MLSetting::gi()->get('blDebug') ? 'true' : 'false' ?>,
                    sDebugLoopParam: "<?php echo MLHttp::gi()->parseFormFieldName('saveSelection') ?>=true"
                });
            });
        </script>
    <?php } ?>
<?php } ?>
<?php
