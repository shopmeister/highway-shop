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
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLI18n::gi()->add('formfields_otto', array(
    'vat' => array(
        'label' => 'Steuern',
        'hint' => ''
    ),
    'lang' => array(
        'label' => 'Sprache',
        'hint' => ''
    ),
    'imagesize' => array(
        'label' => 'Bildgröße',
        'hint' => ''
    ),
    'delivery' => array(
        'label' => 'Versand',
        'hint' => 'Wenn „Aus Versandprofil übernehmen“ ausgewählt ist, wird die Standardbearbeitungszeit aus dem Versandprofil verwendet. Andernfalls wird diese überschrieben und bleibt unverändert, selbst wenn das Versandprofil geändert wird. Die Bearbeitungszeit gibt an, wie lange der Händler benötigt, um eine Bestellung für den Versand vorzubereiten.<br><br>
                    Hinweis: Die Bearbeitungszeit wird in „Arbeitstagen“ angegeben. Sie beschreibt die Zeit von der Auftragsfreigabe bis zur Übergabe an den Versanddienstleister. Eine „1“ gilt bis zur Cut-Off-Zeit als taggleicher Versand (0 Tage).'

    ),
//    'deliverytype' => array(
//        'label' => 'Art der Zustellung'
//    ),
//    'deliverytime' => array(
//        'label' => 'Lieferzeit in Tagen'
//    ),
    'shippingprofile' => array(
        'label' => 'Versandprofil',
    ),
    'processingtime' => array(
        'label' => 'Bearbeitungszeit',
    ),
    'prepare_title' => array(
        'label' => 'Title',
        'hint' => '',
    ),
    'blacklisting' => array(
        'label' => 'Versandbenachrichtigungen an {#setting:currentMarketplaceName#} Kunden unterdrücken',
        'valuehint' => '{#setting:currentMarketplaceName#} Kunden-E-Mail Adresse blacklisten',
        'help' => '<b>Versandbenachrichtigungen an {#setting:currentMarketplaceName#} Käufer vermeiden</b><br />
                Die Einstellung “{#setting:currentMarketplaceName#} Kunden-E-Mail Adresse blacklisten” dient dazu E-Mails zu blacklisten, die (für über magnalister importierte Bestellungen) aus dem Shopsystem heraus versandt werden. Sie kommen dann beim {#setting:currentMarketplaceName#} Käufer nicht an.<br />
                Wichtige Hinweise:
                <ul>
                    <li>Das Blacklisting ist standardmäßig deaktiviert. Wenn Sie es aktivieren, erhalten Sie in dem Moment einen Mailer Daemon (Information des Mailservers, dass die E-Mail nicht zugestellt werden konnte), wenn durch das Shopsystem eine E-Mail an den eBay Käufer versandt wird.<br /><br /></li>
                    <li>magnalister setzt vor die {#setting:currentMarketplaceName#} E-Mail-Adresse lediglich das Prefix “blacklisted-” (z. B. blacklisted-12345@otto.de). Möchten Sie dennoch mit dem {#setting:currentMarketplaceName#} Käufer Kontakt aufnehmen, entfernen Sie einfach das Prefix “blacklisted-”.</li>
                </ul>'
    ),
    'prepare_description' => array(
        'label' => 'Beschreibung<span class="bull">•</span>',
        'hint' => 'Detaillierte und informative Beschreibung des Produkts mit seinen Spezifikationen und Eigenschaften. Angebotsdetails, Versand- oder Shopinformationen wie Preise, Lieferbedingungen, etc. sind nicht erlaubt. Bitte beachten Sie, dass es nur eine Produktdetailseite pro Produkt gibt, die von allen Verkäufern, die dieses Produkt anbieten, geteilt wird. Fügen Sie keine Hyperlinks, Bilder oder Videos hinzu.<br><br>May contain HTML elements<br><br>Maximal 2000 Zeichen',
        'optional' => array(
            'checkbox' => array(
                'labelNegativ' => 'immer aktuell aus Web-Shop verwenden',
            ),
        )
    ),
    'prepare_image' => array(
        'label' => 'Produktbilder<span class="bull">•</span>',
        'hint' => 'Es muss mindestens ein Bild übermittelt werden',
        'optional' => array(
            'checkbox' => array(
                'labelNegativ' => 'immer aktuell aus Web-Shop verwenden',
            ),
        )
    ),
    'prepare_mainimage' => array(
        'label' => 'Produkt-Hauptbild<span class="bull">•</span>',
        'help' => '<p>W&auml;hlen Sie hier das an Ihrem Shop-Produkt hinterlegte Bild, das als Hauptbild f&uuml;r OTTO verwendet werden soll.</p>
<p></p>
<p>Die &Uuml;bermittlung eines Produkt-Hauptbildes ist f&uuml;r OTTO verpflichtend. Es muss freigestellt und mit wei&szlig;em Hintergrund &uuml;bermittelt werden.</p>
<p></p>
<p>Wenn Sie die Option "Immer aktuell aus dem Web-Shop verwenden" w&auml;hlen, so wird das erste, an Ihrem Shop-Produkt hinterlegte Bild, an OTTO &uuml;bermittelt.</p>
<p></p>
<p>Bitte beachten Sie, dass Sie f&uuml;r bereits an OTTO hochgeladene Produkte einen erneuten Produkt-Upload in magnalister durchf&uuml;hren m&uuml;ssen, damit die &Auml;nderungen zum Produkt-Hauptbild &uuml;bernommen werden.</p>',
        'hint' => '',
        'optional' => array(
            'checkbox' => array(
                'labelNegativ' => 'immer aktuell aus Web-Shop verwenden',
            ),
        )
    ),
    'prepare_saveaction' => array(
        'name' => 'saveaction',
        'type' => 'submit',
        'value' => 'save',
        'position' => 'right',
    ),
    'prepare_resetaction' => array(
        'name' => 'resetaction',
        'type' => 'submit',
        'value' => 'reset',
        'position' => 'left',
    ),
    'freightforwarding' => array(
        'label' => 'Lieferung per Spedition',
        'hint' => 'Geben Sie an, ob Ihr Produkt per Spedition versendet wird.',
    ),
    'orderstatus.open' => array(
        'label' => 'Webshop-Status für “offene” Bestellungen',
        'help' => '
            <p>Der OTTO Market Bestellstatus “offen” bedeutet, dass der Käufer die Ware bereits bezahlt hat. Sie kann also risikofrei versendet werden.</p>
            <p>Wählen Sie hier den entsprechenden Webshop-Bestellstatus, den eine importierte OTTO Market Bestellung mit dem Status “offen” erhalten soll.</p>
        ',
    ),
    'orderimport.paymentstatus' => array(
        'label' => 'Zahlstatus im Shop',
        'help' => 'W&auml;hlen Sie hier, welcher Webshop-Zahlstatus während des magnalister Bestellimports in den Bestelldetails hinterlegt werden soll.'
    ),

    /*
    'paymentmethods' => array(
        'label' => 'Payment Methods',
        'help' => 'Payment method that will apply to all orders imported from OTTO Market. Standard: "OTTO Market"<br><br>
            This setting is necessary for the invoice and shipping notice, and for editing orders later in the Shop or via ERP.'
    ),
    'shippingservice' => array(
        'label' => 'Shipping Service',
        'help' => 'Shipping methods that will be assigned to all OTTO Market orders. Standard: "Marketplace"<br><br>
            This setting is necessary for the invoice and shipping notice, and for editing orders later in the Shop or via ERP.'
    ),*/

    'orderstatus.standardshipping' => array(
        'label' => 'Versanddienstleister für versendete Bestellungen',
        'hint' => 'OTTO Market lässt nur bestimmte Versanddienstleister zu.<br>Bitte achten Sie darauf, dass Sie nur gültige Daten angeben.'
    ),
    'orderstatus.forwardershipping' => array(
        'label' => 'Versanddienstleister (Spedition) für versendete Bestellungen',
        'hint' => 'OTTO Market lässt nur bestimmte Versanddienstleister zu.<br>Bitte achten Sie darauf, dass Sie nur gültige Daten angeben.'
    ),
    'orderstatus.shippedaddress' => array(
        'label' => 'Bestätigen Sie die Versand- und Absenderadresse mit',
        'help' => 'Bestätigen Sie den Versand mit den Webshop-Status und das Lager oder den Ort, von dem die Sendung zur endgültigen Zustellung Versand wird.'
    ),
    'orderstatus.shippedaddress.city' => array(
        'label' => 'Stadt'
    ),
    'orderstatus.shippedaddress.code' => array(
        'label' => 'Land'
    ),
    'orderstatus.shippedaddress.zip' => array(
        'label' => 'Postleitzahl'
    ),
    'orderstatus.carrier' => array(
        'label' => '&nbsp;&nbsp;&nbsp;&nbsp;Spediteur',
        'help' => 'Vorausgew&auml;hlter Spediteur beim Best&auml;tigen des Versandes nach OTTO Market.',
    ),
    'customfield.carrier' => array(
        'label' => 'Versanddienstleister'
    ),
    'return.carrier' => array(
        'label' => 'Versanddienstleister des Retourenschein',
        'hint' => 'OTTO Market lässt nur bestimmte Versanddienstleister zu.<br>Bitte achten Sie darauf, dass Sie nur gültige Daten angeben.'
    ),
    'return.trackingkey' => array(
        'label' => 'Retouren-Sendungsnummer des Retourenschein',
        'hint' => '<span style="color: red">Hinweis: Die Angabe der Retouren-Sendungsnummer ist für die Versandbestätigung bei OTTO verpflichtend!</span>'
    ),
    'trackingkey' => array(
        'label' => 'Send Tracking Key Option',
    ),
    'customfield.trackingnumber' => array(
        'label' => 'Sendungsnummer'
    ),
));

MLI18n::gi()->add('otto_prepare_form', array(
    'field' => array(
        'variationgroups' => array(
            'label' => 'Marktplatz-Kategorie<span class="bull">•</span>',
            'hint' => '',
        ),
        'variationgroups.value' => array(
            'label' => 'Marktplatz-Kategorie:',
        ),
    ),
), false);
