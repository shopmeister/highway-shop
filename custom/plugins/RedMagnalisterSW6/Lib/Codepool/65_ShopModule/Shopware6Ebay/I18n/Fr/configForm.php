<?php

MLI18n::gi()->{'ebay_config_orderimport__field__paidstatus__label'} = 'Statut de la commande/paiement pour les commandes eBay payantes';
MLI18n::gi()->{'ebay_config_orderimport__field__updateablepaymentstatus__help'} = 'Vous pouvez avec cette fonction synchroniser le changement de statut des commandes après paiements sur eBay. <br>
Normalement, les changements de statut de commande n\'ont pas d’incidence sur le statut de paiement sur eBay. <br>
<br>
Si vous ne souhaitez aucun changement de statut au paiement de la commande, désactivez la case à droite de la fenêtre de choix.<br>
<br>
<strong>Remarque :</strong> Le statut des commandes combinées ne sera modifié, que si toutes les parties ont été payées.';
MLI18n::gi()->{'ebay_config_orderimport__field__update.paymentstatus__label'} = 'Changement de statut de paiement activé';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.shippingmethod__help'} = '<p>Mode de livraison attribué à toutes les commandes eBay lors de l\'importation de commande.  
Par défaut : "Attribution automatique"</p>
<p>
Si vous choisissez « Attribution automatique », magnalister adopte le mode de livraison sélectionné par l\'acheteur sur eBay.
Ce mode de livraison sera également ajouté sous Shopware > Paramètres > Frais de port.</p>
<p>
Tous les autres modes de livraison disponibles dans la liste peuvent également être définis sous Shopware > Paramètres > Frais de port et utilisés ensuite.</p>
<p>
Ce paramètre est important pour l\'impression des factures et des bons de livraison, ainsi que pour le traitement ultérieur de la commande dans la boutique et dans les systèmes de gestion des stocks.</p>
';
MLI18n::gi()->{'ebay_config_orderimport__field__updateable.paymentstatus__help'} = '';
MLI18n::gi()->{'ebay_config_orderimport__field__updateablepaymentstatus__label'} = 'Autoriser les changements de statut de paiement si';
MLI18n::gi()->{'ebay_prepare_apply_form_field_description_hint_customfield'} = '<dt>Champs supplémentaires :</dt><dt>#LABEL_{Nom technique}# #VALUE_{Nom technique}#</dt><dt>ex</dt>';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'ebay_config_orderimport__field__paymentstatus.paid__label'} = 'Statut des paiement';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'ebay_config_orderimport__field__orderstatus.paid__label'} = 'Statut de la commande';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'ebay_config_producttemplate__field__template.content__hint'} = 'ebay_prepare_apply_form__field__description__hint';
MLI18n::gi()->{'ebay_config_orderimport__field__orderstatus.paid__help'} = '';
MLI18n::gi()->{'ebay_config_orderimport__field__updateable.paymentstatus__label'} = '';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.paymentstatus__label'} = 'Statut de paiement dans votre boutique';
MLI18n::gi()->{'ebay_config_producttemplate__field__template.content__label'} = 'Corp du template';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'ebay_config_orderimport__field__orderstatus.open__help'} = 'Définissez ici l’état de la commande dans la boutique en ligne, afin que chaque nouvelle commande sur eBay le modifie automatiquement.
<br><br>
Attention, ce processus entraîne l’importation des commandes sur eBay qui ont été réglées aussi bien que celles qui ne le sont pas.
<br><br>
Toutefois vous pouvez déterminer grâce à la fonction "Importer uniquement les commandes marquées "payées"" que seules les commandes dont le règlement a déjà été effectué sur eBay soient prises en charge dans votre boutique en ligne.
<br><br>
Pour les commandes sur eBay qui ont été payées, vous pouvez créer un Etat de commande spécifique plus bas, sous l’appellation « Synchronisation du statut des commandes » > « Statut de la commande/paiement pour les commandes eBay payantes ». 
<br><br>
<b>Indication pour votre Lettre de relance</b>
<br><br>
Dans le cas où vous utilisez un système de Gestion des marchandises/Facturation rattaché à votre boutique en ligne, il est recommandé d’adapter les Etats de commande de façon à ce que ce service de Gestion des marchandises/Facturation puisse en faire le traitement en adéquation avec votre concept. 
';
MLI18n::gi()->{'ebay_config_orderimport__field__paidstatus__help'} = 'Les commandes sur eBay sont en partie réglées par les acheteurs avec un délai. 
<br><br>
Pour séparer les commandes non payées des commandes payées, vous pouvez choisir votre propre statut de commande pour la boutique en ligne et le statut de paiement pour les commandes payées sur eBay 
<br><br>
Quand les commandes qui sont importées par eBay n’ont pas encore été réglées, l’Etat de la commande qui s’applique est celui que vous avez défini en haut sous «  Importation des commandes » > « Statut de la commande en boutique ».
<br><br>
Si vous avez activé en haut "Importer uniquement les commandes marquées "payées"", c’est également l’ « état de commande dans la boutique en ligne » qui est utilisé. Cette fonction apparaît alors comme grisée.';
MLI18n::gi()->{'ebay_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'ebay_config_producttemplate_content'} = '<style>
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
<div>#PROPERTIES#</div>';
MLI18n::gi()->{'ebay_config_orderimport__field__paymentstatus.paid__help'} = '';
MLI18n::gi()->{'ebay_config_orderimport__field__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
