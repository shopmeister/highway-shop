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

MLI18n::gi()->Shopware6_Marketplace_Configuration_SalesChannel_Label = 'Shopware Sales Channels';
MLI18n::gi()->Shopware6_Marketplace_Configuration_SalesChannel_Info = '<p>Select the Shopware sales channel to which orders from this marketplace should be assigned.
The shipping and payment methods used by magnalister for these orders are also taken from this sales channel.</p>
<p>You can locate the Shopware Sales Channels in the main menu on the left, under &ldquo;Sales Channel.&rdquo;</p>
<p>Open the desired Shopware Sales Channel and under &ldquo;Payment and shipping,&rdquo; configure your preferred payment and shipping methods.</p>
<p>These settings will be available as dropdown options in the magnalister plugin (refer to the subsequent settings for details).</p>';

MLI18n::gi()->{'Shopware_EBay_Configuration_ShippingMethod_Info'} = '<p>Shipping service that will apply to all orders imported from {#setting:currentMarketplaceName#}. Standard: “Automatic Allocation”</p>
<p>
If you choose “Automatic Allocation”, magnalister will accept the shipping service chosen by the buyer on {#setting:currentMarketplaceName#}. This method will then also be added to your payment methods under Shopware > Settings > shipping costs.</p>
<p>
Additional payment methods can be added to the list via Shopware > Settings > Shipping costs, then activated here.</p>
<p>
This setting is necessary for the invoice and shipping notice, and for editing orders later in the Shop or via ERP.</p> ';
MLI18n::gi()->{'Shopware_Amazon_Configuration_ShippingMethod_Info'} = '<p>Amazon does not assign any shipping method to imported orders.</p>
<p>Please choose here the available Web Shop shipping methods. The contents of the drop-down menu can be assigned in Shopware > Settings > Shipping Costs.</p>
<p>This setting is important for bills and shipping notes, the subsequent processing of the order inside the shop, and for some ERPs.</p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_OrderStatus_Label'} = 'Update Order Status When';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_PaymentStatus_Label'} = 'Update Payment Status When';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_PaymentStatus_Info'} = 'Order statuses that can be triggered by eBay payments. 
If the order has a different status, this cannot be changed by an eBay payment.<br /><br />
If you don\'t wish any status changes based on eBay payment, please deactivate the checkbox.<br /><br />
<b>Please note:</b>The status of summarised orders will only be changed when paid in full.';

MLI18n::gi()->{'Shopware_Ebay_Configuration_ArticleDescriptionTemplate_sDefault'} = '<style>
ul.magna_properties_list {
    margin: 0 0 20px 0;
    list-style: none;
    padding: 0;
    display: inline-block;
    width: 100%
}
ul.magna_properties_list li {
    border-bottom: none;
    width: 100%;
    height: 20px;
    padding: 6px 5px;
    float: left;
    list-style: none;
}
ul.magna_properties_list li.odd {
    background-color: rgba(0, 0, 0, 0.05);
}
ul.magna_properties_list li span.magna_property_name {
    display: block;
    float: left;
    margin-right: 10px;
    font-weight: bold;
    color: #000;
    line-height: 20px;
    text-align: left;
    font-size: 12px;
    width: 50%;
}
ul.magna_properties_list li span.magna_property_value {
    color: #666;
    line-height: 20px;
    text-align: left;
    font-size: 12px;

    width: 50%;
}
</style>
<p>#TITLE#</p>
<p>#ARTNR#</p>
<p>#SHORTDESCRIPTION#</p>
<p>#PICTURE1#</p>
<p>#PICTURE2#</p>
<p>#PICTURE3#</p>
<p>#DESCRIPTION#</p>
<p>#Description1# #Freetextfield1#</p>
<p>#Description2# #Freetextfield2#</p>
<div>#PROPERTIES#</div>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_sLabel'} = 'eBay Payment Status in Shop';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_sDescription'} = '<p>Here you define the payment and order status of which an order will get in the shop when it is paid via PayPal on eBay.</p>
<p>If a customer buys your product on eBay the order will be transfered to your shop immediately. Thereby the art of payment parameter will be taken from your configuration in „payment method of orders“ or set as „eBay“.</p>
<p>
furthermore, magnalister monitors if a customer paid after the first order import or if he changed his shipping adress for 16 days on an hourly base.
We therefore check for order changes in the following time interval:
	<ul>
                <li>	1.5 hours after the order every 15 minutes,</li>
	<li>	hourly basis 24 hours after order,</li>
	<li>	up to 48 hours - every 2 hours</li>
	<li>	up to 1 week – every 3 hours</li>
	<li>	up to 16 day after order every 6 hours.</li>
        </ul>
</p>
';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_Payment_sLabel'} = 'Payment status';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_Order_sLabel'} = 'Order status';
MLI18n::gi()->{'Shopware_Amazon_Configuration_PaymentStatus_sLabel'} = 'Payment Status in Shop';
MLI18n::gi()->{'Shopware_Amazon_Configuration_PaymentStatus_sDescription'} = 'The payment status which a new order shall become automatically in the shop.
';
MLI18n::gi()->{'form_config_orderimport_exchangerate_update_help'} = '<strong>General:</strong>
<p>
If the currency of the web-shop differs from the marketplace currency, during the order import process and during product uploading, magnalister calculates accordingly to the web-shop default currency.
When importing marketplace orders, concerning the currency settings, magnalister simulates exactly the same behavior like the web-shop saves any frontend-orders.
</p>

