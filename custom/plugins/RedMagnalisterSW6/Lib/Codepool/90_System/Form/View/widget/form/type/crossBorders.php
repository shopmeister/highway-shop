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
 * @var array{
 *     id:string,
 *     clientKey:string,
 *     crossBorders:array
 * } $aField
 */
?>
<script type="application/javascript">
    (function ($) {
        $(document).ready(function () {
            let clientKey = '<?php echo $aField['clientKey']; ?>';
            let $shippingOrigin = $('#<?php echo $aField['id']; ?>');
            let cross_borders = <?php echo json_encode($aField['crossBorders']); ?>;
            $shippingOrigin.data('oldValue', $shippingOrigin.val());
            $shippingOrigin.on('change', function () {
                if ($shippingOrigin.data('oldValue') === $shippingOrigin.val()) {
                    return;
                }

                let message = '';
                let cross_borders_key = clientKey + '|' + $shippingOrigin.val();
                if (clientKey + '|' + $shippingOrigin.data('oldValue') in cross_borders
                    && !(cross_borders_key in cross_borders)
                ) {
                    message = '<?php echo addslashes(MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_ACTIVATE_STOCK_SYNC_INFO')); ?>';
                } else if (cross_borders_key in cross_borders) {
                    message = '<?php echo addslashes(MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_DEACTIVATE_STOCK_SYNC_INFO')); ?>';
                    message = message.replaceAll(/\{#TAB_LINK#}/g, cross_borders[cross_borders_key]['tab_link']);
                    message = message.replaceAll(/\{#TAB_LABEL#}/g, cross_borders[cross_borders_key]['tab_label']);
                }

                if (!message) {
                    return;
                }

                $('<div></div>').html(message).jDialog({
                    title: '<?php echo addslashes(MLI18n::gi()->get('ML_METRO_CROSS_BORDERS_LIMITATION_TITLE')); ?>',
                    buttons: {
                        '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_ABORT')); ?>': function() {
                            $shippingOrigin.val($shippingOrigin.data('oldValue'));
                            $(this).dialog('close');
                        },
                        '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_OK')); ?>': function() {
                            let $button = $('button[name="ml[action][saveaction]"]').last();
                            window.setTimeout(function () {
                                $button.click();
                            }, 10);

                            $(this).dialog('close');
                        }
                    }
                });
            });
        });
    })(jqml);
</script>
