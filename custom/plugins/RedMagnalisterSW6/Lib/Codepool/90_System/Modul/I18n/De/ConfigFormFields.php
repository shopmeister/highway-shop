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

MLI18n::gi()->configform_quantity_values = array(
    'stock' => array(
        'title' => 'Shop Lagerbestand &uuml;bernehmen',
        'textoption' => false
    ),
    'stocksub' => array(
        'title' => 'Shop Lagerbestand &uuml;bernehmen abzgl. Wert aus rechten Feld',
        'textoption' => true
    ),
    'lump' => array(
        'title' => 'Pauschal (aus rechtem Feld)',
        'textoption' => true
    ),
);
MLI18n::gi()->configform_price_addkind_values = array(
    'percent' => 'x% Shop-Preis Auf-/Abschlag',
    'addition' => 'x  Shop-Preis Auf-/Abschlag',
);

MLI18n::gi()->configform_sync_value_auto = 'Automatische Synchronisierung per CronJob (empfohlen)';
MLI18n::gi()->configform_sync_value_fast = 'Schnellere automatische Synchronisation cronjob (auf 15 Minuten)';
MLI18n::gi()->configform_sync_value_no = 'Keine Synchronisierung';

MLI18n::gi()->configform_sync_values = array(
    'auto' => '{#i18n:configform_sync_value_auto#}',
    'no' => '{#i18n:configform_sync_value_no#}',
);

MLI18n::gi()->configform_fast_sync_values = array(
    'auto' => '{#i18n:configform_sync_value_auto#}',
    //'auto_fast' => '{#i18n:configform_sync_value_fast#}',
    'no' => '{#i18n:configform_sync_value_no#}',
);

