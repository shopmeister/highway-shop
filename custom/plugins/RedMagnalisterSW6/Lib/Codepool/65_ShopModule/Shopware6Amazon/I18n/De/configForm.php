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
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_carrier'} = 'Transportunternehmen aus einem Webshop-Zusatzfeld (Bestellungen) wählen';
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod'} = 'Lieferservice aus einem Webshop-Zusatzfeld (Bestellungen) wählen';
MLI18n::gi()->add('amazon_config_orderimport', array(
    'field' => array(
        'orderimport.paymentmethod'     => array(
            'label' => '{#i18n:formfields_orderimport.paymentmethod_label#}',
            'help' => '{#i18n:shopware6_configuration_paymentmethod_help#}',
            'hint'  => '',
        ),
        'orderimport.shippingmethod'    => array(
            'label' => '{#i18n:formfields_orderimport.shippingmethod_label#}',
            'help' => '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}',
            'hint'  => '',
        ),
        'orderimport.paymentstatus'     => array(
            'label' => 'Zahlstatus im Shop',
            'help'  => 'Wählen Sie hier, welcher Webshop-Zahlstatus während des magnalister Bestellimports in den Bestelldetails hinterlegt werden soll.',
            'hint'  => '',
        ),
        'orderimport.fbashippingmethod' => array(
            'label' => 'Versandart der Bestellungen (FBA)',
            'help' => '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}',
            'hint'  => '',
        ),
        'orderimport.fbapaymentstatus'  => array(
            'label' => 'Zahlstatus im Shop (FBA)',
            'help'  => 'Wählen Sie hier, welcher Webshop-Zahlstatus während des magnalister Bestellimports in den Bestelldetails hinterlegt werden soll.',
            'hint'  => '',
        ),
    ),
), true);
MLI18n::gi()->{'amazon_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.carrier__help'} = '
Wählen Sie hier das Transportunternehmen, das den Amazon Bestellungen standardmäßig zugeordnet wird.<br>
<br>
Sie haben folgende Optionen:<br>
<ul>
	<li><span class="bold underline">Von Amazon vorgeschlagene Transportunternehmen</span>
        <p>
        Wählen Sie ein Transportunternehmen aus der Dropdown-Liste. Es werden die Unternehmen angezeigt, die von Amazon empfohlen werden.<br>
        <br>
        Diese Option bietet sich an, wenn Sie für Amazon Bestellungen <strong>immer das gleiche Transportunternehmen nutzen</strong> möchten.</p>
    </li>
    <li><span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_carrier#}</span>
        <p>
        {#i18n:shop_order_attribute_creation_instruction#}<br>
        <br>
        Diese Option bietet sich an, wenn Sie für Amazon Bestellungen <strong>unterschiedliche Transportunternehmen</strong> nutzen möchten.</p>
    </li>
    
    <li><span class="bold underline">Von Amazon vorgeschlagene Transportunternehmen mit Versanddienstleistern aus Webshop Versandkosten-Modul matchen</span>
        <p>
        
        Sie können die von Amazon empfohlenen Transportunternehmen mit den im Shopware Versandkosten-Modul angelegten Dienstleistern matchen. Über das “+” Symbol können Sie mehrere Matchings vornehmen.<br>
        <br>
        Infos, welcher Eintrag aus dem Shopware Versandkosten-Modul beim Amazon Bestellimport verwendet wird, entnehmen Sie bitte dem Info Icon unter “Bestellimport” -> “Versandart der Bestellungen”.<br>
        <br>
        Diese Option bietet sich an, wenn Sie auf <strong>bestehende Versandkosten-Einstellungen</strong> aus dem <strong>Shopware</strong> Versandkosten-Modul zurückgreifen möchten.<br>
        </p>
    </li>
    
    <li><span class="bold underline">magnalister fügt ein Freitextfeld in den Bestelldetails hinzu</span>
        <p>
        Wenn Sie diese Option wählen, fügt magnalister beim Bestellimport ein Feld in den Bestelldetails bei der Shopware Bestellung hinzu. In dieses Feld können Sie das Transportunternehmen eintragen.<br>
        <br>
        Diese Option bietet sich an, wenn Sie für Amazon Bestellungen <strong>unterschiedliche Transportunternehmen</strong> nutzen möchten.<br>
        </p>
    </li>
    <li><span class="bold underline">Manuelle Eingabe eines Transportunternehmens für alle Bestellungen in ein magnalister Textfeld</span>
        <p>
        Wenn Sie in magnalister unter “Transportunternehmen” die Option “Andere” wählen, können Sie im Textfeld rechts daneben manuell den Namen eines Transportunternehmens eingeben.<br>
        <br>
        Diese Option bietet sich an, wenn Sie <strong>für alle Amazon Bestellungen ein und dasselbe Transportunternehmen manuell hinterlegen</strong> möchten.<br>
        </p>
    </li>
</ul>
<span class="bold underline">Wichtige Hinweise:</span>
<ul>
	<li>Die Angabe eines Transportunternehmens ist für Versandbestätigungen bei Amazon verpflichtend.<br><br></li>
	<li>Die Nicht-Übermittlung des Transportunternehmens kann zu einem vorübergehenden Entzug der Verkaufsberechtigung führen.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.shipmethod__help'} = '
Wählen Sie hier den Lieferservice ( = Versandart), der allen Amazon Bestellungen standardmäßig zugeordnet wird.<br>
<br>
Sie haben folgende Optionen:
<ul>
	<li><span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod#}</span>
        <p>
        Wählen Sie einen Lieferservice aus einem Webshop Zusatzfeld.<br>
        <br>
        {#i18n:shop_order_attribute_creation_instruction#}<br>
        <br>
        Diese Option bietet sich an, wenn Sie für Amazon Bestellungen <strong>unterschiedliche Lieferservices</strong> nutzen möchten.<br>
        </p>
    </li>
	<li><span class="bold underline">Lieferservice mit Einträgen aus Webshop Versandkosten-Modul matchen</span>
        <p>
        Sie können einen beliebigen Lieferservice mit den im Shopware Versandkosten-Modul angelegten Einträgen matchen. Über das “+” Symbol können Sie mehrere Matchings vornehmen.<br>
        <br>
        Infos, welcher Eintrag aus dem Shopware Versandkosten-Modul beim Amazon Bestellimport verwendet wird, entnehmen Sie bitte dem Info Icon unter “Bestellimport” -> “Versandart der Bestellungen”.<br>
        <br>
        Diese Option bietet sich an, wenn Sie auf <strong>bestehende Versandkosten-Einstellungen aus</strong> dem <strong>Shopware</strong> Versandkosten-Modul zurückgreifen möchten.<br>
        </p>
    </li>
    <li><span class="bold underline">magnalister fügt ein Freitextfeld in den Bestelldetails hinzu</span>
        <p>
        Wenn Sie diese Option wählen, fügt magnalister beim Bestellimport ein Feld in den Bestelldetails bei der Shopware Bestellung hinzu. In dieses Feld können Sie den Lieferservice eintragen.<br>
        <br>
        Diese Option bietet sich an, wenn Sie für Amazon Bestellungen <strong>unterschiedliche Lieferservices</strong> nutzen möchten.<br>
        </p>
    </li>
	<li><span class="bold underline">Manuelle Eingabe eines Lieferservices für alle Bestellungen in ein magnalister Textfeld</span>
        <p>
    
        Wenn Sie diese Option in magnalister wählen, können Sie im Textfeld rechts daneben manuell den Namen eines Lieferservices eingeben.<br>
        <br>
        Diese Option bietet sich an, wenn Sie <strong>für alle Amazon Bestellungen ein und denselben Lieferservice manuell hinterlegen</strong> möchten.<br>
        </p>
    </li>

</ul>
<span class="bold underline">Wichtige Hinweise:</span>
<ul>
	<li>Die Angabe eines Lieferservices ist für Versandbestätigungen bei Amazon verpflichtend.<br><br></li>
	<li>Die Nicht-Übermittlung des Lieferservices kann zu einem vorübergehenden Entzug der Verkaufsberechtigung führen.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__label'} = 'Rechnungsnummer';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__help'} = '<p>
Wählen Sie hier, ob Sie Ihre Rechnungsnummern von magnalister erzeugen lassen möchten oder ob diese aus einem Shopware Zusatzfeld übernommen werden sollen.
</p><p>
<b>Rechnungsnummern über magnalister erzeugen</b>
</p><p>
magnalister generiert bei der Rechnungserstellung fortlaufende Rechnungsnummern. Sie können ein Präfix definieren, das vor die Rechnungsnummer gesetzt wird. Beispiel: R10000.
</p><p>
Hinweis: Von magnalister erstellte Rechnungen beginnen mit der Nummer 10000.
</p><p>
<b>Rechnungsnummern mit Shopware Zusatzfeld matchen</b>
</p><p>
Bei der Rechnungserstellung wird der Wert aus dem von Ihnen ausgewählten Shopware Zusatzfeld übernommen.
</p><p>
{#i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Wichtig:</b><br/> magnalister erzeugt und übermittelt die Rechnung, sobald die Bestellung als versendet markiert wird. Bitte achten Sie darauf, dass zu diesem Zeitpunkt das Zusatzfeld gefüllt sein muss, da sonst ein Fehler erzeugt wird (Ausgabe im Tab “Fehlerlog”).
<br/><br/>
Nutzen Sie das Zusatzfeld-Matching, ist magnalister nicht für die korrekte, fortlaufende Erstellung von Rechnungsnummern verantwortlich.
</p>
';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumberoption__label'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber.matching__label'} = 'Shopware-Bestellung-Zusatzfelder';

MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__label'} = 'Stornorechnungsnummer';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__help'} = '<p>
Wählen Sie hier, ob Sie Ihre Stornorechnungsnummer von magnalister erzeugen lassen möchten oder ob diese aus einem Shopware Zusatzfeld übernommen werden sollen.
</p><p>
<b>Stornorechnungsnummer über magnalister erzeugen</b>
</p><p>
magnalister generiert bei der Rechnungserstellung fortlaufende Stornorechnungsnummer. Sie können ein Präfix definieren, das vor die Rechnungsnummer gesetzt wird. Beispiel: R10000.
</p><p>
Hinweis: Von magnalister erstellte Rechnungen beginnen mit der Nummer 10000.
</p><p>
<b>Stornorechnungsnummer mit Shopware Zusatzfeld matchen</b>
</p><p>
Bei der Rechnungserstellung wird der Wert aus dem von Ihnen ausgewählten Shopware Zusatzfeld übernommen.
</p><p>
{#i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Wichtig:</b><br/> magnalister erzeugt und übermittelt die Rechnung, sobald die Bestellung als versendet markiert wird. Bitte achten Sie darauf, dass zu diesem Zeitpunkt das Zusatzfeld gefüllt sein muss, da sonst ein Fehler erzeugt wird (Ausgabe im Tab “Fehlerlog”).
<br/><br/>
Nutzen Sie das Zusatzfeld-Matching, ist magnalister nicht für die korrekte, fortlaufende Erstellung von Stornorechnungsnummer verantwortlich.
</p>
';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumberoption__label'} = MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumberoption__label'};
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber.matching__label'} = MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber.matching__label'};

MLI18n::gi()->{'amazon_config_amazonvcsinvoice_invoicenumberoption_values_matching'} = 'Rechnungsnummern mit Zusatzfeld matchen';
MLI18n::gi()->{'amazon_config_amazonvcsinvoice_reversalinvoicenumberoption_values_matching'} = 'Stornorechnungsnummer mit Zusatzfeld matchen';