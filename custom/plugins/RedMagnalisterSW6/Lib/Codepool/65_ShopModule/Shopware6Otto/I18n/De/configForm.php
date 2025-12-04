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

MLI18n::gi()->{'otto_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';

//MLI18n::gi()->{'formfields__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->add('formfields', array(
    'orderimport.paymentstatus' => array(
        'label' => 'Zahlstatus im Shop',
        'help' => '<p>Otto &uuml;bergibt beim Bestellimport keine Information der Versandart.</p>
<p>W&auml;hlen Sie daher bitte hier die verf&uuml;gbaren Web-Shop-Versandarten. Die Inhalte aus dem Drop-Down k&ouml;nnen Sie unter Shopware > Einstellungen > Versandkosten definieren.</p>
<p>Diese Einstellung ist wichtig f&uuml;r den Rechnungs- und Lieferscheindruck, und f&uuml;r die nachtr&auml;gliche Bearbeitung der Bestellung im Shop, sowie in Warenwirtschaften.</p>',
        'hint' => '',
    ),
), true);
MLI18n::gi()->{'sOtto_automatically'} = '-- allocate automatically --';
MLI18n::gi()->{'otto_config_free_text_attributes_opt_group'} = 'Zusatzfelder';
MLI18n::gi()->{'formfields__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'formfields__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'formfields__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__hint'} = '';

MLI18n::gi()->{'formfields_otto__return.carrier__help'} = '<strong>Option: "{#i18n:otto_config_free_text_attributes_opt_group_value#}"</strong>
<p>magnalister fügt ein Feld unter “Bestell-Details” in den Bestellungen hinzu, um Retourensendungsdaten wie Sendungscode und Carrier einzutragen.</p>
<p>Das Modul <a href="https://store.shopware.com/de/appli31539763616m/dpd-paketversand-mit-sendungsverfolgung-mydpd.html" target="_blank">DPD Paketversand mit Sendungsverfolgung (MyDPD)</a> unterstützt die automatische Befüllung dieser Felder, wodurch die Retourenbearbeitung noch einfacher wird.</p>';
MLI18n::gi()->{'formfields_otto__return.trackingkey__help'} = '{#i18n:formfields_otto__return.carrier__help#}';
