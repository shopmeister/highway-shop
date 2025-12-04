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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLI18n::gi()->add('cdiscount_config_orderimport', array(
    'field' => array(
        'orderimport.paymentmethod' => array(
            'label' => '{#i18n:formfields_orderimport.paymentmethod_label#}',
            'help' => '{#i18n:shopware6_configuration_paymentmethod_help#}',
            'hint' => '',
        ),
        'orderimport.shippingmethod' => array(
            'label' => '{#i18n:formfields_orderimport.shippingmethod_label#}',
            'help' => '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}',
            'hint' => '',
        ),
        'orderimport.paymentstatus' => array(
            'label' => 'Zahlstatus im Shop',
            'help' => '<p>Cdiscount &uuml;bergibt beim Bestellimport keine Information der Versandart.</p>
<p>W&auml;hlen Sie daher bitte hier die verf&uuml;gbaren Web-Shop-Versandarten. Die Inhalte aus dem Drop-Down k&ouml;nnen Sie unter Shopware > Einstellungen > Versandkosten definieren.</p>
<p>Diese Einstellung ist wichtig f&uuml;r den Rechnungs- und Lieferscheindruck, und f&uuml;r die nachtr&auml;gliche Bearbeitung der Bestellung im Shop, sowie in Warenwirtschaften.</p>',
            'hint' => '',
        ),
    ),
));

MLI18n::gi()->{'cdiscount_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->sCdiscount_automatically = '-- Automatisch zuordnen --';

MLI18n::gi()->{'cdiscount_config_orderimport__field__orderstatus.carrier__help'} = '
Wählen Sie hier das Transportunternehmen, das den Cdiscount Bestellungen standardmäßig zugeordnet wird.<br>
<br>
Sie haben folgende Optionen:<br>
<ul>
    <li>
        <span class="bold underline">Von Cdiscount vorgeschlagene Transportunternehmen</span>
        <p>Wählen Sie ein Transportunternehmen aus der Dropdown-Liste. Es werden die Unternehmen angezeigt, die von Cdiscount empfohlen werden.<br>
            <br>
            Diese Option bietet sich an, wenn Sie für Cdiscount Bestellungen <strong>immer das gleiche Transportunternehmen nutzen</strong> möchten.
        </p>
    </li>
    <li>
        <span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_carrier#}</span>
        <p>{#i18n:shop_order_attribute_creation_instruction#}<br>
            <br>
            Diese Option bietet sich an, wenn Sie für Cdiscount Bestellungen <strong>unterschiedliche Transportunternehmen</strong> nutzen möchten.
        </p>
    </li>
    <li>
        <span class="bold underline">Von Cdiscount vorgeschlagene Transportunternehmen mit Versanddienstleistern aus dem Webshop Versandkosten-Modul matchen</span>
        <p>Sie können die von Cdiscount empfohlenen Transportunternehmen mit den im Shopware Versandkosten-Modul angelegten Dienstleistern matchen. Über das “+” Symbol können Sie mehrere Matchings vornehmen.<br>
            <br>
            Infos, welcher Eintrag aus dem Shopware Versandkosten-Modul beim Cdiscount Bestellimport verwendet wird, entnehmen Sie bitte dem Info Icon unter “Bestellimport” -> “Versandart der Bestellungen”.<br>
            <br>
            Diese Option bietet sich an, wenn Sie auf <strong>bestehende Versandart-Einstellungen</strong> aus dem <strong>Shopware</strong> Versandkosten-Modul zurückgreifen möchten.<br>
        </p>
    </li>
    <li>
        <span class="bold underline">magnalister fügt ein Freitextfeld in den Bestelldetails hinzu</span>
        <p>Wenn Sie diese Option wählen, fügt magnalister beim Bestellimport ein Feld in den Bestelldetails bei der Shopware Bestellung hinzu. In dieses Feld können Sie das Transportunternehmen eintragen.<br>
            <br>
            Diese Option bietet sich an, wenn Sie für Cdiscount Bestellungen <strong>unterschiedliche Transportunternehmen</strong> nutzen möchten.<br>
        </p>
    </li>
    <li>
        <span class="bold underline">Manuelle Eingabe eines Transportunternehmens für alle Bestellungen in ein magnalister Textfeld</span><br>
        <p>Diese Option bietet sich an, wenn Sie <strong>für alle Cdiscount Bestellungen ein und dasselbe Transportunternehmen manuell hinterlegen</strong> möchten.<br></p>
    </li>
</ul>
<span class="bold underline">Wichtige Hinweise:</span>
<ul>
    <li>Die Angabe eines Transportunternehmens ist für Versandbestätigungen bei Cdiscount verpflichtend.<br><br></li>
    <li>Die Nicht-Übermittlung des Transportunternehmens kann zu einem vorübergehenden Entzug der Verkaufsberechtigung führen.</li>
</ul>
';
