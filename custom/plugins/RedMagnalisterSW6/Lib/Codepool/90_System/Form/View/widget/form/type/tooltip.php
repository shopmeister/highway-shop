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
 *     id: string,
 *     i18n: array{
 *         tooltip: string
 *     }
 * } $aField
 */
?>
<script>
    (function ($) {
        $(document).ready(function () {
            let $tooltipField = $('#<?php echo $aField['id']; ?>');
            $tooltipField.on('mouseover', function () {
                $tooltipField.parent().css('position', 'relative');
                // Create tooltip
                let $tooltip = $('<div class="ml-tooltip"><div>' +
                    '<div class="ml-tooltip-arrow"></div>' +
                    '<div class="ml-tooltip-content">' +
                    '<?php echo str_replace("'", "\\'", $aField['i18n']['tooltip']); ?>' +
                    '</div></div></div>');
                
                // Insert the tooltip directly after the field element
                $tooltipField.after($tooltip);
            });
            $tooltipField.on('mouseout', function () {
                $tooltipField.next('.ml-tooltip').remove();
            });
        });
    })(jqml);
</script>
