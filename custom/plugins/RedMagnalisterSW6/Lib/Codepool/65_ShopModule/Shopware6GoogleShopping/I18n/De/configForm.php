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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLI18n::gi()->set('googleshopping_config_orderimport_automatic_method', '-- Automatisch zuordnen --', true);
MLI18n::gi()->add('googleshopping_config_orderimport', array(
     'field' => array(
        'orderimport.paymentmethod' => array(
            'label' => '{#i18n:formfields_orderimport.paymentmethod_label#}',
            'help' => '{#i18n:shopware6_configuration_paymentmethod_help#}',
            'hint' => '',
        ),
        'orderimport.shippingmethod' => array(
            'label' => '{#i18n:formfields_orderimport.shippingmethod_label#}',
            'help' => '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}',
            'hint' => '',
        ),
        'orderimport.paymentstatus' => array(
            'label' => 'Zahlstatus im Shop',
            'hint' => '',
        ),
    ),
), false);

MLI18n::gi()->{'googleshopping_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';

MLI18n::gi()->add('googleshopping_prepare_apply_form', array(
    'field' => array(
        'variationgroups' => array(
            'label' => 'Choose category'
        )
    )
));
