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

if (!class_exists('ML', false)) {
    throw new Exception();
}
?>
<script type="text/javascript">/*<![CDATA[*/
(function($) {
    $(document).ready(function() {
        // Handle dismiss button click for Etsy processing profile update hint
        // Hook into the standard close-message button
        $(".magna .noticeBox .close-message").on("click", function(e) {
            var $messageContainer = $(this).closest("div.noticeBox");

            // Check if this is the processing profile hint by looking for the unique MD5
            var messageText = $messageContainer.text();

            if (messageText.indexOf('Verarbeitungsprofile') > -1 ||
                messageText.indexOf('Processing Profiles') > -1 ||
                messageText.indexOf('Perfiles de Procesamiento') > -1 ||
                messageText.indexOf('Profils de Traitement') > -1) {

                // Make AJAX call to save dismiss state
                $.ajax({
                    url: '<?php echo MLHttp::gi()->getCurrentUrl(array('action' => 'dismissProcessingProfileHint', 'kind' => 'ajax')) ?>',
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            console.error('Failed to dismiss hint:', response.error);
                        }
                    },
                    error: function() {
                        console.error('AJAX request failed');
                    }
                });
            }
        });
    });
})(jqml);
/*]]>*/</script>