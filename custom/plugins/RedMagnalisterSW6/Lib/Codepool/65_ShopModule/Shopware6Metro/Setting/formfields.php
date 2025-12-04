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

MLSetting::gi()->get('formfields');
MLSetting::gi()->add('formfields', array(
    'config_invoice_invoiceNumberPrefix'         => array(
        'i18n'      => '{#i18n:formfields__config_invoice_invoiceNumber#}',
        'name'      => 'invoice.invoicenumber',
        'type'      => 'bundles_invoicenumber',
        'subfields' => array(
            'options'  => array(
                'i18n'       => '{#i18n:formfields__config_invoice_invoiceNumberOption#}',
                'name'       => 'invoice.invoicenumberoption',
                'type'       => 'select',
                'values'     => array(
                    'magnalister' => '{#i18n:formfields_config_invoice_invoiceNumberOption_values_magnalister#}',
                    'matching'    => '{#i18n:formfields_config_invoice_invoiceNumberOption_values_matching#}'
                ),
                'cssclasses' => array('ml-invoiceNumberOption')
            ),
            'string'   => array(
                'i18n'       => '{#i18n:formfields__config_invoice_invoiceNumberPrefixValue#}',
                'name'       => 'invoice.invoicenumberprefix', 'type' => 'string',
                'cssclasses' => array('ml-invoiceNumberPrefix')
            ),
            'matching' => array(
                'i18n'       => '{#i18n:formfields__config_invoice_invoiceNumberMatching#}',
                'name'       => 'invoice.invoicenumber.matching',
                'type'       => 'select',
                'cssclasses' => array('ml-invoiceNumberMatching')
            ),
        )
    ),
    'config_invoice_reversalInvoiceNumberPrefix' => array(
        'i18n'      => '{#i18n:formfields__config_invoice_reversalInvoiceNumber#}',
        'name'      => 'invoice.reversalInvoiceNumber',
        'type'      => 'bundles_invoicenumber',
        'subfields' => array(
            'options'  => array(
                'i18n'       => '{#i18n:formfields__config_invoice_invoiceNumberOption#}',
                'name'       => 'invoice.reversalinvoicenumberoption',
                'type'       => 'select',
                'values'     => array(
                    'magnalister' => '{#i18n:formfields_config_invoice_invoiceNumberOption_values_magnalister#}',
                    'matching'    => '{#i18n:formfields_config_invoice_invoiceNumberOption_values_matching#}'
                ),
                'cssclasses' => array('ml-reversalInvoiceNumberOption')
            ),
            'string'   => array(
                'i18n'       => '{#i18n:formfields__config_invoice_invoiceNumberPrefixValue#}',
                'name'       => 'invoice.reversalinvoicenumberprefix',
                'type'       => 'string',
                'cssclasses' => array('ml-reversalInvoiceNumberPrefix')
            ),
            'matching' => array(
                'i18n'       => '{#i18n:formfields__config_invoice_invoiceNumberMatching#}',
                'name'       => 'invoice.reversalinvoicenumber.matching',
                'type'       => 'select',
                'cssclasses' => array('ml-reversalInvoiceNumberMatching')
            ),
        )
    )
));

