<?php
/**
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

/**
 * POST Button Template
 *
 * Renders a button that submits via AJAX POST instead of GET
 * This prevents URL changes and avoids nested form issues
 *
 * Expected $aField structure:
 * array(
 *     'name' => 'fieldname',
 *     'action' => '/path/to/action',  // AJAX POST URL
 *     'params' => array(              // Parameters to send via POST
 *         'key1' => 'value1',
 *         'key2' => array('nested' => 'value')
 *     ),
 *     'i18n' => array(
 *         'buttontext' => 'Button Label'
 *     ),
 *     'disabled' => false // Optional
 * )
 */

if (!class_exists('ML', false))
    throw new Exception();

// Generate unique button ID
$buttonId = 'ml-postbutton-' . md5($aField['name'] . microtime());

// Get action URL and parameters
$actionUrl = !empty($aField['action']) ? $aField['action'] : '';
$params = !empty($aField['params']) ? $aField['params'] : array();

// Convert params to JSON for JavaScript
$paramsJson = json_encode($params);
?>

<!-- Button that triggers AJAX POST -->
<a class="mlbtn abutton js-field" id="<?php echo $buttonId; ?>"
    href="#"
    <?php echo ((isset($aField['disabled']) && $aField['disabled']) ? ' disabled="disabled"' : ''); ?>>
    <?php echo $aField['i18n']['buttontext']; ?>
</a>

<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $('#<?php echo $buttonId; ?>').on('click', function (e) {
                e.preventDefault();

                var $button = $(this);
                var actionUrl = '<?php echo $actionUrl; ?>';
                var params = <?php echo $paramsJson; ?>;
                console.log(params);
                // Use jQuery $.ajax with traditional serialization (jQuery 1.8+ compatible)
                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: params,
                    traditional: true,  // Important for nested arrays in jQuery 1.8
                    success: function () {
                        // Reload page to show updated state
                        window.location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('POST request failed:', error);
                        alert('An error occurred. Please try again.');
                    }
                });

                return false;
            });
        });
    })(jqml);
</script>