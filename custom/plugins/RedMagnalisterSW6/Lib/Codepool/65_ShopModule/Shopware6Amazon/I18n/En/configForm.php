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

MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_carrier'} = 'Shipping Carriers from webshop custom fields for orders';
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod'} = 'Shipping method from webshop custom field for orders';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__label'} = 'Payment Status in Shop';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__help'} = 'Please select which shop system payment status should be set in the order details during the magnalister order import.';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__label'} = 'Shipping Service of the Orders (FBA)';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__help'} = '<p>Amazon does not assign any shipping method to imported orders.</p>
<p>Please choose here the available Web Shop shipping methods. The contents of the drop-down menu can be assigned in Shopware > Settings > Shipping Costs.</p>
<p>This setting is important for bills and shipping notes, the subsequent processing of the order inside the shop, and for some ERPs.</p>';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__label'} = 'Payment Status of FBA Orders';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__help'} = 'Please select which shop system payment status should be set in the order details during the magnalister order import.';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';

MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.carrier__help'} = '
Select the shipping carrier that will be assigned to Amazon orders by default.<br>'.'
<br>
You have the following options:<br>
<ul>
	<li><span class="bold underline">Shipping carriers suggested by Amazon</span>
        <p>
        Select a shipping carrier from the drop-down list. Companies recommended by Amazon are displayed.<br>
        <br>
        You should choose this option if you <strong>always</strong> want to <strong>use the same shipping carrier</strong> for Amazon orders.<br>
        </p>
    </li>
    <li><span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_carrier#}</span>
        <p>
        {#i18n:shop_order_attribute_creation_instruction#}
        <br>
        Choose this option if you want to use <strong>different shipping carriers</strong> for Amazon orders.
        </p>
    </li>
	<li><span class="bold underline">Match shipping carriers suggested by Amazon with carriers defined in webshop system (shipping module)</span>
        <p>
        You can match the shipping carriers suggested by Amazon with the service providers created in the Shopware shipping module. Add more than one match by clicking the "+" symbol.<br>
        <br>
        For information on which entry from the Shopware shipping module is used for the Amazon order import, please refer to the info icon under "Order Import" -> "Shipping Service of the Orders".<br>
        <br>
        Choose this option if you want to use <strong>existing shipping settings from the Shopware shipping module</strong>.<br>
        </p>
    </li>
    <li><span class="bold underline">magnalister adds a free text field in the order details</span>
        <p>
        If you select this option, magnalister will add a field in the order details of the Shopware order when importing it. You can enter the shipping carrier in this field.<br>
        <br>
        Choose this option if you want to use different shipping carriers for Amazon orders.<br>
        </p>
    </li>
	<li><span class="bold underline">Manually enter a shipping carrier for all orders in a magnalister text field</span>
        <p>
        If you select "Other" under "Shipping carriers" in magnalister, you can manually enter the name of a shipping carrier in the right text field.<br>
        <br>
        Choose this option if you want to <strong>manually enter the same shipping carrier</strong> for all Amazon orders.<br>
        <br>
        <span class="bold underline">Important notes:</span>
        </p>
    </li>
</ul>
<ul>
	<li>Providing a shipping carrier is mandatory for shipping confirmations on Amazon.<br><br></li>
	<li>Failure to provide the shipping carrier may result in temporary suspension of the sales permission on Amazon.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.shipmethod__help'} = '
Select the shipping method that will be assigned to Amazon orders by default.<br>'.'
<br>
You have the following options:
<ul>
	<li><span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod#}</span>
        <p>
        {#i18n:shop_order_attribute_creation_instruction#}
        <br>
        Choose this option if you want to use <strong>different shipping methods</strong> for Amazon orders.<br>
        </p>
    </li>
	<li><span class="bold underline">Match shipping method with entries from webshop shipping module</span>
	    <p>
        You can match any shipping method with the entries created in the Shopware shipping module. Add more than one match by clicking the "+" symbol.<br>
        <br>
        For information on which entry from the Shopware shipping module is used for the Amazon order import, please refer to the info icon under "Order Import" -> "Shipping Service of the Orders".<br>
        <br>
        Choose this option if you want to use <strong>existing shipping settings from the Shopware shipping module.</strong><br>
        </p>
    </li>
	<li><span class="bold underline">magnalister adds a free text field in the order details</span>
        <p>
        If you select this option, magnalister will add a field in the order details  in the Shopware order when importing it. You can enter the shipping method in this field.<br>
        <br>
        Choose this option if you want to use different shipping methods for Amazon orders.<br>
        </p>
    </li>
	<li><span class="bold underline">Manually enter a shipping method for all orders in a magnalister text field</span>
        <p>
        If you select this option in magnalister, you can manually enter the name of a shipping method in the right text field. <br>
        <br>
        Choose this option if you want to <strong>manually enter the same shipping method</strong> for all Amazon orders.<br>
        <br>
        <span class="bold underline">Important notes:</span>
        </p>
    </li>
</ul>
<ul>
	<li>Providing a shipping method is mandatory for shipping confirmations on Amazon.<br><br></li>
	<li>Failure to provide the shipping method may result in temporary suspension of the sales permission on Amazon.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__label'} = 'Invoice number';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__help'} = '<p>
Choose here if you want to have your invoice numbers generated by magnalister or if you want them to be taken from a Shopware custom field.
</p><p>
<b>Create invoice numbers via magnalister</b>
</p><p>
magnalister generates consecutive invoice numbers during the invoice creation. You can define a prefix that is set in front of the invoice number. Example: R10000.
</p><p>
Note: Invoices created by magnalister start with the number 10000.
</p><p>
<b>Match invoice numbers with Shopware custom field</b>
</p><p>
When creating the invoice, the value is taken from the Shopware custom field you selected.
</p><p>
{#i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Important:</b><br/> magnalister generates and transmits the invoice as soon as the order is marked as shipped. Please make sure that the custom field is filled, otherwise an error will be caused (see tab "Error Log").
<br/><br/>
If you use custom field matching, magnalister is not responsible for the correct, consecutive creation of invoice numbers.
</p>';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumberoption__label'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber.matching__label'} = 'Shopware order custom field';

MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__label'} = 'Reversal invoice number';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__help'} = '<p>
Choose here if you want to have your reversal invoice numbers generated by magnalister or if you want them to be taken from a Shopware custom field.
</p><p>
<b>Create reversal invoice numbers via magnalister</b>
</p><p>
magnalister generates consecutive reversal invoice numbers during the invoice creation. You can define a prefix that is set in front of the reversal invoice number. Example: R10000.
</p><p>
Note: Invoices created by magnalister start with the number 10000.
</p><p>
<b>Match reversal invoice numbers with Shopware custom field</b>
</p><p>
When creating the invoice, the value is taken from the Shopware custom field you selected.
</p><p>
{#i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Important:</b><br/>magnalister generates and transmits the invoice as soon as the order is marked as shipped. Please make sure that the custom field is filled, otherwise an error will be caused (see tab "Error Log").
<br/><br/>
If you use custom field matching, magnalister is not responsible for the correct, consecutive creation of reversal invoice numbers.
</p>';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumberoption__label'} = MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumberoption__label'};
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber.matching__label'} = MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber.matching__label'};

MLI18n::gi()->{'amazon_config_amazonvcsinvoice_invoicenumberoption_values_matching'} = 'Match invoice numbers with Shopware free text field';
MLI18n::gi()->{'amazon_config_amazonvcsinvoice_reversalinvoicenumberoption_values_matching'} = 'Match reversal invoice numbers with Shopware free text field';