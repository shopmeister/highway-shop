<?php

MLI18n::gi()->{'formfields__quantity__help'} = '{#setting:currentMarketplaceName#} n\'autorise, que la quantité de stock "disponible" ou "non disponible". Indiquez si l\'article doit être offert en fonction de l\'inventaire de votre magasin.<br /><br />Pour éviter les surventes, vous pouvez utiliser la valeur «prendre en charge le stock de la boutique, moins, la valeur du champ ci-contre» et donner une valeur de reserve dans le champ mentionné.<br /><br />Exemple : Stock en boutique : 10 (articles) → valeur entrée: 2 (articles) → Stock alloué à {#setting:currentMarketplaceName#}: 8 (articles).<br /><br />Note: Si, vous voulez que les articles inactifs, indépendamment des quantités disponibles, aient sur le marché une valeur de stock "0", veuillez procéder comme suit:<br /<br />Cliquez sur les onglets “Configuration” → “Synchronisation”;<br />Rubrique “Synchronisation des Inventaires" → "Variation du stock boutique";<br />Activez dans la liste de choix "synchronisation automatique via CronJob",<br />Cliquez sur l’onglet "Configuration globale", Rubrique “Inventaire”,<br />activez<br />"Si le statut du produit est placé comme étant inactif, le niveau des stocks sera alors enregistré comme quantité 0".';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__COD'} = 'paiement à la livraison';
MLI18n::gi()->{'formfields_idealo__shippingmethod__values__Spedition'} = 'entreprise de transport';
MLI18n::gi()->{'formfields_idealo__prepare_title__label'} = 'Titre';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__SKRILL'} = 'Skrill';
MLI18n::gi()->{'formfields_idealo__shippingmethod__values__Paketdienst'} = 'service de colis';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__PAYPAL'} = 'PayPal';
MLI18n::gi()->{'formfields_idealo__shippingcostmethod__values____ml_weight'} = 'Frais d\'expédition = articles poids';
MLI18n::gi()->{'formfields_idealo__shippingmethod__help'} = 'Spécifiez la méthode d\'expédition qui sera utilisée pour vos devis d\'achat direct.';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__1-3days__title'} = '1-3 journées';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__BILL'} = 'facture';
MLI18n::gi()->{'formfields_idealo__prepare_image__label'} = 'Images de produits';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__CREDITCARD'} = 'carte de crédit';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__GIROPAY'} = 'Giropay';
MLI18n::gi()->{'formfields_idealo__prepare_description__label'} = 'Description';
MLI18n::gi()->{'formfields_idealo__shippingtime__optional__checkbox__labelNegativ'} = '';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values____ml_lump__title'} = 'Forfait (dans le champs de droite)';
MLI18n::gi()->{'formfields_idealo__prepare_image__optional__checkbox__labelNegativ'} = '{#i18n:ML_PRODUCTPREPARATION_ALWAYS_USE_FROM_WEBSHOP#}';
MLI18n::gi()->{'formfields_idealo__shippingmethod__values__Download'} = 'Download';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__help'} = '
    <strong>Remarque:</strong> Comme {#setting:currentMarketplaceName#} ne connaît que "disponible" ou "indisponible" pour vos offres, il est tenu compte ici :<br>
    <br>
    <ul>
        <li>Quantité en stock magasin &gt ; 0 = disponible sur {#setting:currentMarketplaceName#}</li>
        <li>Quantité en stock magasin &lt ; 1 = non disponible sur {#setting:currentMarketplaceName#}</li>.
    </ul>
    <br>
    <strong>Fonction :</strong><br>
    <dl>
        <dt>Synchronisation automatique par CronJob (recommandé)</dt>
        <dd>
            La fonction "Synchronisation automatique" aligne toutes les 4 heures (commence à 0:00 du matin) le {#setting:currentMarketplaceName#} stock actuel sur le stock de la boutique (avec déduction éventuelle selon la configuration).<br />
            <br />
            Les valeurs de la base de données sont alors vérifiées et reprises, même si les modifications apportées par exemple par une gestion des marchandises n\'ont eu lieu que dans la base de données.<br />
            <br />
            Vous pouvez déclencher une synchronisation manuelle en cliquant sur le bouton de fonction correspondant "Synchronisation des prix et des stocks" en haut à droite du plug-in magnalister.<br />
            En outre, vous pouvez également déclencher la synchronisation des stocks (à partir du tarif Enterprise - au maximum tous les quarts d\'heure) par votre propre CronJob, en appelant le lien suivant vers votre boutique:<br />
            <i>{#setting:sSyncInventoryUrl#}</i><br />
            Les propres appels CronJob par des clients qui ne sont pas dans le tarif Enterprise ou qui fonctionnent plus souvent que tous les quarts d\'heure sont bloqués.<br />
        </dd>
    </dl>
    <br />
    <strong>Remarque:</strong> Les réglages sous "Configuration" → "Procédure de réglage" ...<br>
    <br>
    → "Limite de commande par jour calendaire" et<br>
    → "Nombre de pièces en stock" pour les deux premières options.<br><br>... sont pris en compte.
';
MLI18n::gi()->{'formfields_idealo__paymentmethod__label'} = 'Mode de payement <span class="bull">•</span>';
MLI18n::gi()->{'formfields_idealo__prepare_description__optional__checkbox__labelNegativ'} = '{#i18n:ML_PRODUCTPREPARATION_ALWAYS_USE_FROM_WEBSHOP#}';
MLI18n::gi()->{'formfields_idealo__shippingmethodandcost__label'} = 'Frais d\'expédition';
MLI18n::gi()->{'formfields_idealo__currency__hint'} = '';
MLI18n::gi()->{'formfields_idealo__shippingcostmethod__values____ml_lump'} = 'ML_COMPARISON_SHOPPING_LABEL_LUMP';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__PRE'} = 'paiement anticipé';
MLI18n::gi()->{'formfields_idealo__campaignlink__label'} = 'Lien de la campagne';
MLI18n::gi()->{'formfields_idealo__campaignparametername__label'} = 'Nom du paramètre de campagne';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__3days__title'} = '3 journées';
MLI18n::gi()->{'formfields_idealo__currency__label'} = 'Monnaie';
MLI18n::gi()->{'formfields_idealo__shippingtime__label'} = 'délai de livraison';
MLI18n::gi()->{'formfields_idealo__shippingcountry__label'} = 'Expédié vers';
MLI18n::gi()->{'formfields_idealo__prepare_title__optional__checkbox__labelNegativ'} = '{#i18n:ML_PRODUCTPREPARATION_ALWAYS_USE_FROM_WEBSHOP#}';
MLI18n::gi()->{'formfields_idealo__prepare_image__hint'} = 'Maximal 3 images';
MLI18n::gi()->{'formfields_idealo__paymentmethod__help'} = '
            indiquez les modes de paiement standard souhaités sur idealo et pour l\'achat direct (multiple possible sélection).<br />
            Sous "préparer les produits", vous pouvez, à tout moment et individuellement, ajuster les modes de paiement  par produit.<br />
            <br />
            Note: {#setting:currentMarketplaceName#} n\'autorise pour l\'achat direct, que le paiement avec PayPal, le transfert instantané ou les cartes de crédit.<br />
            Le mode de paiement, que vous avez sélectionné pour l\'achat direct, sera également affiché sur idealo.
        ';
MLI18n::gi()->{'formfields_idealo__shippingmethodandcost__help'} = '"Indiquez ici les frais de port forfaitaires pour vos articles en euros. Dans la préparation des articles, vous pouvez enregistrer individuellement les valeurs pour les articles sélectionnés."';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__SOFORT'} = 'Sofort&uuml;berweisung';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__4-6days__title'} = '4-6 journées';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__CLICKBUY'} = 'Click&Buy';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__BANKENTER'} = 'banque entrer';
MLI18n::gi()->{'formfields_idealo__campaignlink__help'} = 'Pour créer un lien de campagne qui puisse faire l\'objet d\'un suivi spécifique, veuillez saisir une chaîne de caractères sans caractères spéciaux (par ex. trémas, signes de ponctuation et espaces), comme par exemple "toutdoitdisparaitre".';
MLI18n::gi()->{'formfields_idealo__campaignparametername__help'} = 'Vous pouvez définir ici le nom du paramètre utilisé pour le lien de campagne dans l’URL. Si aucune valeur personnalisée n’est spécifiée, "mlcampaign" sera utilisé par défaut. Veuillez saisir une chaîne sans caractères spéciaux (par exemple, sans accents, signes de ponctuation ou espaces), comme "campagne1".';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__2-3days__title'} = '2-3 journées';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__immediately__title'} = 'immédiatement';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__24h__title'} = '24 heures';
MLI18n::gi()->{'formfields_idealo__shippingtimeproductfield__label'} = 'délai de livraison (matching)';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__1-2days__title'} = '1-2 journées';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__4weeks__title'} = '4 semaines';
MLI18n::gi()->{'formfields_idealo__access.inventorypath__label'} = 'Lien vers votre fichier CSV';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__3-5days__title'} = '3-5 journées';
MLI18n::gi()->{'formfields_idealo__shippingmethod__label'} = 'Mode d\'expédition';
