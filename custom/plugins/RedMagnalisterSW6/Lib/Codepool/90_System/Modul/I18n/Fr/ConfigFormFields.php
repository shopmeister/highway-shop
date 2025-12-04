<?php

MLI18n::gi()->{'configform_sync_value_auto'} = 'Synchronisation automatique via CronJob (recommandée)';
MLI18n::gi()->{'orderstatus_carrier_defaultField_value_shippingname'} = 'Transmetre le nom du livreur en tant que mode de livraison';
MLI18n::gi()->{'configform_price_field_strikeprice_signal_label'} = 'Décimale après la virgule';
MLI18n::gi()->{'configform_emailtemplate_field_send_label'} = 'E-mail au client lors de la réception d\'une commande';
MLI18n::gi()->{'marketplace_configuration_orderimport_payment_method_from_marketplace'} = 'Apply the payment method submitted by the marketplace';
MLI18n::gi()->{'configform_sync_value_no'} = 'Aucune synchronisation';
MLI18n::gi()->{'configform_stocksync_values__rel'} = 'Une commande réduit la quantité en stock du magasin (recommandée)';
MLI18n::gi()->{'configform_sync_value_fast'} = 'Synchronisation plus rapide via CronJob (toutes les 15 minutes)';
MLI18n::gi()->{'configform_quantity_values__stock__textoption'} = '';
MLI18n::gi()->{'configform_price_field_priceoptions_label'} = 'Groupe clients';
MLI18n::gi()->{'configform_price_field_strikeprice_signal_help'} = '
                Ce champ de texte sera utilisé comme décimale de votre prix lors de la transmission des données à {#setting:currentMarketplaceName#}.<br/><br/>
                <strong>Exemple:</strong> <br />
                Valeur dans la zone de texte : 99 <br />
                Origine du prix : 5.58 <br />
                Résultat final : 5.99 <br /><br />
                Cette fonction est particulièrement utile pour les augmentations/diminutions de prix en pourcentage.
                Laissez le champ vide si vous ne souhaitez pas calculer de décimale.
                Le format de saisie est un nombre entier de 2 chiffres maximum.';
MLI18n::gi()->{'configform_price_field_priceoptions_help'} = 'Avec cette fonction, vous pouvez transférer des prix divergents sur le marché et les faire synchroniser automatiquement.<br />
<br />
Pour ce faire, sélectionnez un groupe clients de votre boutique dans le menu déroulant de droite.<br />
<br />
Si vous ne saisissez pas de prix divergent dans le nouveau groupe de clients, le prix par défaut de votre boutique sera automatiquement transmis à eBay. Ainsi vous pouvez facilement appliquer un prix adapté pour un nombre limité d’articles Les autres paramètres relatifs au prix seront également appliqués au prix de vente.<br />
<br />
<b>Exemple :</b><br />
<ul>
<li>Ajoutez un groupe client dans votre boutique, par exemple "Clients eBay"</li>
<li>Sur les fiches produits dans votre boutique, saisissez le prix souhaité</li>
</ul>';
MLI18n::gi()->{'configform_price_field_strikeprice_help'} = 'Prix barré sur {#setting:currentMarketplaceName#}:Cette option vous permet d’afficher un prix au rabais ou le prix de détail suggéré par le fabricant (PDSF).<br /><br />
<b>Important:</b><br />
<ul>
<li>Si le prix barré est inférieur au prix de vente, il ne sera pas transmis.</li>
<li>Les prix barré sont marqué en rouge dans magnalister dans l\'aperçu des produits à côté du prix de vente.</li>
<li>Selon les conditions de ventes d’eBay, le prix barré doit réellement correspondre à un prix antérieurement proposé dans votre boutique ou sur eBay, ou bien correspondre au prix de détail suggéré par le fabricant (PDSF).</li>
</ul>';
MLI18n::gi()->{'configform_quantity_values__lump__textoption'} = '1';
MLI18n::gi()->{'configform_quantity_values__lump__title'} = 'Forfaitaire (dans le champ de droite)';
MLI18n::gi()->{'configform_quantity_values__stock__title'} = 'Prendre en charge le stock de la boutique';
MLI18n::gi()->{'configform_emailtemplate_legend'} = 'Courriel à l\'acheteur';
MLI18n::gi()->{'configform_sync_values__no'} = '{#i18n:configform_sync_value_no#}';
MLI18n::gi()->{'configform_price_addkind_values__percent'} = 'x% prix boutique majoré ou rabais';
MLI18n::gi()->{'configform_quantity_values__stocksub__title'} = 'Prendre en charge le stock de la boutique moins la valeur du champ de droite';
MLI18n::gi()->{'configform_price_field_strikeprice_signal_hint'} = '';
MLI18n::gi()->{'configform_price_field_strikeprice_label'} = 'Prix barr&eacute;s';
MLI18n::gi()->{'configform_price_field_priceoptions_kind_label'} = 'Le prix barr&eacute; correspond &agrave;';
MLI18n::gi()->{'configform_price_addkind_values__addition'} = 'x prix boutique majoré ou rabais';
MLI18n::gi()->{'generic_prepareform_day'} = 'jour';
MLI18n::gi()->{'configform_quantity_values__stocksub__textoption'} = '1';
MLI18n::gi()->{'configform_emailtemplate_field_send_help'} = 'Activez cette fonction si vous voulez qu’un courriel soit envoyé à vos clients, afin de promouvoir votre boutique en ligne.';
MLI18n::gi()->{'configform_fast_sync_values__no'} = '{#i18n:configform_sync_value_no#}';
MLI18n::gi()->{'configform_fast_sync_values__auto'} = '{#i18n:configform_sync_value_auto#}';
MLI18n::gi()->{'configform_sync_values__auto'} = '{#i18n:configform_sync_value_auto#}';
MLI18n::gi()->{'configform_stocksync_values__no'} = '{#i18n:configform_sync_value_no#}';

MLI18n::gi()->{'config_carrier_option_group_marketplace_carrier'} = 'Porteurs suggérés par {#setting:currentMarketplaceName#}';
MLI18n::gi()->{'config_carrier_matching_title_shop_carrier'} = "Fournisseur de services d'expédition à partir du module Webshop Shipping";
MLI18n::gi()->{'config_carrier_option_group_shopfreetextfield_option_carrier'} = 'Selecciona una empresa de transporte de un campo de texto libre de la tienda web (pedidos)';
MLI18n::gi()->{'config_carrier_option_matching_option_carrier'} = 'Vincula las empresas de transporte sugeridas por {#setting:currentMarketplaceName#} con los proveedores de servicios de transporte en el módulo de transporte de la tienda online.';
MLI18n::gi()->{'config_carrier_option_orderfreetextfield_option'} = 'magnalister añade un campo de texto libre en los detalles del pedido';
MLI18n::gi()->{'config_carrier_option_freetext_option_carrier'} = 'Tomar en bloque empresas de transporte desde el campo de texto';
MLI18n::gi()->{'config_carrier_option_group_additional_option'} = 'Opciones adicionales';
MLI18n::gi()->{'config_carrier_matching_title_marketplace_carrier'} = 'Empresas de transporte propuestas por {#setting:currentMarketplaceName#}';
MLI18n::gi()->config_use_shop_value = 'Take over from Shop';
