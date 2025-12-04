<?php
MLI18n::gi()->{'sModuleNameEtsy'} = 'Etsy';
MLI18n::gi()->{'etsy_prepare_description_not_valid'} = 'Die Artikelbeschreibung ist zu lang. Die maximale Anzahl der Zeichen beträgt 63000.';
MLI18n::gi()->{'etsy_prepare_images_not_valid'} = 'Das Bild ist zu groß. Die maximal zulässige Bildgröße beträgt 3000 x 3000 px.';
MLI18n::gi()->{'etsy_prepare_images_not_exist'} = 'Es muss mindestens ein Bild für den Artikel vorhanden sein!';
MLI18n::gi()->{'etsy_prepare_description_not_exist'} = 'Die Artikelbeschreibung ist zu kurz.';
MLI18n::gi()->{'etsy_prepare_price_not_valid'} = 'Minimaler Artikelpreis auf Etsy ist 0.17£.';
MLI18n::gi()->{'etsy_prepare_quantity_not_valid'} = 'Der Bestand für ein Produkt darf nicht größe als 999 sein.';
MLI18n::gi()->{'etsy_prepare_empty_list_processing_profiles'} = 'Bitte erstellen Sie zunächst ein Bearbeitungsprofil in Ihrem Etsy-Konto';

MLI18n::gi()->{'etsy_inventory_listing_status_new'} = 'Produkt wird erstellt';
MLI18n::gi()->{'etsy_inventory_listing_status_active'} = 'Aktiv';
MLI18n::gi()->{'etsy_inventory_listing_status_inactive'} = 'Inaktiv';
MLI18n::gi()->{'etsy_inventory_listing_status_expired'} = 'Expired';
MLI18n::gi()->{'etsy_inventory_listing_status_draft'} = 'Draft';
MLI18n::gi()->{'etsy_inventory_listing_status_sold_out'} = 'Sold Out';

MLI18n::gi()->{'etsy_checkin_purge_popup_text'} = '
<p><strong>Hinweis:</strong> Sie sind dabei Ihr Inventar auf Etsy vollst&auml;ndig zu ersetzen. Dieser Vorgang l&ouml;scht erst komplett Ihren Marktplatz-Bestand und ersetzt ihn dann mit den hier ausgew&auml;hlten Artikeln.</p>
<p>Etsy berechnet Einstellgeb&uuml;hren f&uuml;r das Erstellen neuer Angebote. F&uuml;r mehr Informationen pr&uuml;fen Sie bitte Ihren Etsy H&auml;ndler-Tarif.</p>
<p>Wollen Sie wirklich fortfahren?</p>
';
MLI18n::gi()->{'etsy_checkin_popup_text'} = '<p><strong>Bitte beachten Sie:</strong> Etsy berechnet Einstellgeb&uuml;hren f&uuml;r das Erstellen neuer Angebote. F&uuml;r mehr Informationen pr&uuml;fen Sie bitte Ihren Etsy H&auml;ndler-Tarif.</p>';

MLI18n::gi()->{'ML_ETSY_PROCESSING_PROFILE_UPDATE_HINT'} = '<h3>Wichtiger Hinweis nach dem Update: Bearbeitungsprofile erforderlich</h3>
    <p><strong>Warum ist dieser Schritt notwendig?</strong></p>
    <p>Etsy verlangt nun f&uuml;r alle Artikel verpflichtend die Angabe von Bearbeitungsprofilen (Processing Profiles). Diese Profile enthalten wichtige Informationen wie:</p>
    <ul>
        <li>Werden Ihre Artikel erst nach Bestellung hergestellt oder sind sie sofort versandbereit? Diese Information hilft K&auml;ufern einzusch&auml;tzen, wie schnell sie mit dem Versand rechnen k&ouml;nnen.</li>
        <li>Minimale und maximale Bearbeitungszeit</li>
    </ul>
    <p><strong>Was m&uuml;ssen Sie jetzt tun?</strong></p>
    <ol>
        <li>Erstellen Sie Bearbeitungsprofile direkt bei Etsy:<br>
            &rarr; <a href="https://www.etsy.com/your/shops/me/tools/shipping-profiles" target="_blank">https://www.etsy.com/your/shops/me/tools/shipping-profiles</a><br>
            oder im Etsy-Portal unter <strong>Einstellungen &rarr; Versandeinstellungen</strong></li>
        <li>Warten Sie einige Minuten, bis die Profile synchronisiert sind</li>
        <li>Aktualisieren Sie die magnalister-Seite (F5)</li>
        <li>Gehen Sie zu <strong>Konfiguration &rarr; Artikelvorbereitung &rarr; Bearbeitungsprofile</strong> und w&auml;hlen Sie Ihr Standard-Profil</li>
        <li>Danach k&ouml;nnen Sie Ihre Artikel erneut vorbereiten und hochladen</li>
    </ol>
    <p><strong>Status Ihrer Artikel:</strong> Alle bestehenden Artikel haben den Status "Vorbereitung erneut erforderlich" erhalten und m&uuml;ssen nach der Konfiguration des Bearbeitungsprofils neu vorbereitet werden, bevor sie hochgeladen werden k&ouml;nnen.</p>
    <p><em>Sie k&ouml;nnen diese Nachricht durch Klicken auf die Schaltfl&auml;che Schlie&szlig;en ausblenden.</em></p>';
