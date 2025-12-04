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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLSetting::gi()->add('formgroups_obi__account', array(
    'legend' => array('i18n' => '{#i18n:formgroups_obi__account#}',),
    'fields' => array(
        'clientid' => array(
            'i18n' => '{#i18n:formfields_obi__clientid#}',
            'name' => 'clientid',
            'type' => 'string',
        ),
        'clientsecret' => array(
            'i18n' => '{#i18n:formfields_obi__clientsecret#}',
            'name' => 'clientsecret',
            'type' => 'password',
            'savevalue' => '__saved__',
        ),
    ),
));

MLSetting::gi()->add('formgroups_obi__prepare', array(
    'legend' => array('i18n' => '{#i18n:formgroups_obi__prepare#}'),
    'fields' => array(
//        'prepare.status' => '{#setting:formfields__prepare.status#}',
//        'vat' => '{#setting:formfields_obi__vat#}',
        'delivery' => '{#setting:formfields_obi__delivery#}'
    ),
));

MLSetting::gi()->add('formgroups_obi__upload', array(
    'legend' => array('i18n' => '{#i18n:formgroups_obi__upload#}'),
    'fields' => array(
//        'checkin.status' => '{#setting:formfields__checkin.status#}',
        'lang' => '{#setting:formfields_obi__lang#}',
//        'imagesize' => '{#setting:formfields_obi__imagesize#}',
    ),
));

MLSetting::gi()->add('formgroups_obi__additionalsettings', array(
   'legend' => array('i18n' => '{#i18n:formgroups_obi__additionalsettings#}',),
   'fields' => array(
       'warehouseid' => array(
           'i18n' => '{#i18n:formfields_obi__warehouseid#}',
           'name' => 'warehouseid',
           'type' => 'string',
           'default' => '1'
       ),
   )
));

MLSetting::gi()->add('formgroups_obi__quantity', array(
    'legend' => array('i18n' => '{#i18n:formgroups_legend_quantity#}'),
    'fields' => array(
        'quantity' => '{#setting:formfields__quantity#}',
        'maxquantity' => '{#setting:formfields__maxquantity#}',
        'deliverytime' => '{#setting:formfields_obi__deliverytime#}',
    )
));

MLSetting::gi()->add('formgroups_obi__orderimport', array(
    'legend' => array('i18n' => '{#i18n:formgroups__orderimport#}'),
    'fields' => array(
        'importactive' => '{#setting:formfields__importactive#}',
        'customergroup' => '{#setting:formfields__customergroup#}',
        'orderimport.shop' => '{#setting:formfields__orderimport.shop#}',
    ),
));

MLSetting::gi()->add('formgroups_obi__orderstatusimport', array(
    'legned' => array('i18n' => '{#i18n:formgroups_obi__orderstatusimport#}'),
    'fields' => array(
        'orderstatus.open' => '{#setting:formfields_obi__orderstatus.open#}',
    ),
));

MLSetting::gi()->add('formgroups_obi_paymentandshipping', array(
    'legned' => array('i18n' => '{#i18n:formgroups_obi__paymentandshipping#}'),
    'fields' => array(
        'orderimport.paymentmethod' => '{#setting:formfields_obi__orderimport.paymentmethod#}',
        'orderimport.shippingmethod' => '{#setting:formfields_obi__orderimport.shippingmethod#}',
    ),
));

MLSetting::gi()->add('formgroups_obi__orderstatus', array(
    'legend' => array('i18n' => '{#i18n:formgroups_obi__orderstatus#}'),
    'fields' => array(
        'orderstatus.sync' => '{#setting:formfields__orderstatus.sync#}',
        'orderstatus.shipped' => '{#setting:formfields_obi__orderstatus.shipped#}',
        'orderstatus.standardshipping' => '{#setting:formfields_obi__orderstatus.carrier#}',
        'orderstatus.canceled' => '{#setting:formfields_obi__orderstatus.canceled#}',
        'orderstatus.cancelreason' => '{#setting:formfields_obi__orderstatus.cancelreason#}',
        'orderstatus.return' => '{#setting:formfields_obi__orderstatus.return#}',
    ),
));