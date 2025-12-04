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

MLSetting::gi()->add('formfields_obi', array(
    'vat' => array(
        'name' => 'vat',
        'type' => 'matching',
        'required' => true
    ),
    'lang' => array(
        'i18n' => '{#i18n:formfields_obi__lang#}',
        'name' => 'lang',
        'type' => 'select',
        'default' => '',
    ),
    'delivery' => array(
        'i18n' => '{#i18n:formfields_obi__delivery#}',
        'name' => 'delivery',
        'type' => 'subFieldsContainer',
        'subfields' => array(
            'deliverytype' => array(
                'i18n' => '{#i18n:formfields_obi__deliverytype#}',
                'name' => 'deliverytype',
                'type' => 'select'
            ),
            'deliverytime' => array(
                'i18n' => '{#i18n:formfields_obi__deliverytime#}',
                'name' => 'deliverytime',
                'type' => 'select'
            ),
        ),
    ),
    'deliverytime' => array(
        'i18n' => '{#i18n:formfields_obi__deliverytime#}',
        'name' => 'deliverytime',
        'type' => 'subFieldsContainer',
        'subfields' => array(
            'shop' => array(
                'i18n' => array('label' => 'Attributsmatching'),
                'name' => 'deliverytime',
                'type' => 'am_attributesselect',
                'values' => array()
            ),
            'default' => array(
                'i18n' => '{#i18n:formfields_obi__deliverytime_default#}',
                'name' => 'deliverytime_default',
                'type' => 'select',
                'values' => array(),
                'default' => '3'
            ),
        ),
    ),
    'mwst' => array(
        'i18n' => '{#i18n:formfields_obi__mwst#}',
        'name' => 'mwst',
        'type' => 'string',
    ),
    'shippingtype' => array(
        'i18n' => '{#i18n:formfields_obi__shippingtype#}',
        'name' => 'shippingtype',
        'type' => 'select',
    ),
    'orderstatus.open' => array(
        'i18n' => '{#i18n:formfields_obi__orderstatus.open#}',
        'name' => 'orderstatus.open',
        'type' => 'select',
    ),
    'orderstatus.carrier' => array(
        'i18n' => '{#i18n:formfields_obi__orderstatus.standardshipping#}',
        'name' => 'orderstatus.sendcarrier',
        'type' => 'selectwithtmatchingoption',
        'subfields' => array(
            'select' => '{#setting:formfields_obi__orderstatus.sendcarrier.select#}',
            'matching' => '{#setting:formfields_obi__orderstatus.sendcarrier.duplicate#}'
        ),
    ),
    'orderstatus.sendcarrier.select' => array(
        'i18n' => array('label' => '',),
        'name' => 'orderstatus.sendcarrier.select',
        'required' => true,
        'matching' => 'sendCarrierMatching', //must be the same as value defined in ConfigData key value for matching
        'type' => 'am_attributesselect'
    ),
    'orderimport.paymentmethod' => array(
        'i18n'      => '{#i18n:formfields__orderimport.paymentmethod#}',
        'name'      => 'orderimport.paymentmethod',
        'type'      => 'selectwithtextoption',
        'subfields' => array(
            'select' => array('name' => 'orderimport.paymentmethod', 'type' => 'select'),
            'string' => array('name' => 'orderimport.paymentmethod.name', 'type' => 'string', 'default' => 'obi',)
        ),
        'expert'    => true,
    ),
    'orderimport.shippingmethod' => array(
        'i18n'      => '{#i18n:formfields__orderimport.shippingmethod#}',
        'name'      => 'orderimport.shippingmethod',
        'type'      => 'selectwithtextoption',
        'subfields' => array(
            'select' => array('name' => 'orderimport.shippingmethod', 'type' => 'select'),
            'string' => array('name' => 'orderimport.shippingmethod.name', 'type' => 'string', 'default' => 'Obi',)
        ),
        'expert'    => true,
    ),
    'orderstatus.shipped' => array(
        'i18n' => '{#i18n:formfields__orderstatus.shipped#}',
        'name' => 'orderstatus.shipped',
        'type' => 'select',
    ),
    'orderstatus.canceled' => array(
        'i18n' => '{#i18n:formfields__orderstatus.canceled#}',
        'name' => 'orderstatus.canceled',
        'type' => 'select',
    ),
    'orderstatus.cancelreason' => array(
        'i18n' => '{#i18n:formfields_obi__orderstatus.cancelreason#}',
        'name' => 'orderstatus.cancelreason',
        'type' => 'select',
    ),
    'orderstatus.return' => array(
        'i18n' => '{#i18n:formfields_obi__orderstatus.return#}',
        'name' => 'orderstatus.return',
        'type' => 'select',
    ),
    'orderstatus.sendcarrier.duplicate' => array(
        'i18n' => array('label' => '', ),
        'name' => 'orderstatus.sendcarrier.duplicate',
        'norepeat_included' => true,
        'type' => 'duplicate',
        'duplicate' => array(
            'field' => array(
                'type' => 'subFieldsContainer'
            )
        ),
        'subfields' => array(
            array(
                'i18n' => array('label' => ''),
                'name' => 'orderstatus.sendcarrier.matching',
                'breakbefore' => true,
                'type' => 'matchingcarrier',
                'cssclasses' => array('tableHeadCarrierMatching')
            ),
        ),
    ),
));
