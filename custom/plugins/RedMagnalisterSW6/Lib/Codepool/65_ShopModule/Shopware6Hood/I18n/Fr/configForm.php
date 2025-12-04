<?php

MLI18n::gi()->{'hood_config_orderimport__field__updateable.paymentstatus__help'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'hood_config_producttemplate_content'} = '<style>
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
<p>#Description1# #Freetextfield1#</p>
<p>#Description2# #Freetextfield2#</p>
<div>#PROPERTIES#</div>';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__updateable.paymentstatus__label'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentstatus__label'} = 'Statut de paiement dans votre boutique';
MLI18n::gi()->{'hood_config_orderimport__field__update.paymentstatus__label'} = 'Changement de statut de paiement activé';
MLI18n::gi()->{'hood_config_producttemplate__field__template.content__hint'} = 'Liste des champs de texte libre disponibles pour la description de l’article : <br>
<br>
#TITLE#<br>
<BLOCKQUOTE>
<p>nom du produit</p>
</BLOCKQUOTE>
#ARTNR#
<BLOCKQUOTE>
<p>numéro d’article dans votre boutique</p>
</BLOCKQUOTE>
#PID#
<BLOCKQUOTE>
<p>identifiant du produit dans votre boutique</p>
</BLOCKQUOTE>
#SHORTDESCRIPTION#
<BLOCKQUOTE>
<p>description abrégée de l’article de votre boutique</p>
</BLOCKQUOTE>
#DESCRIPTION#
	<BLOCKQUOTE>
<p>description de l’article de votre boutique</p>
	</BLOCKQUOTE>
#PICTURE1#
	<BLOCKQUOTE>
<p>première image de l’article</p>
	</BLOCKQUOTE>
#PICTURE2# etc.
<BLOCKQUOTE>
<p>deuxième image de l’article; vous pouvez ajouter plus d’images de l’article (autant que dans votre boutique) en saisissant #PICTURE3#, #PICTURE4# etc.</p>
</BLOCKQUOTE>
<br>

<br>
<br>

Champs de texte libre pour description d’article :<br>
<br>
#Description1# #Freetextfield1#<br>
#Description2# #Freetextfield2#<br>
#description12# #freetextfield1#<br>
<br>
Prise en charge des champs de texte libre: le chiffre derrière le paramètre générique correspond à la position du texte.<br>
<br>
#PROPERTIES#<br>
liste contenant toutes les caractéristiques du produit. Vous pouvez changer l’apparence de la liste avec CSS.

';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'hood_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'hood_config_orderimport__field__paidstatus__label'} = 'Statut "payé" d\'hood, en magasin';
MLI18n::gi()->{'hood_config_orderimport__field__paidstatus__help'} = '<p> Définissez ici le statut de la commande et le statut du paiement qui sera attribué à une commande, si elle a été payée via Paypal sur hood.</p>
<p>
Lorsqu\'un client achète l\'un de vos articles sur hood, la commande est directement transmise à votre boutique en ligne.
Le mode de paiement sera alors "hood" ou la valeur que vous avez saisie dans les "réglages d\'experts". </p>';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.paid__help'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__paymentstatus.paid__label'} = 'Statut de la commande';
MLI18n::gi()->{'hood_config_orderimport__field__updateablepaymentstatus__help'} = 'Vous pouvez avec cette fonction synchroniser le changement de statut des commandes après paiements sur hood. <br>
Normalement, les changements de statut de commande n\'ont pas d’incidence sur le statut de paiement sur hood. <br>
<br>
Si vous ne souhaitez aucun changement de statut au paiement de la commande, désactivez la case à droite de la fenêtre de choix.<br>
<br>
<strong>Remarque :</strong> Le statut des commandes combinées ne sera modifié, que si toutes les parties ont été payées.';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.paid__label'} = 'Statut de la commande';
MLI18n::gi()->{'hood_config_orderimport__field__updateablepaymentstatus__label'} = 'Autoriser les changements de statut de paiement si';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__paymentstatus.paid__help'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withfrommarketplace_help#}';
MLI18n::gi()->{'hood_config_producttemplate__field__template.content__label'} = 'Corp du template';
