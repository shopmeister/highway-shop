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

MLI18n::gi()->Shopware6_Marketplace_Configuration_SalesChannel_Label = 'Shopware Verkaufskanal';
MLI18n::gi()->Shopware6_Marketplace_Configuration_SalesChannel_Info = '<p>Wählen Sie hier den Shopware Verkaufskanal aus, dem Bestellungen dieses Marktplatzes zugeordnet werden.
Ebenfalls werden aus diesem Verkaufskanal die im magnalister genutzten Versand- und Zahlarten der Bestellungen übernommen.</p>
<p></p>
<p>Die Shopware Verkaufskan&auml;le finden Sie im Shopware Hauptmen&uuml; links unter &ldquo;Verkaufskan&auml;le&rdquo;.</p>
<p></p>
<p>&Ouml;ffnen Sie den gew&uuml;nschten Shopware Verkaufskanal und legen Sie unter &ldquo;Zahlung und Versand&rdquo; die pr&auml;ferierten Zahlungs- und Versandarten an.</p>
<p></p>
<p>Die dort hinterlegten Zahlungs- und Versandarten werden Ihnen dann im magnalister Plugin als Dropdown-Auswahloptionen ausgegeben (siehe n&auml;chste Einstellungen).</p>';
//after ebay new configuration these translation are deprecated , please remove them by test

MLI18n::gi()->Shopware_Ebay_Configuration_Updateable_OrderStatus_Label = 'Bestell-Status-&Auml;nderung zulassen wenn';
MLI18n::gi()->Shopware_Ebay_Configuration_Updateable_PaymentStatus_Label = 'Zahl-Status-&Auml;nderung zulassen wenn';
MLI18n::gi()->Shopware_Ebay_Configuration_Updateable_PaymentStatus_Info = 'Stati der Bestellungen, die bei eBay-Zahlungen ge&auml;ndert werden d&uuml;rfen.
			                Wenn die Bestellung einen anderen Status hat, wird er bei eBay-Zahlungen nicht ge&auml;ndert.<br /><br />
			                Wenn Sie gar keine &Auml;nderung des Zahlstatus bei eBay-Zahlung w&uuml;nschen, deaktivieren Sie die Checkbox.<br /><br />
			                <b>Hinweis:</b> Der Status von zusammengefa&szlig;ten Bestellungen wird nur dann ge&auml;ndert, wenn alle Teile bezahlt sind.';

MLI18n::gi()->Shopware_Ebay_Configuration_ArticleDescriptionTemplate_sDefault = "<style>
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
<p>#MOBILEDESCRIPTION#</p>
<p>#Bezeichnung1# #Zusatzfeld1#</p>
<p>#Bezeichnung2# #Zusatzfeld2#</p>
<div>#PROPERTIES#</div>";
MLI18n::gi()->form_config_orderimport_exchangerate_update_help = '{#i18n:configuration_price_field_exchangerate_help#}';
MLI18n::gi()->form_config_orderimport_exchangerate_update_alert = '<strong>Achtung:</strong>
<p>
Durch Aktivieren wird der im Web-Shop hinterlegte Wechselkurs mit dem aktuellen Kurs aus Yahoo-Finance aktualisiert. 
<u>Dadurch werden auch die Preise in Ihrem Web-Shop mit dem aktualisierten Wechselkurs zum Verkauf angezeigt:</u>
</p><p>
Folgende Funktionen lösen die Aktualisierung aus:
<ul>
<li>Bestellimport</li>
<li>Artikel-Vorbereitung</li>
<li>Artikel-Upload</li>
<li>Lager-/Preis-Synchronisation</li>
</ul>
<p>
';


