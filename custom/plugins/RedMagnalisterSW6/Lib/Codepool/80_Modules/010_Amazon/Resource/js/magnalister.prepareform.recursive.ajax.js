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

/**
 * Amazon Module Override for Prepare Form Recursive AJAX
 *
 * This override extends the base mlPrepareRecursiveAjax to integrate with
 * the React-based Amazon Variations component. Before submitting the form,
 * it ensures that React has saved all pending attribute changes via AJAX.
 */

var mlPrepareRecursiveAjax = null;
(function ($) {
    // Flag to prevent infinite loop when React save triggers form submit
    var isReactSaveCompleted = false;

    // Store active animation timers for cleanup
    var activeAnimationTimers = [];

    /**
     * Validate and scroll to first incomplete mandatory field
     * Checks for required fields marked with red bullet (•) in their label
     *
     * @param {jQuery} form - The form element to validate
     * @returns {Object|null} - Returns {field, label} if incomplete field found, null if all complete
     */
    function validateAndScrollToFirstIncompleteField(form) {
        var incompleteField = null;

        // Find all fields marked as required (have red bullet • in label)
        // Look for spans with class="bull" or text content "•" or red color
        form.find('label').each(function () {
            var $label = $(this);

            // Check if this label contains a required indicator (red bullet •)
            // Method 1: Check for class="bull"
            // Method 2: Check for span containing bullet character "•"
            // Method 3: Check for red color (inline style or computed)
            var $requiredIndicator = $label.find('span').filter(function () {
                var $span = $(this);

                // Method 1: Has class "bull"
                if ($span.hasClass('bull')) {
                    return true;
                }

                // Method 2: Contains bullet character
                if ($span.text().trim() === '•') {
                    return true;
                }

                // Method 3: Has red color (check computed style)
                var color = $span.css('color');
                if (color && (
                    color.indexOf('227, 26, 28') > -1 ||
                    color.indexOf('#e31a1c') > -1
                )) {
                    return true;
                }

                return false;
            });

            // If this is a required field
            if ($requiredIndicator.length > 0) {
                // Get the associated input/select/textarea
                var fieldId = $label.attr('for');
                var $field = fieldId ? $('#' + fieldId) : $label.closest('tr, .form-group, .field-wrapper').find('input, select, textarea').first();

                if ($field.length > 0) {
                    // Skip fields that are hidden or have hidden parent
                    // Check: field has hidden class OR parent has hidden class OR field is not visible
                    if ($field.hasClass('hidden') || $field.closest('.hidden').length > 0 || !$field.is(':visible')) {
                        return true; // Continue to next iteration
                    }

                    var fieldValue = $field.val();
                    var isEmpty = !fieldValue || fieldValue === '' || fieldValue === 'null' || fieldValue === '0';

                    // Special handling for select elements
                    if ($field.is('select')) {
                        var selectedOption = $field.find('option:selected');
                        var selectedValue = selectedOption.val();

                        // Consider empty if:
                        // - No option selected
                        // - Selected value is empty, 'null', or '0'
                        isEmpty = !selectedOption.length ||
                            !selectedValue ||
                            selectedValue === '' ||
                            selectedValue === 'null' ||
                            selectedValue === '0';
                    }

                    // If field is empty and we haven't found an incomplete field yet
                    if (isEmpty && !incompleteField) {
                        incompleteField = {
                            field: $field,
                            label: $label.text().trim()
                        };
                        return false; // Break the loop
                    }
                }
            }
        });

        // If we found an incomplete field, scroll to it
        if (incompleteField) {
            console.log('[Amazon Prepare Form] Found incomplete required field:', incompleteField.label);

            // Scroll to the field (or its row for better visibility)
            var $scrollTarget = incompleteField.field.closest('tr').length > 0
                ? incompleteField.field.closest('tr')
                : incompleteField.field;

            $scrollTarget[0].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Add visual feedback (red border flash)
            // For select2, target the visible wrapper instead of hidden select
            var $fieldElement = incompleteField.field;
            if ($fieldElement.is('select') && $fieldElement.hasClass('select2-hidden-accessible')) {
                // Find select2 wrapper (next sibling span.select2)
                var $select2Wrapper = $fieldElement.next('.select2-container');
                if ($select2Wrapper.length > 0) {
                    $fieldElement = $select2Wrapper.find('.select2-selection');
                }
            }

            var originalBorder = $fieldElement.css('border');

            // Clear any existing animation timers to prevent conflicts
            activeAnimationTimers.forEach(function (timerId) {
                clearTimeout(timerId);
            });
            activeAnimationTimers = [];

            // Flash red border 3 times using optimized approach
            // Animation sequence: Red -> Gray -> Red -> Gray -> Red (FINAL - stays red)
            var currentFlash = 0;
            var totalFlashes = 3;

            function animateFlash() {
                if (currentFlash >= totalFlashes) {
                    // Final state: keep red border
                    $fieldElement.css({
                        'border': '2px solid #e31a1c',
                        'box-shadow': '0 0 5px rgba(227, 26, 28, 0.5)'
                    });
                    return;
                }

                // Red flash
                $fieldElement.css({
                    'border': '2px solid #e31a1c',
                    'box-shadow': '0 0 5px rgba(227, 26, 28, 0.5)'
                });

                // Schedule gray flash
                var grayTimer = setTimeout(function () {
                    $fieldElement.css({
                        'border': originalBorder,
                        'box-shadow': 'none'
                    });

                    // Schedule next red flash
                    var redTimer = setTimeout(function () {
                        currentFlash++;
                        animateFlash();
                    }, 200);

                    activeAnimationTimers.push(redTimer);
                }, 200);

                activeAnimationTimers.push(grayTimer);
            }

            animateFlash();

            // ❌ REMOVED: Separate setTimeout for final color
            //
            // Problem Observed: Animation didn't work consistently - border stayed gray
            //
            // Possible Root Causes (unverified):
            // - setTimeout race condition: Timer at 1200ms might execute after 1600ms
            // - Event loop timing: Browser might batch/reorder style updates
            // - jQuery .css() conflicts with multiple simultaneous updates
            //
            // Current Solution (working):
            // - Make final color (red) part of flashTimes array
            // - Last element in array is the final color
            // - No separate timer = More reliable behavior
            // - Border stays red after animation completes
            //
            // See AmazonValueSelector.tsx for detailed discussion of this pattern

            return incompleteField;
        }

        return null;
    }

    /**
     * Handle React component save before form submission
     * Reusable function to avoid code duplication
     *
     * @param {Function} onComplete - Callback to execute after React save completes
     */
    function handleReactSaveBeforeSubmit(onComplete) {
        if (!isReactSaveCompleted) {
            console.log('[Amazon Prepare Form] React component detected, triggering save before form submission...');

            window.magnalisterSaveAmazonVariations(function onReactSaveComplete() {
                console.log('[Amazon Prepare Form] React save completed, proceeding with form submission...');

                // Mark that React save is done and execute callback
                isReactSaveCompleted = true;
                onComplete();

                // Reset flag immediately after callback (no setTimeout needed)
                isReactSaveCompleted = false;
            });

            return true; // Indicates we're waiting for React save
        }

        return false; // No React save needed
    }

    mlPrepareRecursiveAjax = {
        triggerPrepareRecursiveAjax: function (form, aExtraData) {
            //aExtraData = ml[action][prepareaction]:...
            // check if the form is product prepare form to trigger recursive ajax
            var hasReactComponent = typeof window.magnalisterSaveAmazonVariations === 'function';
            if (
                form.attr('id').endsWith('_prepare_apply_form')
                || form.attr('id').endsWith('_prepare_form')
            ) {
                // Step 1: Validate mandatory fields BEFORE React save or form submit
                // This catches fields like Main Category, variation_theme, browsenode, etc.
                var incompleteField = validateAndScrollToFirstIncompleteField(form);
                if (incompleteField) {
                    console.log('[Amazon Prepare Form] Incomplete required field found:', incompleteField.label);
                    console.log('[Amazon Prepare Form] Please complete this field before submitting.');
                    // Return true to prevent default form submission
                    return true;
                }

                // Step 2: Amazon-specific: Check if React component needs to save
                if (hasReactComponent) {
                    var needsReactSave = handleReactSaveBeforeSubmit(function () {
                        // Trigger form submit again after React save completes
                        mlSerializer.submitSerializedForm(form, aExtraData);
                    });

                    if (needsReactSave) {
                        // Return true to prevent normal submit while React save is in progress
                        return true;
                    }
                }

                // Original logic from base class
                var currentA = form,
                    ajaxData = $("#magnalister_recursive_ajax_data"),
                    redirect = true;
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
                } catch (e) {
                    console.log(e);
                    $('.magnalisterForm').attr('title', ajaxData.data('stitle'));
                }

                currentA.magnalisterRecursiveAjax({
                    sOffset: ajaxData.data('offset'),
                    sAddParam: ajaxData.data('ajax') + '=true',
                    aAddParam: [
                        "mlSerialize"
                    ],
                    oI18n: {
                        sProcess: ajaxData.data('sprocess'),
                        sError: ajaxData.data('serror'),
                        sSuccess: ajaxData.data('ssuccess')
                    },
                    onFinalize: function (blError) {
                        if (redirect) {
                            window.location = ajaxData.data('redirect');
                        } else {
                            window.location = window.location;
                        }
                    },
                    onResponse: function (requestData) {
                        redirect = JSON.parse(requestData).info.redirect;
                    },
                    onProgessBarClick: function (data) {
                        console.dir({data: data});
                    },
                    blDebug: ajaxData.data('bldebug'),
                    sDebugLoopParam: ajaxData.data('saveselection') + '=true'
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
            } else if (
                form.attr('id').endsWith('_prepare_variations')
            ) {
                // Variations matching form: save React state before resetting form
                if (hasReactComponent) {
                    var needsReactSave = handleReactSaveBeforeSubmit(function () {
                        return true;
                    });

                }

            } else {
                return false
            }

        },
    }
})(jqml);
