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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLSetting::gi()->get('amazon_config_orderimport');//throws exception if not exists
MLSetting::gi()->add('amazon_config_orderimport', array(
    'importactive' => array(
        'fields' => array(
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
            'orderimport.fbashippingmethod' => array(//use string index to overwrite main setting
                'name' => 'orderimport.fbashippingmethod',
                'type' => 'select',
            ),
            'orderimport.fbapaymentmethod' => array(//use string index to overwrite main setting
                'name' => 'orderimport.fbapaymentmethod',
                'type' => 'select',
            ),
            array(
                'name' => 'orderimport.paymentstatus',
                'type' => 'select',
            ),
        ),
    ),
), true);

MLSetting::gi()->get('amazon_config_vcs');
MLSetting::gi()->add('amazon_config_vcs', array(
    'amazonvcsinvoice' => array(
        'fields' => array(
            'amazonvcsinvoice.invoiceprefix'         => array(
                'name'      => 'amazonvcsinvoice.invoicenumber',
                'type'      => 'amazon_invoicenumber',
                'subfields' => array(
                    'options'  => array(
                        'name'   => 'amazonvcsinvoice.invoicenumberoption',
                        'type'   => 'select',
                        'values' => array(
                            'magnalister' => '{#i18n:amazon_config_amazonvcsinvoice_invoicenumberoption_values_magnalister#}',
                            'matching'    => '{#i18n:amazon_config_amazonvcsinvoice_invoicenumberoption_values_matching#}'
                        )
                    ),
                    'string'   => array('name' => 'amazonvcsinvoice.invoiceprefix', 'type' => 'string'),
                    'matching' => array(
                        'name' => 'amazonvcsinvoice.invoicenumber.matching',
                        'type' => 'select',
                    ),
                )
            ),
            'amazonvcsinvoice.reversalinvoiceprefix' => array(
                'name'      => 'amazonvcsinvoice.reversalinvoicenumber',
                'type'      => 'amazon_invoicenumber',
                'subfields' => array(
                    'options'  => array(
                        'name'   => 'amazonvcsinvoice.reversalinvoicenumberoption',
                        'type'   => 'select',
                        'values' => array(
                            'magnalister' => '{#i18n:amazon_config_amazonvcsinvoice_reversalinvoicenumberoption_values_magnalister#}',
                            'matching'    => '{#i18n:amazon_config_amazonvcsinvoice_reversalinvoicenumberoption_values_matching#}'
                        )
                    ),
                    'string'   => array('name' => 'amazonvcsinvoice.reversalinvoiceprefix', 'type' => 'string'),
                    'matching' => array(
                        'name' => 'amazonvcsinvoice.reversalinvoicenumber.matching',
                        'type' => 'select',
                    ),
                )
            ),
        ),
    ),
), true);
MLSetting::gi()->set('amazon_config_vcs__amazonvcs__fields__invoiceDocumentType', '{#setting:formfields__config_shopware6_invoice_documenttype#}');
MLSetting::gi()->set('amazon_config_vcs__amazonvcs__fields__creditnoteDocumentType', '{#setting:formfields__config_shopware6_creditnote_documenttype#}');
