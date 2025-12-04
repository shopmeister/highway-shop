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
MLI18n::gi()->{'general_shopware6_master_sku_migration_options'} =
    array(
        'label' => 'Shopware 5 Master-SKU verwenden',
        'help'  => '<p>Diese Einstellung ist nur für Händler relevant, die bereits mit Shopware 5 Varianten-Artikel über magnalister zu den
    Marktplätzen übermittelt haben:
<ul>
    <li>Wenn Sie die Einstellung <b>nicht aktivieren</b>, werden die in Shopware 6 Produktverwaltung als
        “Master”-Artikel definierten Produkte mit allen zugehörigen Varianten als neue Produkte auf den Marktplätzen
        angelegt.
    </li>
    <li><b>Aktivieren</b> Sie die Einstellung, wird die SKU (Stock Keeping Unit) des “Master”-Artikels von magnalister
        automatisch so angepasst, dass bei einem erneuten Produkt-Upload der bestehende Marktplatz-Artikel aktualisiert
        wird.
    </li>
</ul></p>
<p>
    <b>Hintergrund:</b> Shopware 6 unterscheidet bei der Vergabe einer SKU in “Master”-Artikel und Varianten. Sofern Sie den
    Shopware 6 Migrationsassistenten nutzen um Ihre Produkte von Shopware 5 auf 6 zu migrieren, wird an die SKU des
    “Master”-Artikels ein “M” angehängt (Beispiel SKU: “1234M”). Varianten erhalten diesen Zusatz nicht.
</p><p>
    Die Unterscheidung zwischen “Master” und Variante gibt es bei Shopware 5 nicht. Für einige Marktplätze ist die
    Identifikation eines “Master”-Artikels jedoch relevant. Daher kennzeichnet magnalister beim Produkt-Upload aus
    Shopware 5 eigenständig die SKU der Hauptvariante des Artikels mit dem Zusatz “_Master” (Beispiel: “1234_Master”).
</p><p>
    Bei aktivierter Einstellung “Shopware 5 Master-SKU” wandelt magnalister während des Produkt-Uploads das Suffix “M”
    automatisch in “_Master” um.
</p>
<p><b>Weitere Hinweise:</b>
<ul>
    <li>Der Preis- und Lagerabgleich zwischen Webshop und Marktplätzen von Artikeln, die via magnalister aus Shopware 5
        übermittelt wurden, funktioniert unter Shopware 6 auch dann, wenn diese Einstellung nicht aktiviert ist.
    </li>
    <li>In der Übersicht der Produktvorbereitung, Produktupload und dem Inventarreiter können Sie “Master”-Artikel am
        Zusatz hinter der SKU erkennen.
    </li>
</ul></p>
    '
    );

MLI18n::gi()->{'general_shopware6_flow_skipped'} =
    array(
        'label'     => 'Shopware 6 Flow Builder Unterstützung',
        'valuehint' => 'Flow Builder beim Bestellimport überspringen',
        'hint'      => 'Weitere Informationen im Info-Icon',
        'help'      => 'Aktuell unterstützen wir folgende Events:<br>
* "Bestellung erreicht Status ..." (state_enter.order.state....)<br>
* "Bestellung ist eingegangen" (checkout.order.placed)'
    );



