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

MLSetting::gi()->add('formfields', array(
    'config_shopware6_invoice_documenttype' => array(
        'i18n' => '{#i18n:formfields__config_shopware6_invoice_documenttype#}',
        'name' => 'invoice.invoice_documenttype',
        'type' => 'select',
        'default' => 'invoice',
        'cssclasses' => array('ml-invoiceDocType'),
        //'expert' => true, // dont set it expert because it will be hidden by javascript
    ),
    'config_shopware6_creditnote_documenttype' => array(
        'i18n' => '{#i18n:formfields__config_shopware6_creditnote_documenttype#}',
        'name' => 'invoice.creditnote_documenttype',
        'type' => 'select',
        'default' => 'storno',
        'cssclasses' => array('ml-invoiceDocType'),
        //'expert' => true, // dont set it expert because it will be hidden by javascript
    ),
));
