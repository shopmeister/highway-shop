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
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLI18n::gi()->ebay_config_producttemplate_content =
'<style>
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
<div>#PROPERTIES#</div>';

MLI18n::gi()->add('ebay_config_orderimport', array(
     'field' => array(
         'updateablepaymentstatus' => array(
             'label' => 'Zahl-Status-&Auml;nderung zulassen wenn',
             'help' => 'Stati der Bestellungen, die bei eBay-Zahlungen ge&auml;ndert werden d&uuml;rfen.
			                Wenn die Bestellung einen anderen Status hat, wird er bei eBay-Zahlungen nicht ge&auml;ndert.<br /><br />
			                Wenn Sie gar keine &Auml;nderung des Zahlstatus bei eBay-Zahlung w&uuml;nschen, deaktivieren Sie die Checkbox.',
         ),
        'paidstatus'=> array(
            'label' => 'Bestell-/Zahlstatus für bezahlte eBay Bestellungen',
            'help' => '<p>eBay-Bestellungen werden vom Käufer teils zeitverzögert bezahlt.
<br><br>
Damit Sie nicht-bezahlte Bestellungen von bezahlten Bestellungen trennen zu können, können Sie hier für bezahlte eBay-Bestellungen einen eigenen Webshop Bestellstatus, sowie einen Zahlstatus wählen.
<br><br>
Wenn Bestellungen von eBay importiert werden, die noch nicht bezahlt sind, so greift der Bestellstatus, den Sie oben unter "Bestellimport" > "Bestellstatus im Shop" festgelegt haben." 
<br><br>
Wenn Sie oben “Nur bezahlt-markierte Bestellungen importieren” aktiviert haben, wird ebenfalls der “Bestellstatus im Shop” von oben verwendet. Die Funktion hier ist in dem Fall dann ausgegraut.
'
        ),
        'orderstatus.paid' => array(
            'label' => 'Bestellstatus',
            'help' => '',
        ),
        'paymentstatus.paid' => array(
            'label' => 'Zahlstatus',
            'help' => '',
        ),
        'updateable.paymentstatus' => array(
            'label' => '',
            'help' => '',
        ),
        'update.paymentstatus' => array(
            'label' => 'Status-&Auml;nderung aktiv',
        ),
        'orderimport.paymentmethod' => array(
            'label' => '{#i18n:formfields_orderimport.paymentmethod_label#}',
            'help' => '{#i18n:shopware6_configuration_paymentmethod_help#}',
            'hint' => '',
        ),
        'orderimport.shippingmethod' => array(
            'label' => '{#i18n:formfields_orderimport.shippingmethod_label#}',
            'help' => '{#i18n:shopware_marketplace_configuration_shippingmethod_withfrommarketplace_help#}',
            'hint' => '',
        ),
        'orderimport.paymentstatus' => array(
            'label' => 'Zahlstatus im Shop',
            'hint' => '',
        ),
    ),
), true);

MLI18n::gi()->{'ebay_config_orderimport__field__orderstatus.open__help'} = '
                Legen Sie hier den Bestellstatus im Webshop fest, den eine von eBay neu eingegangene Bestellung automatisch bekommen soll.
<br><br>
Bitte beachten Sie, dass hierbei sowohl bezahlte, als auch nicht bezahlte eBay Bestellungen importiert werden.
<br><br>
Sie können jedoch in der folgenden Funktion "Nur bezahlt-markierte Bestellungen importieren" festlegen, ausschließlich bezahlte eBay-Bestellungen in Ihren Webshop übernehmen zu lassen. 
<br><br><br>

Für bezahlte eBay-Bestellungen können Sie einen eigenen Bestellstatus weiter unten, bei "Bestellstatus-Synchronisation" > "Bestell-/Zahlstatus für bezahlte eBay Bestellungen" festlegen.
            ';
MLI18n::gi()->add('ebay_config_producttemplate', array(
    'field' => array(
        'template.content' => array(
            'label' => 'Template Produktbeschreibung',
            'hint' => '
Liste verf&uuml;gbarer Platzhalter f&uuml;r die Produktbeschreibung:
<dl>
    <dt>#TITLE#</dt>
        <dd>Produktname (Titel)</dd>
    <dt>#ARTNR#</dt>
        <dd>Artikelnummer im Shop</dd>
    <dt>#PID#</dt>
        <dd>Produkt ID im Shop</dd>
    <!--<dt>#PRICE#</dt>
            <dd>Preis</dd>
    <dt>#VPE#</dt>
            <dd>Preis pro Verpackungseinheit</dd>-->
    <dt>#SHORTDESCRIPTION#</dt>
        <dd>Kurzbeschreibung aus dem Shop</dd>
    <dt>#DESCRIPTION#</dt>
        <dd>Beschreibung aus dem Shop</dd><br>
    <dt>#MOBILEDESCRIPTION#</dt>
        <dd>Kurzbeschreibung für mobile Ger&auml;te, falls hinterlegt</dd><br>
    <dt>#PICTURE1#</dt>
        <dd>erstes Produktbild</dd><br>
    <dt>#PICTURE2# usw.</dt>
            <dd>zweites Produktbild; mit #PICTURE3#, #PICTURE4# usw. k&ouml;nnen weitere Bilder &uuml;bermittelt werden, so viele wie im Shop vorhanden.</dd>'
        .'<dt>#PROPERTIES#</dt>'
        .'<dd>Eine Liste aller Produkteigenschaften des Produktes. Aussehen kann &uuml;ber CSS gesteuert werden (siehe Code vom Standard Template)</dd>'.
        '</dl>',
        ),
    ),
), true);
MLI18n::gi()->{'ebay_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->set('ebay_config_producttemplate__field__template.content__hint', MLI18n::gi()->{'ebay_prepare_apply_form__field__description__hint'}, true);
MLI18n::gi()->set('ebay_prepare_apply_form_field_description_hint_customfield', '<dt>Zusatzfelder:</dt><dt>#LABEL_{Technischer Name}# #VALUE_{Technischer Name}#</dt><dt>Z. B.</dt>', true);
