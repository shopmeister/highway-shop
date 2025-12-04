<?php

MLI18n::gi()->{'sOtto_automatically'} = '-- allocate automatically --';
MLI18n::gi()->{'otto_config_account_producttemplate'} = 'Produkt Template';
MLI18n::gi()->{'otto_config_account_title'} = 'Access Data';
MLI18n::gi()->{'otto_configform_orderimport_payment_values__textfield__title'} = 'Aus Textfeld';
MLI18n::gi()->{'otto_configform_orderstatus_sync_values__no'} = '{#i18n:otto_config_general_nosync#}';
MLI18n::gi()->{'otto_config_order__legend__orderstatusimport'} = 'Order Status: Import (Marketplace to Shop)';
MLI18n::gi()->{'otto_config_order__legend__guidelines'} = 'Directives de communication {#setting:currentMarketplaceName#}';
MLI18n::gi()->{'otto_configform_orderimport_payment_values__matching__title'} = 'Zahlart von {#setting:currentMarketplaceName#} &uuml;bernehmen';
MLI18n::gi()->{'otto_configform_pricesync_values__no'} = '{#i18n:otto_config_general_nosync#}';
MLI18n::gi()->{'formfields__price__hint'} = '<span style="color: red">Zu dem hier definierten Preis addiert sich der unter "Artikelvorbereitung" ausgewählte Versandkostenaufschlag</span>';
MLI18n::gi()->{'otto_config_prepare__field__vat__hint'} = '';
MLI18n::gi()->{'otto_config_carrier_matching_title_marketplace_carrier'} = 'Marketplace supported carriers';
MLI18n::gi()->{'formgroups_legend_quantity'} = 'Lager';
MLI18n::gi()->{'otto_configform_orderimport_payment_values__textfield__textoption'} = '1';
MLI18n::gi()->{'otto_config_prepare__legend__upload'} = 'Product Upload';
MLI18n::gi()->{'otto_config_account_emailtemplate'} = 'Promotion-E-Mail Template';
MLI18n::gi()->{'formfields__importactive__hint'} = '';
MLI18n::gi()->{'otto_config_free_text_attributes_opt_group_value'} = 'Associez-le au champ de texte libre sur une commande spécifique';
MLI18n::gi()->{'otto_config_account_emailtemplate_sender'} = 'Beispiel-Shop';
MLI18n::gi()->{'otto_configform_sync_values__no'} = '{#i18n:otto_config_general_nosync#}';
MLI18n::gi()->{'otto_config_carrier_matching_title_shop_carrier'} = 'Carriers defined in the shop-system (shipping module)';
MLI18n::gi()->{'otto_config_general_nosync'} = 'keine Synchronisierung';
MLI18n::gi()->{'otto_config_account_price'} = 'Preisberechnung';
MLI18n::gi()->{'otto_config_order__legend__paymentandshipping'} = 'Payment and Shipping Service of the Orders';
MLI18n::gi()->{'otto_config_account__field__tabident__label'} = '{#i18n:ML_LABEL_TAB_IDENT#}';
MLI18n::gi()->{'otto_config_prepare__field__vat__label'} = 'VAT';
MLI18n::gi()->{'otto_config_carrier_option_group_additional_option'} = 'Additional option:';
MLI18n::gi()->{'otto_config_matching_shop_values'} = 'Shop Values';
MLI18n::gi()->{'formfields__price__help'} = 'Geben Sie einen prozentualen oder fest definierten Preis Auf- oder Abschlag an. Abschlag mit vorgesetztem Minus-Zeichen.<br><br><span style="color: red">Zu dem hier definierten Preis addiert sich der unter "Artikelvorbereitung" ausgewählte Versandkostenaufschlag</span>';
MLI18n::gi()->{'otto_config_order__legend__orderstatus'} = 'Order Status: Synchronisation (Shop to Marketplace)';
MLI18n::gi()->{'otto_config_prepare__field__vat__matching__titlesrc'} = 'Shop Tax Classes';
MLI18n::gi()->{'formfields__price__label'} = 'Preis';
MLI18n::gi()->{'otto_configform_orderstatus_sync_values__auto'} = '{#i18n:otto_config_general_autosync#}';
MLI18n::gi()->{'otto_config_prepare__field__processingtime__label'} = 'Lieferzeit in Werktagen';
MLI18n::gi()->{'otto_config_general_autosync'} = 'Automatische Synchronisierung per CronJob (empfohlen)';
MLI18n::gi()->{'otto_config_account_orderimport_returntrackingkey_title'} = 'Return Tracking Key';
MLI18n::gi()->{'otto_config_sync_inventory_import__false'} = 'Nein';
MLI18n::gi()->{'otto_config_emailtemplate_content'} = '
 <style><!--
