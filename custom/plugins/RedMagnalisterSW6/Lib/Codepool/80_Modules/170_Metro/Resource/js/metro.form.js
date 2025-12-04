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

(function ($) {
    $(document).ready(function () {
        let $shipping_destination = jqml('#metro_config_country_field_shippingdestination');
        let $shipping_origin = jqml('#metro_config_country_field_shippingorigin');
        let cross_border_combinations = $shipping_destination.data('originDestinationCombinations');

        if ($shipping_destination.length && $shipping_origin.length && cross_border_combinations) {
            $shipping_origin.on('change', function () {
                let origin_key = $shipping_origin.val().replace(/_MAIN$/, '');

                jqml('> option[value]', $shipping_destination).each(function () {
                    let $this = jqml(this);
                    let idx = cross_border_combinations[origin_key].indexOf(
                        $this.val().replace(/_MAIN$/, ''));
                    if (-1 !== idx) {
                        $this.removeAttr('disabled');
                    } else {
                        $this.attr('disabled', 'disabled');
                    }
                });

                if (jqml('> option:selected', $shipping_destination).attr('disabled')) {
                    $shipping_destination.val($shipping_origin.val());
                }
            });
        }

        // Volume Prices
        var dropdownEnableVolumePrice = $('*[name="ml[field][volumepricesenable]"]'),
            dropdownEnableVolumeWebShopOptionCustomerGroup = $('*[name="ml[field][volumepriceswebshopcustomergroup]"]'),
            dropdownEnableVolumeWebShopOptionPriceOptions = $('*[name="ml[field][volumepriceswebshoppriceoptionsaddkind]"]'),
            priceOptions = $('*[name^="ml[field][volumepriceprice"][name$="addkind]"]');

        // Hide Use Webshop Customer Group dropdown
        if (dropdownEnableVolumePrice.val() !== 'webshop') {
            dropdownEnableVolumeWebShopOptionCustomerGroup.closest('tr').hide();
            dropdownEnableVolumeWebShopOptionPriceOptions.closest('tr').hide();
        }

        if (dropdownEnableVolumePrice.val() === 'dontuse' || dropdownEnableVolumePrice.val() === 'webshop') {
            priceOptions.attr("disabled", "disabled");
        }

        dropdownEnableVolumePrice.on('change', function () {
            // Hide/Show Webshop Customer Group dropdown
            if (this.value !== 'webshop') {
                $('*[name="ml[field][volumepriceswebshopcustomergroup]"]').closest('tr').hide();
                $('*[name="ml[field][volumepriceswebshoppriceoptionsaddkind]"]').closest('tr').hide();
            } else {
                $('*[name="ml[field][volumepriceswebshopcustomergroup]"]').closest('tr').show();
                $('*[name="ml[field][volumepriceswebshoppriceoptionsaddkind]"]').closest('tr').show();
            }

            // disable all dropdowns below if enable volume price is 'dontuse' or 'webshop'
            if (this.value === 'dontuse' || this.value === 'webshop') {
                $('*[name^="ml[field][volumepriceprice"]').attr("disabled", "disabled").closest('tr').hide();
            } else {
                $('*[name^="ml[field][volumepriceprice"]').removeAttr("disabled").closest('tr').show();
            }
        });
        dropdownEnableVolumePrice.change();
        function fieldAddFunction(field) {
            // update view on change
            field.on('change', function() {
                console.log($(this).val());
                $(this).closest('td').children("span:not(:first)").hide();
                if ($(this).val() === 'dontuse' || $(this).val() === 'customergroup') {
                    if ($(this).val() === 'customergroup') {
                        let timeBack = 3; //7 for option A and B not for 1,2,3,4,5
                        if (    $(this).attr("id") == "metro_config_priceandstock_field_volumepricepriceaaddkind"
                            || $(this).attr("id") == "metro_config_priceandstock_field_volumepricepricebaddkind"
                        ) {
                            timeBack = 7;
                        }
                        prevSpan($(this).closest('td').children("span:last"), timeBack).nextAll().show();
                    }
                } else {
                    prevSpan($(this).closest('td').children("span:last"), 2).prevAll().show();
                }
            });
            field.change();
        }

        priceOptions.each(function() {
            fieldAddFunction($(this));
        });

        function prevSpan(element, steps) {
            for (var i = 0; i < steps; i++) {
                element = element.prev('span');
            }
            return element;
        }
    });
})(jqml);