MLI18n::gi()->shopware6_configuration_paymentmethod_help = '
<p>W&auml;hlen Sie aus dem Dropdown die Zahlart, die allen {#setting:currentMarketplaceName#}-Bestellungen beim Bestellimport zugeordnet werden soll.&nbsp; Zur Auswahl stehen die Zahlarten, die Sie im ausgew&auml;hlten Shopware Verkaufskanal unter &ldquo;Zahlung und Versand&rdquo; angelegt haben.</p>
<p></p>
<p>Weitere Hinweise:</p>
<p></p>
<ul>
<li aria-level="1">
<p>Die Auswahl einer Zahlart ist verpflichtend. W&auml;hlen Sie keine Zahlart aus der Dropdownliste, gibt magnalister eine Fehlermeldung am oberen Bildschirmrand aus, wenn Sie versuchen, die Einstellungen zu speichern.&nbsp;</p>
</li>
</ul>
<p></p>
<ul>
<li aria-level="1">
<p>magnalister gibt ebenfalls eine Fehlermeldung aus, wenn eine im Dropdown-Men&uuml; ausgew&auml;hlte Zahlart nachtr&auml;glich aus dem Shopware Verkaufskanal entfernt wird.</p>
</li>
</ul>
<p></p>
<ul>
<li aria-level="1">
<p>Die Angabe der Zahlart ist unter anderem wichtig f&uuml;r den Rechnungs- und Lieferscheindruck, und f&uuml;r die nachtr&auml;gliche Bearbeitung der Bestellung im Shop, sowie in Warenwirtschaften.</p>
</li>
</ul>';

MLI18n::gi()->Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_label='Verkaufspreis aus Preisregel';
MLI18n::gi()->Shopware6_eBay_Marketplace_Configuration_chinesePriceoptions_label='Preis aus Preisregel';
MLI18n::gi()->Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_help = '<p>Mit dieser Funktion k&ouml;nnen Sie abweichende Preise zu {#setting:currentMarketplaceName#} &uuml;bergeben und automatisch synchronisieren lassen.</p>
<p>Wählen Sie dazu über das nebenstehende Dropdown eine Preisregel aus Ihrem Webshop (siehe unten).</p>
<p>Wenn Sie keinen Preis für die neue Preisregel eintragen, wird automatisch der Standard-Preis aus dem Webshop verwendet. Somit ist es sehr einfach, auch für nur wenige Artikel einen abweichenden Preis zu hinterlegen. Die übrigen Konfigurationen zum Preis finden ebenfalls Anwendung.</p>
<p><b>Anwendungsbeispiel:</b></p>
<ul>
<li>Hinterlegen Sie unter Einstellungen > Shop > Rule Builder Ihrem Web-Shop eine Regel z.B. "{#setting:currentMarketplaceName#}-Kunden"</li>
<li>F&uuml;gen Sie in der Produkt-Ansicht ihres Web-Shops unter "Erweiterte Preise" die gew&uuml;nschten Preise f&uuml;r die Regel ein.</li>
 </ul>';


MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withfrommarketplace_help'} = '<p>W&auml;hlen Sie aus dem Dropdown die Versandart, die allen {#setting:currentMarketplaceName#}-Bestellungen beim Bestellimport zugeordnet werden soll.</p>
<p></p>
<p>Auswahlm&ouml;glichkeiten:</p>
<ul>
<li aria-level="1">
<p>Versandart von {#setting:currentMarketplaceName#} &uuml;bernehmen: magnalister &uuml;bernimmt die Versandart, die der K&auml;ufer auf {#setting:currentMarketplaceName#} gew&auml;hlt hat. Existiert diese Versandart in den Shopware-Versandeinstellungen noch nicht, so wird sie von magnalister dort automatisch angelegt. Dar&uuml;ber hinaus wird die Versandart von magnalister auch im Shopware Verkaufskanal unter &ldquo;Zahlung und Versand&rdquo; automatisch hinzugef&uuml;gt.</p>
</li>
</ul>
<ul>
<li aria-level="1">
<p>Eigene Auswahl einer Versandart: W&auml;hlen Sie eine der nicht ausgegrauten Versandarten aus dem Dropdown, um diese allen Bestellungen zuzuweisen. Zur Auswahl stehen die Versandarten, die Sie im ausgew&auml;hlten Shopware Verkaufskanal unter &ldquo;Zahlung und Versand&rdquo; angelegt haben.</p>
</li>
</ul>
<p>Weitere Hinweise:</p>
<ul>
<li aria-level="1">
<p>Die Auswahl einer Versandart ist verpflichtend. W&auml;hlen Sie keine Versandart aus der Dropdownliste, gibt magnalister eine Fehlermeldung am oberen Bildschirmrand aus, wenn Sie versuchen, die Einstellungen zu speichern.</p>
</li>
</ul>
<ul>
<li aria-level="1">
<p>magnalister gibt ebenfalls eine Fehlermeldung aus, wenn eine im Dropdown-Men&uuml; ausgew&auml;hlte Versandart nachtr&auml;glich aus dem Shopware Verkaufskanal entfernt wird.</p>
</li>
</ul>
<ul>
<li aria-level="1">
<p>Die Angabe der Versandart ist unter anderem wichtig f&uuml;r den Rechnungs- und Lieferscheindruck, und f&uuml;r die nachtr&auml;gliche Bearbeitung der Bestellung im Shop, sowie in Warenwirtschaften.</p>
</li>
</ul>';

MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help'} = '<p>W&auml;hlen Sie aus dem Dropdown die Versandart, die allen {#setting:currentMarketplaceName#}-Bestellungen beim Bestellimport zugeordnet werden soll.</p>
<p></p>
<p>Auswahlm&ouml;glichkeiten:</p>
<ul>
<li aria-level="1">
<p>Eigene Auswahl einer Versandart: W&auml;hlen Sie eine der nicht ausgegrauten Versandarten aus dem Dropdown, um diese allen Bestellungen zuzuweisen. Zur Auswahl stehen die Versandarten, die Sie im ausgew&auml;hlten Shopware Verkaufskanal unter &ldquo;Zahlung und Versand&rdquo; angelegt haben.</p>
</li>
</ul>
<p>Weitere Hinweise:</p>
<ul>
<li aria-level="1">
<p>Die Auswahl einer Versandart ist verpflichtend. W&auml;hlen Sie keine Versandart aus der Dropdownliste, gibt magnalister eine Fehlermeldung am oberen Bildschirmrand aus, wenn Sie versuchen, die Einstellungen zu speichern.</p>
</li>
</ul>
<ul>
<li aria-level="1">
<p>magnalister gibt ebenfalls eine Fehlermeldung aus, wenn eine im Dropdown-Men&uuml; ausgew&auml;hlte Versandart nachtr&auml;glich aus dem Shopware Verkaufskanal entfernt wird.</p>
</li>
</ul>
<ul>
<li aria-level="1">
<p>Die Angabe der Versandart ist unter anderem wichtig f&uuml;r den Rechnungs- und Lieferscheindruck, und f&uuml;r die nachtr&auml;gliche Bearbeitung der Bestellung im Shop, sowie in Warenwirtschaften.</p>
</li>
</ul>';
