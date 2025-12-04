<?php

MLI18n::gi()->{'formfields_obi__deliverytype__label'} = 'Delivery type';
MLI18n::gi()->{'formfields_obi__processingtime__hint'} = 'Tragen Sie hier ein, wie viele Werktagen Sie zur Bearbeitung der Bestellung brauchen (vom Bestelleingang bis zum Versand der Ware).';
MLI18n::gi()->{'formfields_obi__orderstatus.shippedaddress.city__label'} = 'City';
MLI18n::gi()->{'formfields_obi__imagesize__hint'} = '';
MLI18n::gi()->{'formfields_obi__vat__label'} = 'VAT';
MLI18n::gi()->{'formfields_obi__orderstatus.open__label'} = 'Statut des commandes “en cours” dans la boutique';
MLI18n::gi()->{'formfields_obi__orderstatus.shippedaddress.zip__label'} = 'ZIP Code';
MLI18n::gi()->{'formfields_obi__return.trackingkey__label'} = 'Return Tracking Key Option';
MLI18n::gi()->{'formfields_obi__prepare_image__hint'} = 'A minimum of 1 product images';
MLI18n::gi()->{'formfields_obi__delivery__label'} = 'Delivery';
MLI18n::gi()->{'formfields_obi__orderstatus.canceled__help'} = '
            Sélectionnez ici, le statut boutique, qui transmettra automatiquement le statut "Commande annulée" à l\'{#setting:currentMarketplaceName#}.<br />
            <br />
            Remarque: Dans le cadre de commandes groupées, l\'annulation partielle n\'est pas possible. Cette fonction annulera toute la commande.
        ';
MLI18n::gi()->{'formfields_obi__prepare_image__label'} = 'Produktbilder<span class="bull">•</span>';
MLI18n::gi()->{'formfields_obi__shippingtype__label'} = 'Shipping type';
MLI18n::gi()->{'formfields_obi__return.carrier__hint'} = 'OBI.de only allows certain carriers.Please make sure to provide valid data only.';
MLI18n::gi()->{'formfields_obi__customfield.trackingnumber__label'} = 'Tracking number';
MLI18n::gi()->{'formfields_obi__lang__hint'} = '';
MLI18n::gi()->{'formfields_obi__deliverytime__label'} = 'Delivery time in days';
MLI18n::gi()->{'formfields_obi__imagesize__label'} = 'Image Size';
MLI18n::gi()->{'formfields_obi__orderstatus.cancelreason__help'} = 'Pour annuler une commande chez OBI, il faut donner un motif.';
MLI18n::gi()->{'formfields_obi__orderstatus.forwardershipping__label'} = 'Forwarding Carrier Option';
MLI18n::gi()->{'formfields_obi__orderstatus.shippedaddress.code__label'} = 'Country Code';
MLI18n::gi()->{'formfields_obi__freightforwarding__hint'} = 'Geben Sie an, ob Ihr Produkt per Spedition versendet wird.';
MLI18n::gi()->{'formfields_obi__orderstatus.standardshipping__hint'} = 'OBI.de only allows certain carriers.Please make sure to provide valid data only.';
MLI18n::gi()->{'formfields_obi__orderstatus.shippedaddress__label'} = 'Confirm Shipping and \'From\' Address';
MLI18n::gi()->{'formfields_obi__customfield.carrier__label'} = 'Carrier';
MLI18n::gi()->{'formfields_obi__vat__hint'} = '';
MLI18n::gi()->{'formfields_obi__shippingtype__help'} = 'Enter witch shipping type. Available values PARCEL and FORWARDER';
MLI18n::gi()->{'formfields_obi__prepare_title__hint'} = '';
MLI18n::gi()->{'formfields_obi__orderimport.paymentstatus__help'} = '<p>OBI Market does not assign any shipping method to imported orders.</p>
            <p>Please choose here the available Web Shop shipping methods. The contents of the drop-down menu can be assigned in Shopware > Settings > Shipping Costs.</p>
            <p>This setting is important for bills and shipping notes, the subsequent processing of the order inside the shop, and for some ERPs.</p>';