<strong>Caution:</strong>
<p>
By activating this function, the currency settings in your web-shop will be updated and overwritten with the current Yahoo Finance exchange-rate.
<u>As a result, this will affect your foreign currency in the web-shop frontend.</u>
</p>
<p>
The following magnalister functions trigger the exchange-rate update:
<ul>
<li>Order import</li>
<li>Preparation of products</li>
<li>Upload of products</li>
<li>Synchronization of stock and prices</li>
</ul>
</p>
<p>
If an exchange-rate of a marketplace is not configured in the web-shop currency settings, magnalister will display an error message.
</p>';
MLI18n::gi()->{'form_config_orderimport_exchangerate_update_alert'} = '<strong>Caution:</strong>
<p>
By activating this function, the currency settings in your web-shop will be updated and overwritten with the current Yahoo Finance exchange-rate.
<u>As a result, this will affect your foreign currency in the web-shop frontend.</u>
</p>
<p>
The following magnalister functions trigger the exchange-rate update:
<ul>
<li>Order import</li>
<li>Preparation of products</li>
<li>Upload of products</li>
<li>Synchronization of stock and prices</li>
</ul>
</p>';
MLI18n::gi()->{'global_config_price_field_price.discountmode_label'} = 'Mode of discount';

MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_label'}='Sales Price from Price Rule';
MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_chinesePriceoptions_label'}='Price from Price Rule';
MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_help'} = 'With this function you can transfer different prices to the marketplace and synchronize them automatically.<br />
<br />
Select a price rule from your webshop using the dropdown on the right (see below).<br />
<br />
If you do not enter a price in the new price rule, the default price from the webshop will be used automatically. This makes it very easy to enter a different price even for just a few items. The other price configurations are also applied.<br />
<br />
<b>Example:</b>
<ul>
<li>Create a price rule in the Settings > Shop > Rule builder menu of your webshop, e.g. "{#setting:currentMarketplaceName#} customers".</li>
<li>In your webshop`s Products edition > Advanced pricing, add the wanted prices to the items</li>
<ul>';

MLI18n::gi()->{'shopware6_configuration_paymentmethod_help'} = '<p>From the dropdown, select the payment method to be assigned to all {#setting:currentMarketplaceName#} orders upon import. You can choose from the payment methods that have been configured under &ldquo;Payment and shipping&rdquo; in the selected Shopware Sales Channel.</p>
<p>Additional Notes:</p>
<ul>
<li aria-level="1">
<p>Selecting a payment method is mandatory. If no payment method is chosen, magnalister will issue an error message at the top of the screen when you attempt to save the settings.<br><br></p>
</li>
<li aria-level="1">
<p>An error message will also appear if a payment method selected from the dropdown is later removed from the Shopware Sales Channel.<br><br></p>
</li>
<li aria-level="1">
<p>The payment method information is crucial for the printing of invoices and packing slips, and for the subsequent management of the order both in the shop and in inventory management systems.</p>
</li>
</ul>';
MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withfrommarketplace_help'} = '<p>From the dropdown, select the shipping method to be assigned to all {#setting:currentMarketplaceName#} orders upon import.</p>
<p>Options:</p>
<ul>
<li aria-level="1">
<p>Adopt shipping method from {#setting:currentMarketplaceName#}: magnalister will adopt the shipping method selected by the buyer on {#setting:currentMarketplaceName#}. If this shipping method is not already configured in the Shopware shipping settings, magnalister will automatically create it. It will also be automatically added to the "Payment and shipping" section of the Shopware Sales Channel.<br><br></p>
</li>
<li aria-level="1">
<p>Select a specific shipping method: Choose any of the active shipping methods from the dropdown to apply to all orders. These are the methods you have configured under &ldquo;Payment and shipping&rdquo; in the selected Shopware Sales Channel.</p>
</li>
</ul>
<p>Additional Notes:</p>
<ul>
<li aria-level="1">
<p>Selecting a shipping method is mandatory. If no shipping method is chosen, magnalister will issue an error message at the top of the screen when you attempt to save the settings.<br><br></p>
</li>
<li aria-level="1">
<p>An error message will also appear if a shipping method selected from the dropdown is later removed from the Shopware Sales Channel.<br><br></p>
</li>
</ul>
<p>The shipping method information is crucial for the printing of invoices and packing slips, and for the subsequent management of the order both in the shop and in inventory management systems.</p>';
MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help'} = '<p>From the dropdown, select the shipping method to be assigned to all {#setting:currentMarketplaceName#} orders upon import.</p>
<p>Options:</p>
<ul>

<li aria-level="1">
<p>Select a specific shipping method: Choose any of the active shipping methods from the dropdown to apply to all orders. These are the methods you have configured under &ldquo;Payment and shipping&rdquo; in the selected Shopware Sales Channel.</p>
</li>
</ul>
<p>Additional Notes:</p>
<ul>
<li aria-level="1">
<p>Selecting a shipping method is mandatory. If no shipping method is chosen, magnalister will issue an error message at the top of the screen when you attempt to save the settings.<br><br></p>
</li>
<li aria-level="1">
<p>An error message will also appear if a shipping method selected from the dropdown is later removed from the Shopware Sales Channel.<br><br></p>
</li>
</ul>
<p>The shipping method information is crucial for the printing of invoices and packing slips, and for the subsequent management of the order both in the shop and in inventory management systems.</p>';
