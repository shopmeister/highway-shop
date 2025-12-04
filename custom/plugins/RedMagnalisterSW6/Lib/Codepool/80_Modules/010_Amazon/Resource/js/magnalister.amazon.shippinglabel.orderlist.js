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

(function($) {
    jqml(document).ready(function() {
        // Tooltip text for tabs from global variable (translated)
        var tooltipText = window.mlAmazonShippingLabelTooltip || 'Falls hier bereits Bestellungen vorliegen, wählen Sie eine aus und klicken Sie dann auf den Button «Weiter zu "Angaben zu den Paketen"».';

        // Function to update the inactive class
        function updateButtonState() {
            var checkedCount = $('input.js-mlFilter-activeRowCheckBox[type="checkbox"]:checked').length;
            var actionButton = $('.ml-container-action a.mlbtn-red.action');

            // Tab links for the three tabs concerned - try multiple selectors
            var tabLinks = $('a[href*="shippinglabel_upload_form"]:not(.mlbtn), ' +
                           'a[href*="shippinglabel_upload_shippingmethod"]:not(.mlbtn), ' +
                           'a[href*="shippinglabel_upload_summary"]:not(.mlbtn)');

            // Fallback: Search in li.tab
            if (tabLinks.length === 0) {
                tabLinks = $('li a[href*="shippinglabel_upload"]').not('.mlbtn');
            }

            if (checkedCount > 0) {
                actionButton.removeClass('inactive');
                actionButton.css('pointer-events', 'auto');
                actionButton.css('cursor', '');

                // Remove special tooltip from tabs and allow clicks
                tabLinks.each(function() {
                    var $link = $(this);
                    var originalTitle = $link.data('original-title');
                    if (originalTitle !== undefined) {
                        $link.attr('title', originalTitle);
                        $link.removeData('original-title');
                    }
                    // Remove pointer-events: none so that tabs are clickable
                    $link.css('pointer-events', 'auto');
                    $link.removeAttr('style'); // Remove inline style completely
                });
            } else {
                if (!actionButton.hasClass('inactive')) {
                    actionButton.addClass('inactive');
                }
                // IMPORTANT: Set pointer-events: auto for tooltip to work
                actionButton.css('pointer-events', 'auto');
                actionButton.css('cursor', 'not-allowed');

                // Set special tooltip for tabs, BUT remove pointer-events: none
                tabLinks.each(function() {
                    var $link = $(this);
                    // Save original titles if not already saved
                    if ($link.data('original-title') === undefined) {
                        $link.data('original-title', $link.attr('title') || '');
                    }
                    $link.attr('title', tooltipText);
                    // IMPORTANT: Remove pointer-events: none so that tooltips work
                    $link.css('pointer-events', 'auto');
                    $link.css('cursor', 'not-allowed');
                });
            }
        }

        // Event listener to block clicks on inactive buttons
        $(document).on('click', '.ml-container-action a.mlbtn-red.action.inactive', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        // Event listener to block clicks on inactive tab links
        $(document).on('click', 'a[href*="shippinglabel_upload_form"], a[href*="shippinglabel_upload_shippingmethod"], a[href*="shippinglabel_upload_summary"]', function(e) {
            var checkedCount = $('input.js-mlFilter-activeRowCheckBox[type="checkbox"]:checked').length;

            // Only block if no checkbox is active and it is not a button
            if (checkedCount === 0 && !$(this).hasClass('mlbtn')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Event listener for checkbox changes
        $(document).on('change', 'input.js-mlFilter-activeRowCheckBox[type="checkbox"]', function() {
            updateButtonState();
        });

        // Hover event for tab links (as additional security measure)
        $(document).on('mouseenter', 'li.inactive a, li.tab a', function() {
            var $link = $(this);
            var href = $link.attr('href') || '';

            // Check whether it is one of the three tabs affected
            if (href.indexOf('shippinglabel_upload_form') > -1 ||
                href.indexOf('shippinglabel_upload_shippingmethod') > -1 ||
                href.indexOf('shippinglabel_upload_summary') > -1) {

                var checkedCount = $('input.js-mlFilter-activeRowCheckBox[type="checkbox"]:checked').length;

                if (checkedCount === 0) {
                    // Save original titles if not already saved
                    if ($link.data('original-title') === undefined) {
                        $link.data('original-title', $link.attr('title') || '');
                    }
                    $link.attr('title', tooltipText);
                }
            }
        });

        // Check initial when loading the page
        updateButtonState();
    });
})(jqml);