<?php
/**
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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLI18n::gi()->add('check24_prepare_form',array(
    'legend' => array(
        'details' => 'Produktdetails',
        'categories' => 'CHECK24 Kategorien',
    ),
    'field' => array(
        'shippingtime' => array(
            'label' => 'Versandzeit<span class="bull">•</span>',
            'hint' => ''
        ),
        'shippingcost' => array(
            'label' => 'Versandkosten<span class="bull">•</span>',
            'hint' => ''
        ),
        'marke' => array(
            'label' => 'Marke <span class="bull">•</span>',
        ),
        'hersteller_name' => array(
            'label' => 'Hersteller: Name <span class="bull">•</span>',
        ),
        'hersteller_strasse_hausnummer' => array(
            'label' => 'Hersteller: Straße und Hausnummer <span class="bull">•</span>',
        ),
        'hersteller_plz' => array(
            'label' => 'Hersteller: PLZ <span class="bull">•</span>',
        ),
        'hersteller_stadt' => array(
            'label' => 'Hersteller: Stadt <span class="bull">•</span>',
        ),
        'hersteller_land' => array(
            'label' => 'Hersteller: Land <span class="bull">•</span>',
        ),
        'hersteller_email' => array(
            'label' => 'Hersteller: E-Mail <span class="bull">•</span>',
        ),
        'hersteller_telefonnummer' => array(
            'label' => 'Hersteller: Telefonnummer',
        ),
        'verantwortliche_person_fuer_eu_name' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: Name <span class="bull">•</span>',
        ),
        'verantwortliche_person_fuer_eu_strasse_hausnummer' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: Straße und Hausnummer <span class="bull">•</span>',
        ),
        'verantwortliche_person_fuer_eu_plz' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: PLZ <span class="bull">•</span>',
        ),
        'verantwortliche_person_fuer_eu_stadt' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: Stadt <span class="bull">•</span>',
        ),
        'verantwortliche_person_fuer_eu_land' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: Land <span class="bull">•</span>',
        ),
        'verantwortliche_person_fuer_eu_email' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: E-Mail <span class="bull">•</span>',
        ),
        'verantwortliche_person_fuer_eu_telefonnummer' => array(
            'label' => 'Verantwortliche Person f&uuml;r EU: Telefonnummer',
        ),
        'delivery' => array(
            'label' => 'Art des Versands',
            'hint' => ''
        ),
        'two_men_handling' => array(
            'label' => 'Lieferung bis zum Aufstellort',
            'hint' => 'Falls Sie kostenlos bis zum Aufstellort liefern, tragen Sie hier &quot;ja&quot; ein, sonst den Aufpreis. Wenn Sie dies nicht anbieten, lassen Sie das Feld leer.'
        ),
        'installation_service' => array(
            'label' => 'Installation des Artikels',
            'hint' => '',
            /*'values' => array (
                 '' => '-',
                 'ja' => '{#i18n:ML_BUTTON_LABEL_YES#}',
            ),*/
        ),
        'removal_old_item' => array(
            'label' => 'Mitnahme des Altger&auml;ts',
            'hint' => 'Bei Speditionsware:<br />Mitnahme des Altger&auml;ts'
        ),
        'removal_packaging' => array(
            'label' => 'Mitnahme der Verpackung',
            'hint' => 'Bei Speditionsware:<br />Mitnahme der Verpackung'
        ),
        'available_service_product_ids' => array(
            'label' => 'Zubuchbare Services',
            'hint' => 'Liste von verf&uuml;gbaren Services (Produkte-Ids aus dem Feed), die in Kombination mit dem Produkt kaufbar sind'
        ),
        'logistics_provider' => array(
            'label' => 'Logistikdienstleister',
            'hint' => 'Logistikdienstleister f&uuml;r das Produkt (z.B. DHL)'
        ),
        'custom_tariffs_number' => array(
            'label' => 'TARIC Nummer',
            'hint' => 'Die TARIC Nummer ist eine europ&auml;ische Zoll-Kennzahl f&uuml;r Waren. Wichtig wenn Sie Waren in die EU importieren, oder aus der EU ausf&uuml;hren.'
        ),
        'return_shipping_costs' => array(
            'label' => 'Kosten f&uuml;r Retoure',
            'hint' => 'Kosten f&uuml;r Retoure bei Geschmacksretouren'
        ),
    )
),false);
MLI18n::gi()->check24_deliverymode_sperrgut = 'Sperrgut';
MLI18n::gi()->check24_deliverymode_spedition = 'Spedition';
MLI18n::gi()->check24_deliverymode_paket = 'Paket';
MLI18n::gi()->check24_deliverymode_warensendung = 'Warensendung';
MLI18n::gi()->check24_deliverymode_eigene_angaben = 'Eigene Angaben';
