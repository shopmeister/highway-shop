<?php

MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_label'} = 'Prix de vente résultant de la règle de prix';
MLI18n::gi()->{'Shopware_Ebay_Configuration_ArticleDescriptionTemplate_sDefault'} = '<style>
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
<p>#Description1# #Freetextfield1#</p>
<p>#Description2# #Freetextfield2#</p>
<div>#PROPERTIES#</div>';
MLI18n::gi()->{'Shopware_EBay_Configuration_ShippingMethod_Info'} = '<p> Mode de livraison qui sera attribué à toutes les commandes effectuées sur {#setting:currentMarketplaceName#} lors de l\'importation des commandes. <br>
Valeur par défaut : "Attribution automatique"</p>
<p>
Si l\'"Attribution automatique" est sélectionnée magnalister reprend le mode de livraison que le client a choisit lors de sa commande sur {#setting:currentMarketplaceName#}.
Le mode de livraison sera alors ajouté à votre boutique dans "Shopware" > "Paramètres" > "Frais de port". </p>
<p>Vous pouvez définir d\'autres modes de livraison qui s\'afficheront dans le menu déroulant en vous rendant sur "Shopware" > "frais de port".</p>
<p>Ce réglage est important pour l\'impression des bons de livraison et des factures, mais aussi pour le traitement ultérieur des commandes dans votre boutique ainsi que dans votre gestion des marchandises.</p>
';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_Payment_sLabel'} = 'Statut de paiement';
MLI18n::gi()->{'Shopware6_Marketplace_Configuration_SalesChannel_Info'} = '<p>Sélectionnez ici le canal de vente Shopware auquel les commandes de cette place de marché doivent être attribuées.
Les modes d’expédition et de paiement utilisés par magnalister pour ces commandes seront également repris de ce canal de vente.</p>
<p>Les canaux de vente Shopware sont accessibles depuis le menu principal de Shopware, &agrave; gauche, sous l\'option "Canaux de vente".</p>
<p>Ouvrez le canal de vente Shopware de votre choix et configurez vos m&eacute;thodes de paiement et de livraison pr&eacute;f&eacute;r&eacute;es dans la section "Paiement et exp&eacute;dition".</p>
<p>Ces m&eacute;thodes seront alors disponibles comme options d&eacute;roulantes dans le plugin magnalister (voir r&eacute;glages suivants).</p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_sLabel'} = 'Statut de paiement eBay dans la boutique';
MLI18n::gi()->{'Shopware_Amazon_Configuration_PaymentStatus_sLabel'} = 'Statut de paiement dans la boutique';
MLI18n::gi()->{'Shopware6_Marketplace_Configuration_SalesChannel_Label'} = 'Canaux de vente';
MLI18n::gi()->{'shopware6_configuration_paymentmethod_help'} = '<p>Choisissez dans le menu d&eacute;roulant le mode de paiement &agrave; attribuer &agrave; toutes les commandes {#setting:currentMarketplaceName#} lors de leur importation. Vous pouvez choisir parmi les m&eacute;thodes de paiement que vous avez &eacute;tablies dans le canal de vente Shopware s&eacute;lectionn&eacute;, sous la section &ldquo;Paiement et exp&eacute;dition&rdquo;.</p>
<p>Remarques suppl&eacute;mentaires :</p>
<ul>
<li aria-level="1">
<p>Il est obligatoire de choisir un mode de paiement. Si aucun mode n\'est s&eacute;lectionn&eacute;, magnalister affichera un message d\'erreur en haut de l\'&eacute;cran lors de la tentative de sauvegarde des r&eacute;glages.<br><br></p>
</li>
<li aria-level="1">
<p>Un message d\'erreur sera &eacute;galement &eacute;mis par magnalister si un mode de paiement choisi est supprim&eacute; ult&eacute;rieurement du canal de vente Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>La d&eacute;finition du mode de paiement est cruciale pour l\'impression des factures et des bons de livraison, ainsi que pour la gestion ult&eacute;rieure des commandes dans la boutique et dans les syst&egrave;mes de gestion de stock.</p>
</li>
</ul>';
MLI18n::gi()->{'Shopware_Amazon_Configuration_PaymentStatus_sDescription'} = 'Le statut de paiement dans la boutique en ligne, a récemment reçu une nouvelle commande qui devrait arriver automatiquement dans la boutique.';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_Order_sLabel'} = 'Statut des commandes';
MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withfrommarketplace_help'} = '<p>Choisissez dans le menu d&eacute;roulant le mode de livraison &agrave; attribuer &agrave; toutes les commandes {#setting:currentMarketplaceName#} lors de leur importation.</p>
<p>Options de s&eacute;lection :</p>
<ul>
<li aria-level="1">
<p>Classement automatique (reprendre le mode de livraison de {#setting:currentMarketplaceName#}) : magnalister utilisera le mode de livraison s&eacute;lectionn&eacute; par l\'acheteur sur {#setting:currentMarketplaceName#}. Si ce mode de livraison n\'est pas d&eacute;j&agrave; configur&eacute; dans Shopware, magnalister le cr&eacute;era automatiquement et l\'ajoutera &eacute;galement sous "Paiement et exp&eacute;dition" dans le canal de vente Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>S&eacute;lectionnez votre propre mode de livraison : Optez pour l\'un des modes de livraison disponibles et non gris&eacute;s dans le menu d&eacute;roulant pour l\'appliquer &agrave; toutes les commandes. Vous pouvez choisir parmi les modes de livraison que vous avez configur&eacute;s dans le canal de vente Shopware s&eacute;lectionn&eacute;, sous "Paiement et exp&eacute;dition".</p>
</li>
</ul>
<p>Remarques suppl&eacute;mentaires :</p>
<ul>
<li aria-level="1">
<p>Il est obligatoire de choisir un mode de livraison. Si aucun mode n\'est s&eacute;lectionn&eacute;, magnalister affichera un message d\'erreur en haut de l\'&eacute;cran lors de la tentative de sauvegarde des r&eacute;glages.<br><br></p>
</li>
<li aria-level="1">
<p>Un message d\'erreur sera &eacute;galement &eacute;mis par magnalister si un mode de livraison choisi est supprim&eacute; ult&eacute;rieurement du canal de vente Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>La d&eacute;finition du mode de livraison est cruciale pour l\'impression des factures et des bons de livraison, ainsi que pour la gestion ult&eacute;rieure des commandes dans la boutique et dans les syst&egrave;mes de gestion de</p>
</li>
</ul>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_sDescription'} = '<p> Définissez ici le statut de la commande et le statut du paiement qui sera attribué à une commande, si elle a été payée via Paypal sur eBay.</p>
<p>
Lorsqu\'un client achète l\'un de vos articles sur eBay, la commande est directement transmise à votre boutique en ligne.
Le mode de paiement sera alors "eBay" ou la valeur que vous avez saisie dans les "réglages d\'experts". </p>';
MLI18n::gi()->{'Shopware_Amazon_Configuration_ShippingMethod_Info'} = '<p>Lors des importations des commandes, Amazon ne transmet pas d\'information sur le mode d\'expédition. </p>
<p>Veuillez sélectionner dans le menu déroulant, les modes de livraison de votre boutique. Vous pouvez définir les modes de livraison de votre boutique en vous rendant sur "Shopware" > "paramètres" > "Frais de port". </p>
<p>Ce réglage est important pour l\'impression des bons de livraison et des factures, mais aussi pour le traitement ultérieur des commandes dans votre boutique ainsi que dans votre gestion des marchandises.</p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_OrderStatus_Label'} = 'Permettre le changement de statut de commande si';
MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help'} = '<p>Choisissez dans le menu d&eacute;roulant le mode de livraison &agrave; attribuer &agrave; toutes les commandes {#setting:currentMarketplaceName#} lors de leur importation.</p>
<p>Options de s&eacute;lection :</p>
<ul>
<li aria-level="1">
<p>S&eacute;lectionnez votre propre mode de livraison : Optez pour l\'un des modes de livraison disponibles et non gris&eacute;s dans le menu d&eacute;roulant pour l\'appliquer &agrave; toutes les commandes. Vous pouvez choisir parmi les modes de livraison que vous avez configur&eacute;s dans le canal de vente Shopware s&eacute;lectionn&eacute;, sous "Paiement et exp&eacute;dition".</p>
</li>
</ul>
<p>Remarques suppl&eacute;mentaires :</p>
<ul>
<li aria-level="1">
<p>Il est obligatoire de choisir un mode de livraison. Si aucun mode n\'est s&eacute;lectionn&eacute;, magnalister affichera un message d\'erreur en haut de l\'&eacute;cran lors de la tentative de sauvegarde des r&eacute;glages.<br><br></p>
</li>
<li aria-level="1">
<p>Un message d\'erreur sera &eacute;galement &eacute;mis par magnalister si un mode de livraison choisi est supprim&eacute; ult&eacute;rieurement du canal de vente Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>La d&eacute;finition du mode de livraison est cruciale pour l\'impression des factures et des bons de livraison, ainsi que pour la gestion ult&eacute;rieure des commandes dans la boutique et dans les syst&egrave;mes de gestion de</p>
</li>
</ul>';
MLI18n::gi()->{'form_config_orderimport_exchangerate_update_help'} = 'Si la devise utilisé dans votre boutique en ligne est différente de celle de la place de marché, magnalister calcule le taux de change par rapport au taux que vous avez défini dans votre boutique en ligne. <br>
<br>
En activant cette fonction, le taux de change actuel défini par Yahoo-Finance sera appliqué à vos articles. Les prix dans votre boutique en ligne seront également mis à jour.<br>
<br>
L’activation et la désactivation de cette fonction prend effet toutes les heures.<br>
<br>
Les fonctions suivantes provoqueront une actualisation du taut de change :
<ul>
<li>Importation des commandes</li>
<li>Préparer les articles</li>
<li>Charger les articles</li>
<li>Synchronisation des prix et des stocks</li>
</ul>
<b>Avertissement :</b> RedGecko GmbH n\'assume aucune responsabilité quand à l\'exactitude du taux de change. Veuillez vérifier en contrôlant les prix de vos articles sur la place de marché.';
MLI18n::gi()->{'form_config_orderimport_exchangerate_update_alert'} = '<b>Attention!</b> <br>
En activant cette fonction, le taux de change actuel défini par Yahoo-Finance sera appliqué à vos articles. Les prix dans votre boutique en ligne seront également mis à jour.<br>
<br>
les fonctions suivantes provoquent une mise à jour des prix:
<ul>
<li>Importation des commandes</li>
<li>Préparer les articles</li>
<li>Charger les articles</li>
<li>Synchroniser les prix et les stocks</li>
<br>
<b>Avertissement :</b> RedGecko GmbH n\'assume aucune responsabilité pour l\'exactitude du taux de change. Veuillez vérifier en contrôlant les prix de vos articles sur la place de marché.';
MLI18n::gi()->{'global_config_price_field_price.discountmode_label'} = 'Mode de rabais';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_PaymentStatus_Info'} = 'Vous pouvez avec cette fonction synchroniser le changement de statut des commandes après paiements sur eBay. <br>
Normalement, les changements de statut de commande n\'ont pas d’incidence sur le statut de paiement sur eBay. <br>
<br>
Si vous ne souhaitez aucun changement de statuts au paiement de la commande, désactivez la case à droite de la fenêtre de choix.<br>
<br>
<strong>Remarque :</strong> Le statut des commandes combinées ne sera modifié, que si toutes les parties ont été payées.';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_PaymentStatus_Label'} = 'Permettre le changement de statut de commande si';