MLI18n::gi()->{'formfields_obi__deliverytime__help'} = '
                    <p>Hier k&ouml;nnen Sie die Lieferzeit festlegen, die w&auml;hrend der Preis-Lager-Synchronisation an den OBI-Marktplatz &uuml;bermittelt wird. Die &Uuml;bermittlung eines Wertes ist seitens OBI verpflichtend.</p>
                    <p>Sie haben folgende Optionen:</p>
                    <ul>
                        <li aria-level="1">
                            <p><strong>Attributsmatching<br><br></strong>Bitte w&auml;hlen Sie hier das Feld aus Ihrem Shopsystem, aus dem die Lieferzeit &uuml;bernommen werden soll. Beachten Sie, dass nicht jedes Shopsystem ein Standardfeld f&uuml;r die Lieferzeit vorsieht. Sie m&uuml;ssen daher ggf. zuerst ein Meta- bzw. Freitextfeld in Ihrem Shopsystem anlegen und in den Produktdetails pflegen.<br><br><strong>Wichtiger Hinweis</strong>: Erlaubte Werte f&uuml;r das Lieferzeitfeld sind nur ganze Tage (Beispiel: &ldquo;1&rdquo; f&uuml;r einen Tag Lieferzeit, &ldquo;2&rdquo; f&uuml;r zwei Tage Lieferzeit usw.)<br><br></p>
                        </li>
                    </ul>
                    <ul>
                        <li aria-level="1">
                            <p><strong>Standardwert<br><br></strong>M&ouml;chten Sie das Attributsmatching nicht verwenden, so belassen Sie unter&nbsp; &ldquo;Attributsmatching&rdquo; die Auswahl bei &ldquo;Kein Matching&rdquo;.<br><br>Nun k&ouml;nnen Sie im rechten Dropdown unter &ldquo;Standardwert&rdquo; die gew&uuml;nschte Lieferzeit ausw&auml;hlen (1 - 99 Tage), die dann f&uuml;r alle Produkte &uuml;bernommen wird. <br><br>Zus&auml;tzlicher Hinweis: Der Standardwert wird auch dann verwendet, wenn ein Matching unter &ldquo;Attributsmatching&rdquo; konfiguriert ist, aber im entsprechenden Feld im Shopsystem kein Wert eingetragen ist.</p>
                        </li>
                    </ul>';
MLI18n::gi()->{'formfields_obi__orderstatus.shipping__help'} = 'Here you set the shop status which will set the {#setting:currentMarketplaceName#} order status to „shipped order“.';
MLI18n::gi()->{'formfields_obi__return.carrier__label'} = 'Return Carrier Option';
MLI18n::gi()->{'formfields_obi__orderstatus.carrier__help'} = 'Pre-selected freight forwarder confirming shipment to OBI Market.';
MLI18n::gi()->{'formfields_obi__orderstatus.shippedaddress.status__label'} = 'Order Status';
MLI18n::gi()->{'formfields_obi__prepare_description__optional__checkbox__labelNegativ'} = 'immer aktuell aus Web-Shop verwenden';
MLI18n::gi()->{'formfields_obi__orderimport.paymentstatus__label'} = 'Payment Status (Webshop)';
MLI18n::gi()->{'obi_prepare_form__field__variationgroups__hint'} = '';
MLI18n::gi()->{'formfields_obi__prepare_description__label'} = 'Beschreibung<span class="bull">•</span>';
MLI18n::gi()->{'formfields_obi__prepare_title__label'} = 'Title';
MLI18n::gi()->{'formfields_obi__orderstatus.open__help'} = '
            <p>Le statut OBI Market “en cours” signifie que la commande a été payée et qu’elle peut donc être expédiée. Sélectionnez ici le statut que les commandes “en cours” doivent recevoir dans votre boutique.</p>
        ';
MLI18n::gi()->{'formfields_obi__processingtime__label'} = 'Bearbeitungszeit in Werktagen';
MLI18n::gi()->{'formfields_obi__prepare_description__hint'} = 'Detaillierte und informative Beschreibung des Produkts mit seinen Spezifikationen und Eigenschaften. Angebotsdetails, Versand- oder Shopinformationen wie Preise, Lieferbedingungen, etc. sind nicht erlaubt. Bitte beachten Sie, dass es nur eine Produktdetailseite pro Produkt gibt, die von allen Verkäufern, die dieses Produkt anbieten, geteilt wird. Fügen Sie keine Hyperlinks, Bilder oder Videos hinzu.<br><br>May contain HTML elements<br><br>Maximal 2000 Zeichen';
MLI18n::gi()->{'formfields_obi__deliverytime_default__label'} = 'Default value';
MLI18n::gi()->{'formfields_obi__orderstatus.carrier__label'} = 'Carrier';
MLI18n::gi()->{'formfields_obi__orderstatus.standardshipping__label'} = 'Send Carrier Option';
MLI18n::gi()->{'formfields_obi__orderstatus.forwardershipping__hint'} = 'OBI.de only allows certain carriers.Please make sure to provide valid data only.';
MLI18n::gi()->{'formfields_obi__freightforwarding__label'} = 'Lieferung per Spedition';
MLI18n::gi()->{'formfields_obi__orderstatus.shippedaddress__help'} = 'Confirm Shipping status and the warehouse or location from which the shipment will be picked up for final delivery.';
MLI18n::gi()->{'obi_prepare_form__field__variationgroups__label'} = 'Marktplatz-Kategorie<span class="bull">•</span>';
MLI18n::gi()->{'formfields_obi__orderstatus.shipping__label'} = 'Confirm Shipping with';
MLI18n::gi()->{'formfields_obi__trackingkey__label'} = 'Send Tracking Key Option';
MLI18n::gi()->{'obi_prepare_form__field__variationgroups.value__label'} = 'Marktplatz-Kategorie:';
MLI18n::gi()->{'formfields_obi__lang__label'} = 'Language';
MLI18n::gi()->{'formfields_obi__orderstatus.cancelreason__label'} = 'Annuler une commande - Motif';
MLI18n::gi()->{'formfields_obi__orderstatus.canceled__label'} = 'Confirm Canceled with';
MLI18n::gi()->{'formfields_obi__prepare_image__optional__checkbox__labelNegativ'} = 'immer aktuell aus Web-Shop verwenden';
