<?php

MLI18n::gi()->{'amazon_config_amazonvcsinvoice_reversalinvoicenumberoption_values_matching'} = 'Faire correspondre le numéro de facture d\'annulation avec un champ de la boutique';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__label'} = 'Reversal invoice number';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__label'} = 'Mode de livraison des commandes FBA';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.carrier__help'} = '
Sélectionnez le transporteur qui sera utilisé pour les commandes Amazon.<br>
<br>
Les options suivantes s’offrent à vous :<br>
<ul>
	<li><span class="bold underline">Société de livraison Amazon</span></li>
</ul>
Sélectionnez un transporteurs dans le menu déroulant. Les sociétés recommandées par Amazon y sont affichées.<br>
<br>
Cette option est idéale si vous souhaitez <strong>toujours utiliser le même transporteur</strong> pour les commandes Amazon.<br>
<ul>
	<li><span class="bold underline">Apparier les sociétés de livraison Amazon avec les sociétés de livraison de votre boutique</span></li>
</ul>
Vous pouvez faire correspondre les transporteurs proposés par Amazon avec les transporteurs créés dans le module d\'expédition de Shopware. Pour ajouter un nouvel appariement, cliquez sur le symbole "+".<br>
<br>
Pour savoir quelle entrée du module d\'expédition de Shopware est utilisée pour l\'importation des commandes Amazon, veuillez vous référer à l\'icône info sous "Importation des commandes" -> "Service d\'expédition des commandes".<br>
<br>
Choisissez cette option si vous souhaitez <strong>utiliser les sociétés de livraisons existantes du module d\'expédition de Shopware.</strong><br>
<ul>
    <li><span class="bold underline">magnalister ajoute un champ supplémentaire dans les détails de la commande</span></li>
</ul>
Si vous sélectionnez cette option, un champ sera ajouté dans l’aperçu de la commande dans Shopware dans lequel vous pouvez renseigner la société de livraison.<br>
<br>
Choisissez cette option si vous souhaitez <strong>utiliser différents transporteurs</strong> pour les commandes Amazon.<br>
<ul>
	<li><span class="bold underline">Saisir manuellement le nom de la société de transport pour toutes les commande</span></li>
</ul>
Si vous sélectionnez "Entrer le nom de la société de livraison manuellement dans un champ " sous l’option "Société de livraison" vous pouvez saisir le nom de la société de livraison manuellement.<br>
<br>
Sélectionnez cette option, si vous souhaitez <strong>définir une société de livraison qui sera utilisée pour toutes les commandes Amazon.</strong><br>
<br>
<span class="bold underline">Important :</span>
<ul>
	<li>La société de livraison doit obligatoirement être renseignée pour que l’expédition de la commande puisse être confirmée sur Amazon.<br><br></li>
	<li>Le non-renseignement de la société de livraison lors de la confirmation de l’expédition sur amazon peut entrainer la suspension de votre compte vendeur.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__help'} = 'Sélectionnez ici le statut de paiement qui sera automatiquement attribué aux commandes lors de l’importation des commandes depuis la place de marché.';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__help'} = '<p>Mode de paiement attribué à toutes les commandes Amazon lors de l\'importation de commande.  
