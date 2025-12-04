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
        'label' => 'Utiliser un Master SKU dans Shopware 5',
        'help'  => '<p>Cette configuration est particulièrement intéressante pour les vendeurs qui ont commercialisé des articles et leurs variantes sur des places de marché via magnalister avec Shopware 5 :
<ul>
    <li>Si vous ne voulez <b>pas activer</b> cette configuration, les produits définis comme articles “principaux” dans la gestion de produit Shopware 6 seront déposés sur les places de marché accompagnés de leurs variantes comme s’il s’agissait de nouveaux produits.
    </li>
    <li>Si vous <b>activez</b> cette configuration, le code SKU (Stock Keeping Unit) de l’article principal est automatiquement modifié par magnalister : à chaque nouveau produit chargé, l’article déjà présent sur la place de marché est automatiquement actualisé.
    </li>
</ul></p>
<p>
    <b>Arrière-plan :</b> Shopware 6 fait la différence entre l’article principal et ses variantes lors de l’attribution d’un code SKU. Du moment que vous utilisez l’assistant de migration de Shopware 6 pour déplacer vos articles de Shopware 5 vers Shopware 6, un “M” est ajouté au SKU de l’article principal (Exemple de SKU : “1234M”). Les variantes par contre n’ont pas cette lettre en plus.

</p><p>

    Shopware 5 ne fait aucune différence entre article principal et variante. Pour certaines places de marché, la désignation d’un article dit “principal” aussi appelé “Master”) est intéressante malgré tout. C’est pourquoi magnalister caractérise le SKU de la variante principale de l’article avec l’extension “_Master” (Beispiel : “1234_Master”).

</p><p>
    Dans la configuration activée “Shopware 5 Master-SKU”, magnalister convertit automatiquement le suffixe “M” en “_Master” lors du chargement du produit.

</p>
<p><b>Informations complémentaires :</b>
<ul>
    <li>La synchronisation des prix et des stocks entre la boutique en ligne et les différentes places de marché pour des articles déposés depuis Shopware 5 via magnalister fonctionne sous Shopware 6, même si cette configuration n’est pas activée.
    </li>
    <li>Dans la vue d’ensemble de la préparation, du chargement et de l’inventaire des produits, vous pourrez reconnaître l’article principal à l’extension placée derrière son code SKU.
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