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

if (!class_exists('ML', false))
    throw new Exception();
    $aField['value'] = (isset($aField['value']) && is_array($aField['value'])) ? $aField['value'] : array();

    if (!isset($aField['cssclass'])) {
        $aField['cssclass'] = array();
    }
$aField['cssclass'][] = 'multi-select';

    if (!empty($aField['classes']) && is_array($aField['classes'])) {
        // in most cases it's a string so convert to array for merging below
        if (is_string($aField['cssclass'])) {
            $aField['cssclass'] = array($aField['cssclass']);
        }
        $aField['cssclass'] = array_merge($aField['cssclass'], $aField['classes']);
    }

$aField['multiple'] = true;
$aField['type'] = 'select';
$this->includeType($aField);

if (isset($aField['limit'])) { ?>
    <script type="text/javascript">
        (function ($) {
            $(document).ready(function () {
                var last_valid_selection = null;
                $('select[name="<?php echo MLHTTP::gi()->parseFormFieldName($aField['name']);?>[]"]').change(function (event) {
                    var selectValue = $(this).val();
                    if (typeof (selectValue) != "undefined" && selectValue !== null &&
                        selectValue.length > <?php echo $aField['limit']?>) {
                    $(this).val(last_valid_selection);
                } else {
                    last_valid_selection = $(this).val();
                }
            });
        });
    })(jqml);
</script>
<?php
}
?>
<script type="text/javascript">
    (function ($) {
        /**
         * Auto-adjusts the height of multiple select elements based on their content
         * @param {number} maxHeight - Maximum height in pixels
         */
        $.fn.autoAdjustHeight = function (maxHeight = 200) {
            return this.each(function () {
                if (!$(this).is('select[multiple]')) {
                    return;
                }
                const $select = $(this);
                const optionCount = $select.find('option').length;

                if (optionCount === 0) {
                    $select.height(0);
                    return;
                }

                // Create a clone to measure option height
                const $clone = $select.clone()
                    .css({
                        visibility: 'hidden',
                        position: 'absolute',
                        height: 'auto'
                    })
                    .attr('size', 1)
                    .appendTo('body');

                // Get the height of a single option
                const singleOptionHeight = $clone.height();

                // Remove the clone
                $clone.remove();

                // Calculate total height needed with padding
                const padding = 4;
                const calculatedHeight = Math.min((optionCount * singleOptionHeight) + padding, maxHeight);

                // Set the height
                $select.height(calculatedHeight);
            });
        };

        $(document).ready(function () {
            // Apply to all multiple selects

            try{

            $('select[name="<?php echo MLHTTP::gi()->parseFormFieldName($aField['name']); ?>[]"]').autoAdjustHeight(); // Set max height to 250px
            }catch (e) {
                console.log('test69');
                console.log(e);
            }

            // Or with event handling:
            $(document).on('change', 'select[multiple]', function () {
                try{
                $(this).autoAdjustHeight();
                }catch (e) {
                    console.log('test79');
                    console.log(79, e);
                }
            }).trigger('change');
        });

    })(jqml);
</script>