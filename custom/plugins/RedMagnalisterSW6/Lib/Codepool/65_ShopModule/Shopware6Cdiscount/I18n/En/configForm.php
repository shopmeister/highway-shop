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

MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentstatus__label'} = 'Payment Status in Shop';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentstatus__help'} = '<p>Cdiscount does not pass shipping service information with the order import</p>
<p>For this reason please choose the available dispatch type information from the web-shop. You can find those in the dropdown menu in Shopware-Admin > Shipping > carriers.</p>
<p>That configuration is important for printing of the invoice and shipping documents as well as for later adaptation of the invoice in the shop and for your ERP system.</p>';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'cdiscount_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'sCdiscount_automatically'} = '-- allocate automatically --';

MLI18n::gi()->{'cdiscount_config_orderimport__field__orderstatus.carrier__help'} = '
Select the shipping carrier that will be assigned to Cdiscount orders by default.<br>
<br>
You have the following options:<br>
<ul>
    <li>
        <span class="bold underline">Shipping carriers suggested by Cdiscount</span>
        <p>Select a shipping carrier from the drop-down list. Companies recommended by Cdiscount are displayed.<br>
            <br>
            You should choose this option if you <strong>always</strong> want to <strong>use the same shipping carrier</strong> for Cdiscount orders.
        </p>
    </li>
    <li>
        <span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_carrier#}</span>
        <p>{#i18n:shop_order_attribute_creation_instruction#}
            <br>
            Choose this option if you want to use <strong>different shipping carriers</strong> for Cdiscount orders.
        </p>
    </li>
    <li>
        <span class="bold underline">Match shipping carriers suggested by Cdiscount with carriers defined in webshop system (shipping module)</span>
        <p>You can match the shipping carriers suggested by Cdiscount with the service providers created in the Shopware shipping module. Add more than one match by clicking the "+" symbol.<br>
            <br>
            For information on which entry from the Shopware shipping module is used for the Cdiscount order import, please refer to the info icon under "Order Import" -> "Shipping Service of the Orders".<br>
            <br>
            Choose this option if you want to use <strong>existing shipping settings from the Shopware shipping module</strong>.<br>
        </p>
    </li>
    <li>
        <span class="bold underline">magnalister adds a free text field in the order details</span>
        <p>If you select this option, magnalister will add a field in the order details of the Shopware order when importing it. You can enter the shipping carrier in this field.<br>
            <br>
            Choose this option if you want to use <strong>different shipping carriers</strong> for Cdiscount orders.<br>
        </p>
    </li>
    <li>
        <span class="bold underline">Manually enter a shipping carrier for all orders in a magnalister text field</span><br>
        <p>Choose this option if you want to <strong>manually enter the same shipping carrier</strong> for all Cdiscount orders.<br></p>
    </li>
</ul>
<span class="bold underline">Important notes:</span>
<ul>
    <li>Providing a shipping carrier is mandatory for shipping confirmations on Cdiscount.<br><br></li>
    <li>Failure to provide the shipping carrier may result in temporary suspension of the sales permission on Cdiscount.</li>
</ul>
';