Par défaut : "Amazon"</p>
<p>
Tous les autres modes de paiement disponibles dans la liste peuvent également être définis sous Shopware > Paramètres > Modes de paiement et utilisés ensuite.</p>
<p>
Ce paramètre est important pour l\'impression des factures et des bons de livraison, ainsi que pour le traitement ultérieur des commandes dans la boutique et dans les systèmes de gestion des stocks.</p>';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__label'} = 'Statut de la commande en boutique';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__label'} = 'Statut de la commande FBA dans votre boutique';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__help'} = 'Sélectionnez ici le statut de paiement qui sera automatiquement attribué aux commandes lors de l’importation des commandes depuis la place de marché.';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumberoption__label'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber.matching__label'} = 'Shopware order free text field';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__help'} = '<p>Lors des importations des commandes, Amazon ne transmet pas d\'information sur le mode d\'expédition. </p>
<p>Veuillez sélectionner dans le menu déroulant, les modes de livraison de votre boutique. Vous pouvez définir les modes de livraison de votre boutique en vous rendant sur "Shopware" > "paramètres" > "Frais de port". </p>
<p>Ce réglage est important pour l\'impression des bons de livraison et des factures, mais aussi pour le traitement ultérieur des commandes dans votre boutique ainsi que dans votre gestion des marchandises.</p>';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__label'} = 'Numéro de facture';
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_carrier'} = 'Charger la société de livraison depuis un des champs supplémentaires de la commande de votre boutique';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumberoption__label'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__help'} = '<p>
Choisissez ici si les numéros de facture doivent être générés par magnalister ou si vous voulez qu’ils soient extraits d’un champ de texte libre de Shopware.
</p><p>
<b>Charger magnalister de la création des numéros de commande</b>
</p><p>
Lors de la création des factures par magnalister, des numéros de factures consécutifs sont automatiquement générés. Saisissez ici un préfixe qui sera automatiquement placé devant le numéro de facture.
Exemple : F10000
</p><p>
Note : Les commandes créées par magnalister commencent par le numéro 10000.
</p><p>
<b>Tirer le numéro de commande d’un {#i18n:shop_order_attribute_name#}</b>
</p><p>
Lors de la création de la facture, le numéro de commande est tirée du {#i18n:shop_order_attribute_name#} que vous avez sélectionné.
</p><p>
{#i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Important :</b> <br/>l’agrandisseur génère et transmet la facture dès que la commande est marquée comme expédiée. Veuillez vous assurer que le champ de texte libre est bien rempli, sinon une erreur sera causée (voir l’onglet "Journal des erreurs").
<br/><br/>
Si vous utilisez la correspondance des champs de texte libre, la société Magnalister n’est pas responsable de la création correcte et consécutive des numéros de facture.
</p>';
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod'} = 'Charger le service d\'expédition depuis un des champs supplémentaires de la commande de votre boutique';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.shipmethod__help'} = '
Sélectionnez le service de livraison qui sera utilisé pour les commandes Amazon.<br>
<br>
Les options suivantes s’offrent à vous :
<ul>
	<li><span class="bold underline">Apparier les services de livraison Amazon avec les services de livraison de votre boutique</span></li>
</ul>
Vous pouvez faire correspondre les services de livraison proposés par Amazon avec les services de livraisons créés dans le module d\'expédition de Shopware. Pour ajouter un nouvel appariement, cliquez sur le symbole "+".<br>
<br>
Pour savoir quelle entrée du module d\'expédition de Shopware est utilisée pour l\'importation des commandes Amazon, veuillez vous référer à l\'icône info sous "Importation des commandes" -> "Service d\'expédition des commandes".<br>
<br>
Choisissez cette option si vous souhaitez <strong>utiliser les services d’expédition existants du module d’expédition de Shopware.</strong><br>
<ul>
	<li><span class="bold underline">magnalister ajoute un champ supplémentaire dans les détails de la commande</span></li>
</ul>
Si vous sélectionnez cette option, un champ sera ajouté dans l’aperçu de la commande dans <strong>Shopware</strong> dans lequel vous pouvez renseigner le service de livraison.<br>
<br>
Choisissez cette option si vous souhaitez <strong>utiliser différents transporteurs</strong> pour les commandes Amazon.<br>
<ul>
	<li><span class="bold underline">Saisir manuellement le nom du service d\'expédition pour toutes les commande</span></li>
</ul>
Si vous sélectionnez "Entrer le nom du service d’expédition manuellement dans un champ " sous l’option "Service d’expédition" vous pouvez saisir le nom de la société de livraison manuellement dans le champ de droite.<br>
<br>
Sélectionnez cette option, si vous souhaitez <strong>définir un service d’expédition qui sera utilisée pour toutes les commandes Amazon.</strong><br>
<br>
<span class="bold underline">Important :</span>
<ul>
	<li>Le service d’expédition doit obligatoirement être renseigné pour que l’expédition de la commande puisse être confirmée sur Amazon<br><br></li>
	<li>Le non-renseignement du service d’expédition lors de la confirmation de l’expédition sur amazon peut entrainer la suspension de votre compte vendeur.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_amazonvcsinvoice_invoicenumberoption_values_matching'} = 'Tirer le numéro de commande d’un {#i18n:shop_order_attribute_name#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__help'} = '<p>
Choose here if you want to have your reversal invoice numbers generated by magnalister or if you want them to be taken from a Shopware free text field.
</p><p>
<b>Create reversal invoice numbers via magnalister</b>
</p><p>
magnalister generates consecutive reversal invoice numbers during the invoice creation. You can define a prefix that is set in front of the reversal invoice number. Example: R10000.
</p><p>
Note: Invoices created by magnalister start with the number 10000.
</p><p>
<b>Match reversal invoice numbers with Shopware free text field</b>
</p><p>
When creating the invoice, the value is taken from the Shopware free text field you selected.
</p><p>
{#i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Important:</b><br/> magnalister generates and transmits the invoice as soon as the order is marked as shipped. Please make sure that the free text field is filled, otherwise an error will be caused (see tab "Error Log").
<br/><br/>
If you use free text field matching, magnalister is not responsible for the correct, consecutive creation of reversal invoice numbers.
</p>';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber.matching__label'} = 'Shopware order free text field';
