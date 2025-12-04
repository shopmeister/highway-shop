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
        'label' => 'Use Shopware 5 Master-SKU',
        'help'  => '<p>This setting is only relevant for merchants who have already submitted variant items to the marketplaces via
    magnalister using Shopware 5:
<ul>
    <li>If you <b>do not activate the setting</b>, the products defined as "master" items in Shopware 6 Product
        Management will
        be created as new products on the marketplaces, along with all associated variants.
    </li>
    <li>If you <b>activate the setting</b>, the SKU (Stock Keeping Unit) of the "master" item will be automatically
        adjusted by
        magnalister so that the existing marketplace item is updated when the product is uploaded again.
    </li>
</ul></p>
<p>
    <b>Background:</b> Shopware 6 differentiates between "master" items and variants when assigning an SKU (Stock
    Keeping Unit). If you use the Shopware 6 Migration Wizard to migrate your products from Shopware 5 to 6, an "M" is
    appended to the SKU of the "master" item (example SKU: "1234M"). Variants do not get this addition.

</p><p>

    The distinction between "master" and variant does not exist in Shopware 5. However, for some marketplaces the
    identification of a master item is relevant. Therefore, magnalister automatically marks the SKU of the main variant
    of the item with the addition "_Master" (example: "1234_Master") when uploading products from Shopware 5.

</p><p>
    If the "Shopware 5 Master SKU" setting is enabled, magnalister automatically converts the "M" suffix to "_Master"
    during product upload.

</p>
<p><b>Further notes:</b>
<ul>
    <li>Price and stock synchronization of items between webshop and marketplaces transmitted via magnalister from
        Shopware 5 works under Shopware 6 even if this setting is not activated.
    </li>
    <li>In the overview of product preparation, product upload and the inventory tab, you can recognize "master" items
        by the suffix behind the SKU.
    </li>
</ul></p>'
    );

MLI18n::gi()->{'general_shopware6_flow_skipped'} =
    array(
        'label'     => 'Shopware 6 Flow Builder Support',
        'valuehint' => 'Skip Flow Builder during order import',
        'hint'      => 'More information in the info icon',
        'help'      => 'Currently we support the following events:<br>
* "Order reached status ..." (state_enter.order.state....)<br>
* "Order is placed" (checkout.order.placed)'
    );