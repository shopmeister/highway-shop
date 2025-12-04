<?php /**
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
MLSetting::gi()->get('hood_config_orderimport');//throws exception if not exists
MLSetting::gi()->add('hood_config_orderimport', array(
    'importactive' => array(
        'fields' => array(
            'orderimport.paymentstatus' => array(
                'name' => 'orderimport.paymentstatus',
                'fieldposition' => array('after'=> 'orderstatus.open'),
                'type' => 'select',
            ),
            'orderimport.shippingmethod' => array(//use string index to overwrite main setting
                'name' => 'orderimport.shippingmethod',
                'type' => 'select',
                'expert' => false
            ),
            'orderimport.paymentmethod' => array(//use string index to overwrite main setting
                'name' => 'orderimport.paymentmethod',
                'type' => 'select',
                'expert' => false
            ),
        ),
    ),
), true);

MLSetting::gi()->get('formgroups_hood__orderstatus');//throws exception if not exists
MLSetting::gi()->add('formgroups_hood__orderstatus__fields__orderstatus.carrier.default',  
    array(
        'i18n' => '{#i18n:formfields__orderstatus.carrier.default#}',
        'name' => 'orderstatus.carrier.default',
        'type' => 'select',
    )
);