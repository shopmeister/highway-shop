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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

if (!class_exists('ML', false))
    throw new Exception();

/**
 * This is the function for initializing the marketplace optional attribute drop down
 * Available variables:
 *   @var array $optionalAttributesIdAndMapValues array of values containing multiple arrays of key pair values that bwe show in the dropdown
 *   @var integer $sizeOfOptionalAttributes total size of optional attributes
 *   @var integer $optionalAttributesMaxSize max size of optional attributes
 *   @var string $marketplaceName name of the marketplace
 */
?>
<script type="text/javascript">
    (function($) {
        $(document).ready(function() {
            // Optional attributes matching JS logic
            function initOptionalAttributesSelector() {
                var optionalAttributesIdAndMapValues = <?php echo defined('JSON_INVALID_UTF8_SUBSTITUTE')? json_encode($optionalAttributesIdAndMapValues,JSON_INVALID_UTF8_SUBSTITUTE):json_encode($optionalAttributesIdAndMapValues)?>;
                jqml.each(optionalAttributesIdAndMapValues, function (optionalFieldsetElId, optionalAttributesMap) {
                    var optionalFieldsetEl = $('#'+optionalFieldsetElId),
                        optionalAttributesMaxSize = <?php echo $optionalAttributesMaxSize ?>,
                        spacerFieldEl = optionalFieldsetEl.find('.spacer').last(),
                        sizeOfOptionalAttributes = <?php echo $sizeOfOptionalAttributes ?>,
                        currentlySelectedAttribute = null,
                        attributesSelectorEl = null,
                        attributesSelectorOptionsTpl = ['<option value="dont_use"><?php echo addslashes(MLI18n::gi()->get('ML_LABEL_DONT_USE')) ?></option>'];

                    for (var fieldId in optionalAttributesMap) {
                        if (optionalAttributesMap.hasOwnProperty(fieldId)) {
                            attributesSelectorOptionsTpl.push(
                                '<option value="' + fieldId + '">' + optionalAttributesMap[fieldId] + '</option>'
                            );
                        }
                    }

                    // If there is no optional attributes quit
                    if (attributesSelectorOptionsTpl.length === 1) {
                        return;
                    }

                    if (sizeOfOptionalAttributes > optionalAttributesMaxSize) {
                        $([
                            '<tr class="js-field hide optionalAttribute dont_use_sub">',
                            '<th><label for="dont_use_sub"></label></th>',
                            '<td class="mlhelp ml-js-noBlockUi"></td>',
                            '<td class="input"></td>',
                            '<td class="info"></td>',
                            '</tr>'
                        ].join('')).insertBefore(spacerFieldEl);
                    }

                    attributesSelectorEl = jqml([
                        '<select class="ml-searchable-select" name="optional_selector" style="width: 100%">',
                        attributesSelectorOptionsTpl.join(''),
                        '</select>'
                    ].join(''));

                    // sort items alphabetic
                    // Remove 'do_not_use' option from options before sorting
                    let doNotUseOption = attributesSelectorEl.find('option[value="dont_use"]').detach();
                    attributesSelectorEl.html(attributesSelectorEl.find('option').sort(function (x, y) {
                        return $(x).text().toLowerCase() > $(y).text().toLowerCase() ? 1 : -1;
                    }));
                    // Prepend 'do_not_use' option back after sorting is done
                    attributesSelectorEl.prepend(doNotUseOption);
                    //select "dont_use" as default
                    attributesSelectorEl.val('dont_use');

                    function showConfirmationDialog(attributeIdToShow) {
                        var d = '<?php echo addslashes(MLI18n::gi()->get($marketplaceName . '_prepare_variations_reset_info')) ?>';
                        $('<div class="ml-modal dialog2" title="<?php echo addslashes(MLI18n::gi()->get('ML_LABEL_INFO')) ?>"></div>').html(d).jDialog({
                            width: (d.length > 1000) ? '700px' : '500px',
                            buttons: {
                                Cancel: {
                                    'text': '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_ABORT')); ?>',
                                    click: function () {
                                        // Reset attribute selector to previous value silently
                                        attributesSelectorEl.val(currentlySelectedAttribute);
                                        $(this).dialog('close');
                                    }
                                },
                                Ok: {
                                    'text': '<?php echo addslashes(MLI18n::gi()->get('ML_BUTTON_LABEL_OK')); ?>',
                                    click: function () {
                                        $('#' + currentlySelectedAttribute).val('').change();
                                        changeCurrentAttribute(attributeIdToShow);
                                        $(this).dialog('close');
                                    }
                                }
                            }
                        });
                    }

                    function attributeSelectorOnChange() {
                        if (currentlySelectedAttribute) {
                            var attributeValue = $('#' + currentlySelectedAttribute).val();
                            if (attributeValue) {
                                showConfirmationDialog($(this).val());
                                return;
                            }
                        }

                        changeCurrentAttribute($(this).val());
                    }

                    function changeCurrentAttribute(attributeIdToShow) {

                        if (jqml('#' + attributeIdToShow).hasClass("select2-hidden-accessible")) {
                            jqml('#' + attributeIdToShow).select2('destroy');
                        }

                        if (attributesSelectorEl.hasClass("select2-hidden-accessible")) {
                            attributesSelectorEl.select2('destroy');
                        }

                        if (sizeOfOptionalAttributes > optionalAttributesMaxSize) {
                            optionalFieldsetEl.find('.optionalAttribute').addClass('hide').hide();
                        }

                        currentlySelectedAttribute = attributeIdToShow;

                        var attributeFieldEl = optionalFieldsetEl.find('.' + currentlySelectedAttribute + '_sub'),
                            currentAttributeLabelEl = attributeFieldEl.find('label[for="' + currentlySelectedAttribute + '_sub"]').hide();

                        attributesSelectorEl.insertBefore(currentAttributeLabelEl);
                        attributeFieldEl.remove().removeClass('hide').show().insertBefore(spacerFieldEl);
                        initAttributeAjaxForm(attributeFieldEl, true);

                        attributesSelectorEl.select2({
                            width: 'resolve'
                        });

                        attributesSelectorEl.on('select2:select select2:unselecting', attributeSelectorOnChange);

                        jqml('#' + attributeIdToShow).select2({
                            width: 'resolve'
                        });
                        jqml('#' + attributeIdToShow).on('select2:open', function (e) {
                            if (this.options.length === 1) {
                                var name = jqml(this).attr('name'),
                                    mpDataType = jqml('input[name="' + name.replace('[Code]', '[DataType]') + '"]').val(),
                                    span = jqml(this).closest("span"),
                                    select = jqml('select[name="' + name + '"]');

                                span.css("width", "81%");
                                jqml(this).find('option').remove().end();
                                renderOptions(jqml(this));
                                jqml(this).trigger('input');

                                if (mpDataType) {
                                    mpDataType = mpDataType.toLowerCase();
                                    isSelectAndText = mpDataType === 'selectandtext';
                                }

                                select.find('option[value^=separator]').attr('disabled', 'disabled');

                                if (['select', 'multiselect'].indexOf(mpDataType) != -1) {
                                    select.find("option[data-type='text']").attr('disabled', 'disabled');
                                    select.find('option[value=freetext]').attr('disabled', 'disabled');
                                }

                                if ('text' == mpDataType) {
                                    select.find('option[value=attribute_value]').attr('disabled', 'disabled');
                                }


                            }
                        });
                    }

                    attributesSelectorEl.change(attributeSelectorOnChange).change();

                    jqml('select[name="optional_selector"]').each(function (index, link) {
                        if (!jqml(this).hasClass("select2-hidden-accessible")) {
                            jqml(this).select2({
                                width: 'resolve'
                            });
                        }
                    });
                });
            }
            initOptionalAttributesSelector();
        });
    })(jqml);
</script>
