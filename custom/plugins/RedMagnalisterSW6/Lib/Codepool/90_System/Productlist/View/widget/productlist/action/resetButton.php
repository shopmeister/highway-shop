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
/* @var $this  ML_Productlist_Controller_Widget_ProductList_Abstract */
/* @var $oList ML_Productlist_Model_ProductList_Abstract */
/* @var $aStatistic array */
if (!class_exists('ML', false))
    throw new Exception();
$marketplaceName = MLModule::gi()->getMarketPlaceName();
?>
<?php if ($this instanceof ML_Productlist_Controller_Widget_ProductList_Abstract) { ?>
    <div class="ml-container-action-head">
        <h4>
            <?php echo $this->__('ML_LABEL_ACTIONS') ?>
        </h4>
    </div>
    <div class="ml-container-action">
        <div class="ml-container-inner ml-container-wd">
            <input type="hidden" name="<?php echo MLHttp::gi()->parseFormFieldName('view') ?>" value="resetvalues" />
            <a style="padding-left: 0; padding-right: 0;" class="mlbtn-gray ml-js-noBlockUi"
                id="ml-<?php echo $marketplaceName ?>-prepare-reset-control"><?php echo $this->__('Productlist_Prepare_sResetValuesButton'); ?></a>
            <?php $aResetI18n = $this->__('Productlist_Prepare_aResetValues'); ?>
            <div id="ml-<?php echo $marketplaceName ?>-prepare-reset-content"
                title="<?php echo $this->__('Productlist_Prepare_sResetValuesButton'); ?>" class="ml-modal dialog2">
                <form action="<?php echo $this->getCurrentUrl() ?>" method="post">
                    <?php foreach (MLHttp::gi()->getNeededFormFields() as $sName => $sValue) { ?>
                        <input type="hidden" name="<?php echo $sName ?>" value="<?php echo $sValue ?>" />
                    <?php }
                    foreach ($this->getPreparationResetFields() as $filedName => $columnName) {
                        $label = $aResetI18n['checkboxes'][$filedName]['label'];
                        $tooltip = isset($aResetI18n['checkboxes'][$filedName]['tooltip']) ? $aResetI18n['checkboxes'][$filedName]['tooltip'] : '';
                        $fieldId = 'ml-' . $marketplaceName . '-prepare-reset-' . $filedName;
                        ?>
                        <div class="ml-prepare-reset-checkbox-container">
                            <div class="ml-prepare-reset-checkbox" id="<?php echo $fieldId ?>">
                                <label>
                                    <input name="<?php echo MLHttp::gi()->parseFormFieldName('view[]') ?>"
                                        value="<?php echo $filedName ?>" type="checkbox" />&nbsp;<?php echo $label ?></label>
                                <?php if ($tooltip) {
                                    MLView::addTooltipById($fieldId, $tooltip);
                                } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <input id="ml-<?php echo $marketplaceName ?>-prepare-reset-complete"
                        name="<?php echo MLHttp::gi()->parseFormFieldName('view') ?>" value="unprepare"
                        type="checkbox" />&nbsp;<label
                        for="ml-<?php echo $marketplaceName ?>-prepare-reset-complete"><?php echo $aResetI18n['checkboxes']['unprepare']['label']; ?></label><br />
                </form>
            </div>
            <script type="text/javascript">//<![CDATA[
                (function ($) {
                    jqml(document).ready(function () {
                        jqml("#ml-<?php echo $marketplaceName ?>-prepare-reset-complete").change(function () {
                            if (jqml(this).prop('checked')) {
                                jqml(this).parent().siblings().find('input[type="checkbox"]').not(jqml(this)).attr('disabled', 'disabled');
                            } else {
                                jqml(this).parent().siblings().find('input[type="checkbox"]').not(jqml(this)).removeAttr('disabled');
                            }
                        });
                        jqml("#ml-<?php echo $marketplaceName ?>-prepare-reset-control").click(function () {
                            var eModal = jqml("#ml-<?php echo $marketplaceName ?>-prepare-reset-content");
                            eModal.dialog({
                                modal: true,
                                width: '600px',
                                buttons: [
                                    {
                                        text: "<?php echo $aResetI18n['buttons']['abort']; ?>",
                                        click: function () {
                                            jqml(this).dialog("close");
                                            return false;
                                        }
                                    },
                                    {
                                        text: "<?php echo $aResetI18n['buttons']['ok']; ?>",
                                        click: function () {
                                            $.blockUI(blockUILoading);
                                            jqml(this).find('form')[0].submit();
                                            jqml(this).dialog("close");
                                            return false;
                                        }
                                    }
                                ]
                            });
                        });
                    });
                })(jqml);
                //]]></script>
        </div>
        <div class="ml-container-inner ml-container-sm">
            <a class="mlbtn-red action"
                href="<?php echo $this->getUrl(array('controller' => $this->getRequest('controller') . '_form')); ?>">
                <?php echo $this->__('ML_EBAY_LABEL_PREPARE') ?>
            </a>
        </div>
    </div>
    <div class="spacer"></div>
<?php } ?>