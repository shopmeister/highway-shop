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

MLI18n::gi()->add('formfields_obi', array(
    'vat' => array(
        'label' => 'VAT',
        'hint' => ''
    ),
    'lang' => array(
        'label' => 'Language',
        'hint' => ''
    ),
    'imagesize' => array(
        'label' => 'Image Size',
        'hint' => ''
    ),
    'delivery' => array(
        'label' => 'Delivery'
    ),
    'deliverytype' => array(
        'label' => 'Delivery type'
    ),
    'deliverytime' => array(
        'label' => 'Delivery time in days',
        'help' => '
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
                    </ul>'
    ),
    'deliverytime_default' => array(
        'label' => 'Default value',
    ),
    'provider' => array(
        'label' => 'Provider'
    ),
    'prepare_title' => array(
        'label' => 'Title',
        'hint' => '',
    ),
    'prepare_description' => array(
        'label' => 'Description<span class="bull">•</span>',
        'hint' => 'Detailed and informative description of the product with its specifications and properties. Offer details, shipping or shop information such as prices, delivery conditions, etc. are not allowed. Please note that there is only one product detail page per product that is shared by all sellers who offer this product. Do not add hyperlinks, images, or videos.<br><br>May contain HTML elements<br><br>A maximum of 2000 characters',
        'optional' => array(
            'checkbox' => array(
                'labelNegativ' => 'Always use the latest from the web shop',
            ),
        )
    ),
    'prepare_image' => array(
        'label' => 'Product images<span class="bull">•</span>',
        'hint' => 'A minimum of 1 product images',
        'optional' => array(
            'checkbox' => array(
                'labelNegativ' => 'Always use the latest from the web shop',
            ),
        )
    ),
    'processingtime' => array(
        'label' => 'Processing time in working days',
        'hint' => 'Enter here how many working days you need to process the order (from the receipt of the order to the dispatch of the goods).',
    ),
    'shippingtype' => array(
        'label' => 'Shipping type',
        'help' => 'Enter witch shipping type. Available values PARCEL and FORWARDER',
    ),
    'freightforwarding' => array(
        'label' => 'Lieferung per Spedition',
        'hint' => 'Geben Sie an, ob Ihr Produkt per Spedition versendet wird.',
    ),
    'orderstatus.open' => array(
        'label' => 'Processable Order Status (Webshop)',
        'help' => '
            <p>The OBI order status „processable" means that the Buyer has already paid for the items. It can therefore be shipped without risk.</p>
            <p>Select the corresponding shop order status that an imported OBI order with the status „processable" should receive.</p>
        ',
    ),
    'orderimport.paymentstatus' => array(
        'label' => 'Payment Status (Webshop)',
        'help' => '<p>OBI does not assign any shipping method to imported orders.</p>
            <p>Please choose here the available Web Shop shipping methods. The contents of the drop-down menu can be assigned in Shopware > Settings > Shipping Costs.</p>
            <p>This setting is important for bills and shipping notes, the subsequent processing of the order inside the shop, and for some ERPs.</p>'
    ),
    'orderstatus.canceled' => array(
        'label' => 'Confirm Canceled with',
        'help' => '
            Here you set the shop status which will set the {#setting:currentMarketplaceName#} order status to „cancel order“. <br/><br/>
            Note: partial cancellation is not possible in this setting. The whole order will be cancelled with this function und credited tot he customer
        '
    ),
    'orderstatus.cancelreason' => array(
        'label' => 'Cancel order - Reason',
        'help' => 'To cancel an order on OBI a reason must be given',
    ),
    'orderstatus.shipping' => array(
        'label' => 'Confirm Shipping with',
        'help' => 'Here you set the shop status which will set the {#setting:currentMarketplaceName#} order status to „shipped order“.'
    ),
    'orderstatus.standardshipping' => array(
        'label' => 'Send Carrier Option',
        'hint' => 'OBI only allows certain carriers. Please make sure to provide valid data only.'
    ),
    'orderstatus.forwardershipping' => array(
        'label' => 'Forwarding Carrier Option',
        'hint' => 'OBI only allows certain carriers. Please make sure to provide valid data only.'
    ),
    //Do not change translation keys
    'orderstatus.shippedaddress' => array(
        'label' => 'Confirm Shipping and \'From\' Address',
        'help' => 'Confirm Shipping status and the warehouse or location from which the shipment will be picked up for final delivery.'
    ),
    //Do not change translation keys
    'orderstatus.shippedaddress.city' => array(
        'label' => 'City'
    ),
    'orderstatus.shippedaddress.code' => array(
        'label' => 'Country Code'
    ),
    //Do not change translation keys
    'orderstatus.shippedaddress.zip' => array(
        'label' => 'ZIP Code'
    ),
    'orderstatus.shippedaddress.status' => array(
        'label' => 'Order Status'
    ),
    'orderstatus.carrier' => array(
        'label' => 'Carrier',
        'help' => 'Pre-selected freight forwarder confirming shipment to OBI.',
    ),
    'customfield.carrier' => array(
        'label' => 'Carrier'
    ),
    'return.carrier' => array(
        'label' => 'Return Carrier Option',
        'hint' => 'OBI only allows certain carriers. Please make sure to provide valid data only.'
    ),
    'return.trackingkey' => array(
        'label' => 'Return Tracking Key Option',
    ),
    'trackingkey' => array(
        'label' => 'Send Tracking Key Option',
    ),
    'customfield.trackingnumber' => array(
        'label' => 'Tracking number'
    ),
));

MLI18n::gi()->add('obi_prepare_form', array(
    'field' => array(
        'variationgroups' => array(
            'label' => 'Marketplace-Category<span class="bull">•</span>',
            'hint' => '',
        ),
        'variationgroups.value' => array(
            'label' => 'Marketplace-Category:',
        ),
    ),
), false);