MLI18n::gi()->configform_stocksync_values = array(
    'rel' => 'Bestellung reduziert Shop-Lagerbestand (empfohlen)',
    'no' => '{#i18n:configform_sync_value_no#}',
);
MLI18n::gi()->{'configform_price_field_strikeprice_label'} = 'Durchgestrichener {#setting:currentMarketplaceName#}-Preis (Streichpreis)';
MLI18n::gi()->{'configform_price_field_strikeprice_help'} = '<p>Die {#setting:currentMarketplaceName#} Streichpreis-Funktion bietet die Darstellung von Aktionspreisen oder unverbindliche Preisempfehlungen (UVP). Ein durchgestrichener Preis wird neben dem finalen Verkaufspreis angezeigt.</p>
<p><b>Wichtige Hinweise:</b></p>
<ul>
<li>Wenn der Streichpreis niedriger als der Verkaufspreis ist, wird kein Streichpreis übermittelt.</li>
<li>Streichpreise werden im magnalister Plugin in den Produktübersichten mit rot durchgestrichenem Preis neben dem Verkaufspreis angezeigt.</li>
<li>Nach den {#setting:currentMarketplaceName#}-Regeln muss der ursprüngliche Originalpreis tatsächlich früher im Shop oder auf {#setting:currentMarketplaceName#} verwendet worden, bzw. eine UVP des Herstellers sein.</li>
</ul>';
MLI18n::gi()->{'configform_price_field_strikeprice_signal_label'} = 'Nachkommastelle';
MLI18n::gi()->{'configform_price_field_strikeprice_signal_help'} = '
                Dieses Textfeld wird beim &Uuml;bermitteln der Daten zu {#setting:currentMarketplaceName#} als Nachkommastelle an Ihrem Preis &uuml;bernommen.<br/><br/>
                <strong>Beispiel:</strong> <br />
                Wert im Textfeld: 99 <br />
                Preis-Ursprung: 5.58 <br />
                Finales Ergebnis: 5.99 <br /><br />
                Die Funktion hilft insbesondere bei prozentualen Preis-Auf-/Abschl&auml;gen.<br/>
                Lassen Sie das Feld leer, wenn Sie keine Nachkommastelle &uuml;bermitteln wollen.<br/>
                Das Eingabe-Format ist eine ganzstellige Zahl mit max. 2 Ziffern.';
MLI18n::gi()->{'configform_price_field_strikeprice_signal_hint'} = 'Nachkommastelle';
MLI18n::gi()->{'configform_price_field_priceoptions_label'} = 'Verkaufspreis aus Kundengruppe';
MLI18n::gi()->{'configform_price_field_priceoptions_kind_label'} = 'Der durchgestrichene Preis auf {#setting:currentMarketplaceName#} entspricht';
MLI18n::gi()->{'configform_price_field_priceoptions_help'} = '<p>Mit dieser Funktion k&ouml;nnen Sie abweichende Preise zu {#setting:currentMarketplaceName#} &uuml;bergeben und automatisch synchronisieren lassen.</p>
<p>Wählen Sie dazu über das nebenstehende Dropdown eine Kundengruppe aus Ihrem Webshop. </p>
<p>Wenn Sie keinen Preis in der neuen Kundengruppe eintragen, wird automatisch der Standard-Preis aus dem Webshop verwendet. Somit ist es sehr einfach, auch für nur wenige Artikel einen abweichenden Preis zu hinterlegen. Die übrigen Konfigurationen zum Preis finden ebenfalls Anwendung.</p>
<p><b>Anwendungsbeispiel:</b></p>
<ul>
<li>Hinterlegen Sie in Ihrem Web-Shop eine Kundengruppe z.B. "{#setting:currentMarketplaceName#}-Kunden"</li>
<li>F&uuml;gen Sie in Ihrem Web-Shop an den Artikeln in der neuen Kundengruppe die gew&uuml;nschten Preise ein.</li>
 </ul>';


MLI18n::gi()->configuration_price_field_exchangerate_help = '<strong>Grundsätzlich:</strong>
<p>
Wenn die Standardwährung des Webshops von der Währung des Marktplatzes abweicht, verwendet magnalister beim Import von Bestellungen und beim Hochladen von Artikeln den im Webshop hinterlegten Wechselkurs.
</p>
<p>
<strong>Wichtig</strong>: Die benötigte Währung muss im Webshop angelegt sein. Sollte die Währung eines Marktplatzes in der Währungskonfiguration des Web-Shops nicht existieren, gibt magnalister beim Bestellimport eine Fehlermeldung aus und die Bestellung kann nicht importiert werden.
</p>
<p>
<strong>Beispiel</strong>: Wenn Sie Kaufland CZ nutzen, muss die Währung "CZK" (Tschechische Krone) in Ihrem Webshop angelegt sein, damit Bestellungen importiert werden können.
</p>
<strong>Achtung:</strong>
<p>
Durch Aktivieren dieser Funktion hier wird der im Web-Shop hinterlegte Wechselkurs mit dem aktuellen Kurs der European Central Bank (EZB) aktualisiert (einmal täglich).
<u>Dadurch werden die Preise in Ihrem Webshop mit dem aktualisierten Wechselkurs angezeigt.</u>
</p>
<p>
Folgende Funktionen lösen die Aktualisierung aus:
<ul>
<li>Bestellimport</li>
<li>Artikel-Vorbereitung</li>
<li>Artikel-Upload</li>
<li>Lager-/Preis-Synchronisation</li>
</ul>
</p>';
// E-Mail Template placeholders
MLI18n::gi()->{'configform_emailtemplate_legend'} = 'E-Mail an K&auml;ufer';
MLI18n::gi()->{'configform_emailtemplate_field_send_label'} = 'E-Mail bei Bestelleingang an Käufer';
MLI18n::gi()->{'configform_emailtemplate_field_send_help'} = 'Soll von Ihrem Shop eine E-Mail an den K&auml;ufer gesendet werden?';
MLI18n::gi()->{'configform_emailtemplate_field_send_hint'} = 'E-Mail versenden?';
MLI18n::gi()->{'generic_prepareform_day'} = 'Tag';
MLI18n::gi()->{'orderstatus_carrier_defaultField_value_shippingname'} = 'Versandart als Spediteur &uuml;bergeben';
MLI18n::gi()->{'marketplace_configuration_orderimport_payment_method_from_marketplace'} = 'Vom Marktplatz übermittelte Zahlart übernehmen';

MLI18n::gi()->{'config_carrier_option_group_marketplace_carrier'} = 'Von {#setting:currentMarketplaceName#} vorgeschlagene Transportunternehmen';
MLI18n::gi()->{'config_carrier_matching_title_shop_carrier'} = 'Versanddienstleister aus Webshop Versandkosten-Modul';
MLI18n::gi()->{'config_carrier_option_group_shopfreetextfield_option_carrier'} = 'Transportunternehmen aus einem Webshop-Freitextfeld (Bestellungen) wählen';
MLI18n::gi()->{'config_carrier_option_matching_option_carrier'} = 'Von {#setting:currentMarketplaceName#} vorgeschlagene Transportunternehmen mit Versanddienstleistern aus Webshop Versandkosten-Modul matchen';
MLI18n::gi()->{'config_carrier_option_orderfreetextfield_option'} = 'magnalister fügt ein Freitextfeld in den Bestelldetails hinzu';
MLI18n::gi()->{'config_carrier_option_freetext_option_carrier'} = 'Transportunternehmen pauschal aus Textfeld übernehmen';
MLI18n::gi()->{'config_carrier_option_group_additional_option'} = 'Zusätzliche Optionen';
MLI18n::gi()->{'config_carrier_matching_title_marketplace_carrier'} = 'Von {#setting:currentMarketplaceName#} vorgeschlagene Transportunternehmen';
MLI18n::gi()->{'config_carrier_option_group_marketplace_carrier'} = 'Von {#setting:currentMarketplaceName#} vorgeschlagene Transportunternehmen';
MLI18n::gi()->config_use_shop_value = 'Vom Shop übernehmen';