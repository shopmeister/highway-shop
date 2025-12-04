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

MLI18n::gi()->{'formfields__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'formfields__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'formfields__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'otto_config_orderimport__field__orderimport.paymentstatus__label'} = 'Payment Status in Shop';
MLI18n::gi()->{'otto_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'otto_config_orderimport__field__orderimport.paymentstatus__help'} = '<p>Otto does not assign any shipping method to imported orders.</p>
<p>Please choose here the available Web Shop shipping methods. The contents of the drop-down menu can be assigned in Shopware > Settings > Shipping Costs.</p>
<p>This setting is important for bills and shipping notes, the subsequent processing of the order inside the shop, and for some ERPs.</p>';
MLI18n::gi()->{'otto_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->set('otto_config_free_text_attributes_opt_group', 'Custom fields', true);

MLI18n::gi()->{'formfields_otto__return.carrier__help'} = '<strong>Option: "{#i18n:otto_config_free_text_attributes_opt_group_value#}"</strong>
<p>magnalister adds a field under "Order Details" in the orders to enter return shipment data such as the tracking code and carrier.</p>
<p>The module <a href="https://store.shopware.com/de/appli31539763616m/dpd-paketversand-mit-sendungsverfolgung-mydpd.html" target="_blank">DPD Shipping with Tracking (MyDPD)</a> supports the automatic filling of these fields, making returns processing even easier.</p>';
MLI18n::gi()->{'formfields_otto__return.trackingkey__help'} = '{#i18n:formfields_otto__return.carrier__help#}';
