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

MLI18n::gi()->add('formfields', array(
    'config_shopware6_invoice_documenttype'    => array(
        'label' => 'Shopware PDF Document Creation Invoice "Technical Name"',
    ),
    'config_shopware6_creditnote_documenttype' => array(
        'label' => 'Shopware PDF Document Creation Credit Note "Technical Name"',
    ),
));


MLI18n::gi()->shop_order_attribute_name = 'Shopware free text';
MLI18n::gi()->shop_order_attribute_creation_instruction = 'You can create free text fields in your Shopware backend under "Settings" -> " Custom fields" (Assign to: Orders) and fill them under "Orders". To do so, open the corresponding order and scroll down to "Custom fields".';