body {
    font: 12px sans-serif;
}
table.ordersummary {
	width: 100%;
	border: 1px solid #e8e8e8;
}
table.ordersummary td {
	padding: 3px 5px;
}
table.ordersummary thead td {
	background: #cfcfcf;
	color: #000;
	font-weight: bold;
	text-align: center;
}
table.ordersummary thead td.name {
	text-align: left;
}
table.ordersummary tbody tr.even td {
	background: #e8e8e8;
	color: #000;
}
table.ordersummary tbody tr.odd td {
	background: #f8f8f8;
	color: #000;
}
table.ordersummary td.price,
table.ordersummary td.fprice {
	text-align: right;
	white-space: nowrap;
}
table.ordersummary tbody td.qty {
	text-align: center;
}
--></style>
<p>Hallo #FIRSTNAME# #LASTNAME#,</p>
<p>vielen Dank f&uuml;r Ihre Bestellung! Sie haben &uuml;ber #MARKETPLACE# in unserem Shop folgendes bestellt:</p>
#ORDERSUMMARY#
<p>Zuz&uuml;glich etwaiger Versandkosten.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Gr&uuml;&szlig;en,</p>
<p>Ihr Online-Shop-Team</p>';
MLI18n::gi()->{'otto_config_account_sync'} = 'Preis und Lager';
MLI18n::gi()->{'otto_configform_stocksync_values__no'} = '{#i18n:otto_config_general_nosync#}';
MLI18n::gi()->{'otto_config_prepare__legend__prepare'} = 'Product Preparation';
MLI18n::gi()->{'otto_config_prepare__legend__pictures'} = 'Einstellungen f&uuml;r Bilder';
MLI18n::gi()->{'otto_config_carrier_option_matching_option'} = 'Match marketplace supported carrier with carriers defined in shop-system';
MLI18n::gi()->{'otto_config_order__field__importactive__hint'} = 'Please note: Orders from the OTTO Market marketplace are automatically accepted when they are handed over to the web shop (order import).';
MLI18n::gi()->{'otto_config_account_prepare'} = 'Product Preparation';
MLI18n::gi()->{'otto_configform_orderimport_shipping_values__textfield__title'} = 'Aus Textfeld';
MLI18n::gi()->{'formgroups_otto__orderstatus'} = 'Synchronisation des Bestell-Status vom Shop zu OTTO Market';
MLI18n::gi()->{'otto_config_sync_inventory_import__true'} = 'Ja';
MLI18n::gi()->{'otto_configform_orderimport_shipping_values__matching__title'} = 'Versandart von {#setting:currentMarketplaceName#} &uuml;bernehmen';
MLI18n::gi()->{'otto_configform_pricesync_values__auto'} = '{#i18n:otto_config_general_autosync#}';
MLI18n::gi()->{'otto_config_account__legend__tabident'} = 'Tab';
MLI18n::gi()->{'otto_config_account_orderimport_returntrackingkey_info'} = 'For Standard Shipping Service "Return Tracking Key" is required filed on OTTO Market marketplace. Since shop system does not provide such fields as standard option we need to match it to custom filed.';
MLI18n::gi()->{'otto_config_order__legend__orderimport'} = 'Basic Order Settings';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__help'} = '
            Hint: idealo supports only "available" and "not available" for your offers.<br />
            <br />
            Stock shop > 0 = availible on {#i18n:sModuleNameOtto#}<br />
            Stock shop < 1 = not avilible on {#i18n:sModuleNameOtto#}<br />
            <br />
            <br />
            Function:<br />
            Automatic synchronisation by CronJob (recommended)<br />
            <br />
            <br />
            The function "Automatic Synchronisation by CronJob" checks the shop stock every 4 hours*<br />
            <br />
            <br />
            By this procedure, the database values are checked for changes. The new data will be submitted, also when the changes had been set by an inventory management system.<br />
            <br />
            You can manually synchronize stock changes, by clicking the assigned button in the magnalister-header, next left to the ant-logo.<br />
            <br />
            Additionally, you can synchronize stock changes, by setting a own cronjob to your following shop-link:<br />
            <i>{#setting:sSyncInventoryUrl#}</i><br />
            <br />
            Own cronjob-calls, exceeding a quarter of an hour will be blocked.<br />
            <br />
            <br />
            Hint: The config value "Configuration" → "Presets" ...<br />
            <br />
            → "Orderlimit for one day" and<br />
            → "shop stock"<br />
            will be consided.
';
MLI18n::gi()->{'otto_config_account_emailtemplate_sender_email'} = 'beispiel@onlineshop.de';
MLI18n::gi()->{'otto_config_account_emailtemplate_subject'} = 'Ihre Bestellung bei #SHOPURL#';
MLI18n::gi()->{'otto_configform_stocksync_values__rel'} = 'Bestellung reduziert Shop-Lagerbestand (empfohlen)';
MLI18n::gi()->{'otto_config_carrier_option_group_marketplace_carrier'} = 'Select Marketplace Supported Carrier:';
MLI18n::gi()->{'otto_config_prepare__field__vat__matching__titledst'} = 'OTTO Market Tax Codes';
MLI18n::gi()->{'otto_configform_sync_values__auto'} = '{#i18n:otto_config_general_autosync#}';
MLI18n::gi()->{'otto_config_order__legend__mwst'} = 'VAT';
MLI18n::gi()->{'otto_config_matching_options'} = 'Matching Options';
MLI18n::gi()->{'otto_config_order__field__importactive__label'} = 'Activate Import';
MLI18n::gi()->{'otto_config_prepare__field__processingtime__help'} = 'Tragen Sie hier ein, wie viele Werktage vom Zeitpunkt der Bestellung durch den Kunden es bis zum Erhalt des Pakets dauert';
MLI18n::gi()->{'otto_config_account__field__token__label'} = 'OTTO API-Token';
MLI18n::gi()->{'otto_configform_orderimport_shipping_values__textfield__textoption'} = '1';
MLI18n::gi()->{'otto_config_free_text_attributes_opt_group'} = 'Champs supplémentaires';
MLI18n::gi()->{'otto_config_prepare__legend__shipping'} = 'Versand';
MLI18n::gi()->{'otto_config_account__field__tabident__help'} = '{#i18n:ML_TEXT_TAB_IDENT#}';
MLI18n::gi()->{'otto_config_producttemplate_content'} = '<p>#TITLE#</p><p>#ARTNR#</p><p>#SHORTDESCRIPTION#</p><p>#PICTURE1#</p><p>#PICTURE2#</p><p>#PICTURE3#</p><p>#DESCRIPTION#</p>';
MLI18n::gi()->{'otto_config_account__legend__account'} = 'Access Data';
MLI18n::gi()->{'otto_config_prepare__field__vat__help'} = 'You must have at least one VAT defined in the shop system';
MLI18n::gi()->{'otto_config_matching_otto_values'} = 'OTTO Market Values';
MLI18n::gi()->{'otto_config_account_orderimport'} = 'Bestellungen';
