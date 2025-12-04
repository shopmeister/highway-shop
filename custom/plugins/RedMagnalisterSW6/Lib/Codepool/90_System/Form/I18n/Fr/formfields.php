<?php

MLI18n::gi()->{'formfields__checkin.status__help'} = '"Dans la boutique en ligne, vous pouvez activer ou désactiver des articles. Selon le paramètre choisi ici, seuls les articles actifs seront affichés lors du téléchargement des produits."';
MLI18n::gi()->{'formfields__orderstatus.canceled__help'} = '
            Sélectionnez ici, le statut boutique, qui transmettra automatiquement le statut "Commande annulée" à l\'{#setting:currentMarketplaceName#}.<br />
            <br />
            Remarque: Dans le cadre de commandes groupées, l\'annulation partielle n\'est pas possible. Cette fonction annulera toute la commande.
        ';
MLI18n::gi()->{'formfields__config_uploadInvoiceOption__label'} = 'Options de transmission des factures';
MLI18n::gi()->{'formfields__price__label'} = 'Prix';
MLI18n::gi()->{'formfields__erpInvoiceSource__label'} = 'Répertoire source pour factures (chemin d’accès)';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumberPrefix__hint'} = 'Si vous définissez un préfixe, celui-ci sera placé automatiquement devant le numéro de facture. Exemple : F10000. Le numéro des factures générées par magnalister commence par 10000';
MLI18n::gi()->{'formfields__stocksync.frommarketplace__label'} = 'Variation du stock {#setting:currentMarketplaceName#}';
MLI18n::gi()->{'formfields__orderimport.shop__help'} = '{#i18n:form_config_orderimport_shop_help#}';
MLI18n::gi()->{'formfields__orderstatus.carrier.default__help'} = 'Transporteur choisi en confirmant l\'expédition sur {#setting:currentMarketplaceName#}.';
MLI18n::gi()->{'formfields__config_invoice_invoiceHintText__label'} = 'Texte d\'information';
MLI18n::gi()->{'formfields__orderstatus.shipped__label'} = 'Confirmer la livraison avec';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumberPrefix__default'} = 'F';
MLI18n::gi()->{'formfields__mail.originator.name__default'} = 'Exemple-Shop';
MLI18n::gi()->{'formfields_uploadInvoiceOption_values__erp'} = 'Les factures créées dans le système tiers (par exemple ERP) sont transmises à {#setting:currentMarketplaceName#}';
MLI18n::gi()->{'formfields__maxquantity__help'} = '
            Cette fonction vous permet de limiter la quantité disponible d’un article, sur votre marché {#setting:currentMarketplaceName#}.
            <br /><br />
            <strong>Exemple</strong> : Sous la rubrique "Quantité", choisissez l’option "Prendre en charge (cas) le stock de la boutique" puis inscrivez “20” sous la rubrique “Quantité limitée”. Ainsi ne seront vendables sur {#setting:currentMarketplaceName#}, que 20 pièces d’un article donné, disponible dans le stock de votre boutique. <br />
            La synchronisation du stock (si elle est activée) harmonisera dans ce cas les quantités entre vos différents stocks à concurrence de 20 pièces maximum. 
            <br /><br />
            Si vous ne souhaitez pas de limitation, laissez le champ vide ou inscrivez "0".<br /><br />
            <strong>Remarque</strong> : Si sous la rubrique "Quantité", vous avez choisi l’option "forfait (sur le côté droit)", la limitation n\'est pas applicable.
        ';
MLI18n::gi()->{'formfields__orderimport.paymentmethod__label'} = 'Mode de paiement des commandes';
MLI18n::gi()->{'formfields__orderstatus.shipped__help'} = 'Définissez ici le statut de la commande, qui doit automatiquement confirmer la livraison  sur {#setting:currentMarketplaceName#}.';
MLI18n::gi()->{'formfields__mail.originator.name__label'} = 'Nom de l\'expéditeur';
MLI18n::gi()->{'formfields__config_invoice_preview__label'} = 'Aperçus de la facture';
MLI18n::gi()->{'formfields__mail.content__label'} = 'Contenu de l\'E-mail';
MLI18n::gi()->{'formfields__customergroup__help'} = 'Groupes de clients, auxquels les clients peuvent être affectés lors de nouvelles commandes';
MLI18n::gi()->{'formfields__config_invoice_headline__label'} = 'Intitulé de la facture';
MLI18n::gi()->{'formfields__tabident__help'} = '{#i18n:ML_TEXT_TAB_IDENT#}';
MLI18n::gi()->{'formfields__quantity__help'} = '
            Cette rubrique vous permet d’indiquer les quantités disponibles d’un article de votre stock, pour une place de marché particulière. <br>
            <br>
            Elle vous permet aussi de gérer le problème de ventes excédentaires. Pour cela activer dans la liste de choix, la fonction : "reprendre le stock de l\'inventaire en boutique, moins la valeur du champ de droite". <br>
            Cette option ouvre automatiquement un champ sur la droite, qui vous permet de donner des quantités à exclure de la comptabilisation de votre inventaire général, pour les réserver à un marché particulier. <br>
            <br>
            <b>Exemple :</b> Stock en boutique : 10 (articles) &rarr; valeur entrée: 2 (articles) &rarr; Stock alloué à {#setting:currentMarketplaceName#}: 8 (articles).<br>
            <br>
            <b>Remarque :</b> Si vous souhaitez cesser la vente sur {#setting:currentMarketplaceName#}, d’un article que vous avez encore en stock, mais que vous avez désactivé de votre boutique, procédez comme suit :
            <ol>
                <li>Cliquez sur  les onglets  “Configuration” →  “Synchronisation”; </li>
                <li>Rubrique  “Synchronisation des Inventaires" →  "Variation du stock boutique";</li>
                <li>Activez dans la liste de choix "synchronisation automatique via CronJob";</li>
                <li>Cliquez sur  l’onglet  "Configuration globale";</li>
                <li>Rubrique “Inventaire”, activez "Si le statut du produit est placé comme étant   inactif, le niveau des stocks sera alors enregistré comme quantité 0".</li>
            </ol>
        ';
MLI18n::gi()->{'formfields_config_invoice_invoiceNumberOption_values_magnalister'} = 'Charger magnalister de la création des numéros de commande';
MLI18n::gi()->{'formfields__orderstatus.sync__label'} = 'Synchronisation du statut';
MLI18n::gi()->{'formfields_uploadInvoiceOption_values__magna'} = 'Charger magnalister de la création et de la transmission des factures';
MLI18n::gi()->{'formfields__erpInvoiceDestination__hint'} = '';
MLI18n::gi()->{'formfields__erpInvoiceSource__hint'} = '';
MLI18n::gi()->{'formfields_uploadInvoiceOption_values__off'} = 'Ne pas transmettre les factures à {#setting:currentMarketplaceName#}';
MLI18n::gi()->{'formfields__orderstatus.open__label'} = 'Statuts de la commandes en boutique';
MLI18n::gi()->{'formfields__exchangerate_update__alert'} = '{#i18n:form_config_orderimport_exchangerate_update_alert#}';
MLI18n::gi()->{'formfields__maxquantity__label'} = 'Nombre de pièces en stock';
MLI18n::gi()->{'formfields__mail.copy__help'} = 'Activez cette fonction si vous souhaitez recevoir une copie du courriel.';
MLI18n::gi()->{'formfields__mwst.fallback__label'} = 'TVA des articles non référencés dans la boutique';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__help'} = '
            Mode d\'expédition standard, assignée à toutes les commandes {#setting:currentMarketplaceName#}. Standard : "{#setting:currentMarketplaceName#}".<br />
            <br />
            Ce paramètre est important pour l\'impression des factures les bons de livraison, pour le traitement ultérieur de la commande dans la boutique ainsi que pour la gestion des marchandises.<br />
        ';
MLI18n::gi()->{'formfields__stocksync.frommarketplace__help'} = '
            Si cette fonction est activée, le nombre de commandes effectués et payés sur {#setting:currentMarketplaceName#}, sera soustrait de votre stock boutique.<br>
            <br>
            <b>Important :</b> Cette fonction n’est opérante que lors de l’importation des commandes.
        ';
MLI18n::gi()->{'formfields__prepare.status__valuehint'} = '{#i18n:formfields__checkin.status__valuehint#}';
MLI18n::gi()->{'formfields__config_invoice_footerCell2__label'} = 'Pied de page colonne 2';
MLI18n::gi()->{'formfields__price.signal__help'} = '';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumber__help'} = '<p>
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
MLI18n::gi()->{'formfields__config_invoice_mailCopy__hint'} = 'Entrez votre adresse email pour recevoir une copie de la facture téléchargée sur Amazon';
MLI18n::gi()->{'formfields__erpInvoiceDestination__help'} = '<p>Une fois que magnalister a chargé une facture depuis le répertoire source sur {#setting:currentMarketplaceName#}, celle-ci est placée dans le répertoire de destination. Ainsi, vous pourrez vérifier quelles factures ont déjà été transmises à {#setting:currentMarketplaceName#}.</p>

<p>Sélectionnez ici le chemin d’accès vers le répertoire cible dans lequel les factures chargées sur {#setting:currentMarketplaceName#} doivent être placées.</p>

<p><b>Important</b> : Si vous ne sélectionnez pas un serveur de destination différent pour les factures chargées sur {#setting:currentMarketplaceName#},  vous ne serez pas en mesure de reconnaître les factures déjà chargées sur {#setting:currentMarketplaceName#}.</p>';
MLI18n::gi()->{'formfields_uploadInvoiceOption_values__webshop'} = 'Transmettre les factures créées dans la boutique';
MLI18n::gi()->{'formfields__erpReversalInvoiceSource__buttontext'} = 'Wählen';
MLI18n::gi()->{'formfields__preimport.start__help'} = '
            Les commandes seront importées à partir de la date que vous saisissez dans ce champ. Veillez cependant à ne pas donner une date trop éloignée dans le temps pour le début de l’importation, car les données sur les serveurs d\'{#setting:currentMarketplaceName#} ne peuvent être conservées, que quelques semaines au maximum. <br>
            <br>
            <b>Attention</b> : les commandes non importées ne seront après quelques semaines plus importables!
        ';
MLI18n::gi()->{'formfields__config_invoice_companyAddressRight__label'} = 'Champ d’adresse de l\'entreprise (droite)';
MLI18n::gi()->{'formfields__config_invoice_footerCell1__label'} = 'Pied de page colonne 1';
MLI18n::gi()->{'formfields__lang__label'} = 'Description d\'article';
MLI18n::gi()->{'formfields__orderimport.shop__label'} = '{#i18n:form_config_orderimport_shop_lable#}';
MLI18n::gi()->{'formfields__config_invoice_footerCell4__label'} = 'Pied de page colonne 4';
MLI18n::gi()->{'formfields__erpInvoiceSource__help'} = '<p>Choisissez ici le chemin d’accès au répertoire dans lequel vous avez téléchargé en PDF les factures créées dans le système tiers (par exemple ERP).</p>

    <b>Important:</b> <br>
<p>Pour que magnalister puisse associer une facture en PDF à une commande dans la boutique, les fichiers PDF doivent être nommés en respectant un modèle précis :</p>
<ol>
    <li><p>Désignation d’après la commande dans la boutique</p>

        <p>Modèle : #numero-de-commande-boutique#.pdf</p>

        <p>Exemple:  <br>
            Numéro de la commande dans votre e-boutique : 12345678<br>
            Facture en PDF : 12345678.pdf</p>
    </li>
    <li>
        <p>Désignation d’après la commande dans la boutique + numéro de facture dans le système ERP</p>

        <p>Modèle :  #numéro-de-commande-boutique#_#numéro-de-facture#.pdf</p>

        <p>Exemple: <br>
            Numéro de la commande dans la boutique : 12345678<br>
            Numéro de facture dans ERP : 9876543<br>
            Facture en PDF : 12345678_9876543.pdf </p>
    </li>
</ol>';
MLI18n::gi()->{'formfields__price.usespecialoffer__label'} = 'Prendre en compte les prix spéciaux';
MLI18n::gi()->{'formfields__mail.originator.adress__label'} = 'Adresse de l\'expéditeur';
MLI18n::gi()->{'formfields__tabident__label'} = '{#i18n:ML_LABEL_TAB_IDENT#}';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__help'} = '
            Note : Idealo ne reconnaît les produits, que "disponible" ou "non disponible" pour vos offres, en conséquence  :<br />
            <br />
            Quantité boutique > 0 = disponible sur Idéalo<br />
            Quantité stock < 1 = non disponible sur Idealo<br />
            <br />
            <br />
            Fonction :<br />
            Synchronisation automatique via CronJob (recommandée)<br />
            <br />
            <br />
            La fonction "Synchronisation automatique" ajuste l\'inventaire Idéalo actuel à, l\'inventaire boutique, toutes les 4 heures. <br />
            <br />
            Les données sont vérifiées et transférées de la base de données, même si les modifications ont été effectuées directement dans la base de données, par exemple, par un système  de gestion de marchandise.<br />
            <br />
            Vous pouvez déclencher une synchronisation manuelle, en cliquant sur le bouton correspondant, dans le groupe de boutons gris en haut à gauche de l\'en-tête magnalister.<br />
            <br />
            Il est aussi possible de synchroniser votre stock, en utilisant un CronJob personnel. Cela n’est possible qu’à partir du tarif “Enterprise”. CronJob vous permet de réduire le délai maximal de synchronisation de vos données à 15 minutes d\'intervalle. Pour opérer la synchronisation, utilisez le lien suivant :<br />
            <i>{#setting:sSyncInventoryUrl#}</i><br />
            <br />
            Toute importation provenant d’un client n’utilisant pas le tarif “Enterprise” ou ne respectant pas le délai de 15 minutes sera bloqué.<br />
            <br />
            Remarque : les paramètres sous "Configuration" → "Téléchargement d\'article" ...<br />
            <br />
            → “Limite journalière de commandes" et<br />
            → “Stock” pour les deux premières options.<br />
            <br />
            ... sont pris en compte.
        ';
MLI18n::gi()->{'formfields__checkin.status__valuehint'} = 'Appliquer uniquement aux articles actifs';
MLI18n::gi()->{'formfields__erpReversalInvoiceDestination__help'} = '<p>Une fois que magnalister a chargé un avoir depuis le répertoire source sur {#setting:currentMarketplaceName#}, celui-ci est placé dans le répertoire de destination. Ainsi, les vendeurs peuvent vérifier quels avoirs ont déjà été transmis à {#setting:currentMarketplaceName#}.</p>

<p>Sélectionnez ici le chemin d’accès vers le répertoire de destination dans lequel doivent être placés les avoirs chargés sur {#setting:currentMarketplaceName#}.</p>

<p><b>Important</b> : Si vous ne souhaitez pas que vos avoirs soient placés dans le répertoire de destination une fois chargés sur {#setting:currentMarketplaceName#}, utilisez le même chemin d’accès pour le répertoire cible et pour le répertoire source.</p>';
MLI18n::gi()->{'formfields__prepare.status__label'} = '{#i18n:formfields__checkin.status__label#}';
MLI18n::gi()->{'formfields__config_invoice_invoiceHintHeadline__label'} = 'Intitulé : notes de facturation';
MLI18n::gi()->{'formfields__price__help'} = '';
MLI18n::gi()->{'formfields__config_invoice_companyAddressRight__default'} = 'Your name
Your street 1

12345 Your town';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumber__help'} = '<p>
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
MLI18n::gi()->{'formfields__orderimport.paymentmethod__help'} = '
            <p>Le mode de paiement, qui sera associé à toutes les commandes d\'{#setting:currentMarketplaceName#}, lors de l\'importation des commandes. Standard: "Attribution automatique"</p>
            <p>Si vous sélectionnez „Attribution automatique", magnalister reprend le mode de paiement, choisi par l\'acheteur sur {#setting:currentMarketplaceName#}.</p>
            <p>Ce paramètre est important pour les factures et l\'impression des bons de livraison et le traitement ultérieur des commandes en boutique, ainsi que dans la gestion des marchandises.</p>
        ';
MLI18n::gi()->{'formfields__mail.content__hint'} = 'Liste des champs disponibles pour "objet" et "contenu".
        <dl>
                <dt>#MARKETPLACEORDERID#</dt>
                        <dd>Marketplace Order Id</dd>
                <dt>#FIRSTNAME#</dt>
                        <dd>Prénom de l\'acheteur</dd>
                <dt>#LASTNAME#</dt>
                        <dd>Nom de l\'acheteur</dd>
                <dt>#EMAIL#</dt>
                        <dd>Adresse E-Mail de l\'acheteur</dd>
                <dt>#PASSWORD#</dt>
                        <dd>Mot de passe de l\'acheteur pour vous connecter à votre boutique. Seulement pour les clients qui seront automatiquement placés, sinon l\'espace réservé sera remplacé par \'(comme on le sait)\'.</dd>
                <dt>#ORDERSUMMARY#</dt>
                        <dd>Résumé des articles achetés. Devrait être à part dans une ligne.<br/><i>Ne peut pas être utilisé dans la ligne objet!</i></dd>
                <dt>#ORIGINATOR#</dt>
                        <dd>Nom de l\'expéditeur</dd>
        </dl>';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumberPrefixValue__label'} = 'Préfixe numéro de facture';
MLI18n::gi()->{'formfields__config_invoice_companyAddressLeft__label'} = 'Adresse de l’entreprise (champ d’adresse de gauche)';
MLI18n::gi()->{'formfields__orderstatus.open__help'} = '
            Déterminez ici le statut, qui déclenche le téléchargement automatiquement d\'une nouvelle commane idéalo, dans votre boutique.<br />
            Si vous utilisez un système de recouvrement de factures, il est conseillé de définir l\'état de la commande par «payé» (configuration → état de la commande).
        ';
MLI18n::gi()->{'formfields__price.factor__label'} = '';
MLI18n::gi()->{'formfields__erpReversalInvoiceDestination__buttontext'} = 'Wählen';
MLI18n::gi()->{'formfields__customergroup__label'} = 'Groupes clients';
MLI18n::gi()->{'formfields__mail.subject__label'} = 'Objet';
MLI18n::gi()->{'formfields__orderstatus.canceled__label'} = 'Annuler la commande avec';
MLI18n::gi()->{'formfields__mail.copy__label'} = 'Copie à l\'expéditeurr';
MLI18n::gi()->{'formfields__config_invoice_invoiceHintText__hint'} = 'Laissez le champ vide si aucune information ne doit figurer sur la facture.';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumberPrefixValue__label'} = 'Préfixe numéro de facture d’annulation';
MLI18n::gi()->{'formfields__config_invoice_footerCell1__default'} = 'Your name
Your street 1

12345 Your town';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumberPrefix__default'} = 'S';
MLI18n::gi()->{'formfields__mwst.fallback__hint'} = 'Taux de TVA utilisé pour les articles hors boutique lors de l\'importation des commandes en %.';
MLI18n::gi()->{'formfields__erpReversalInvoiceSource__help'} = '<p>Sélectionnez ici le chemin d’accès vers le répertoire dans lequel les avoirs issus de votre système tiers (par exemple ERP) ont été stockés au format PDF.</p>

    <b>Important:</b> <br>
<p>Pour que magnalister puisse associer un avoir en PDF à une commande sur votre e-boutique, vous devez nommer vos fichiers PDF en respectant un modèle précis :</p>
<ol>
    <li><p>Désignation d’après la commande dans la boutique</p>

        <p>Modèle : #numero-de-commande-boutique#.pdf</p>

        <p>Exemple:  <br>
            Numéro de la commande dans votre e-boutique : 12345678<br>
            Avoir en PDF : 12345678.pdf</p>
    </li>
    <li>
        <p>Désignation d’après la commande + numéro de l’avoir dans le système ERP</p>

        <p>Modèle :  #numero-de-commande-boutique#_#numero-avoir#.pdf</p>

        <p>Exemple: <br>
            Numéro de la commande dans la boutique : 12345678<br>
            Numéro de l’avoir dans le système ERP : 9876543<br>
            Avoir en PDF : 12345678_9876543.pdf </p>
    </li>
</ol>';
MLI18n::gi()->{'formfields__orderstatus.sync__help'} = '
            La synchronisation automatique de la fonction via CronJob transmet toutes les 2 heures (à partir de 0:00 dans la nuit) le statut actuel des commandes (envoyées) sur Cdisount.<br />
            Ainsi les valeurs venant de la base de données sont vérifiées et appliquées même si des changements, par exemple, dans la gestion des marchandises, sont seulement réalisés dans la base de données.<br />
            <br />
            Un réglage manuel peut être déclenché, en traitant directement une commande de votre boutique en ligne. Vous réglez alors le statut correspondant de la commande et cliquez sur "Actualiser".<br />
            <br />
            Vous pouvez aussi cliquer sur la touche de fonction correspondante dans l\'en-tête de magnalister (à gauche), pour transmettre immédiatement le statut correspondant.<br />
            <br />
            En outre, vous pouvez utiliser la synchronisation des statuts de commande (dès le "tarif Enterprise" - ou au maximum toutes les 15 minutes) en déclenchant les importations via CronJob, et en cliquant sur le lien suivant vers votre boutique:<br />
            <br />
            <i>{#setting:sSyncOrderStatusUrl#}</i><br/><br/>
            <br />
            Les importations déclenchées via CronJob par des clients qui ne sont pas au "tarif Enterprise" ou qui ne respectent pas le délai de 15 minutes, seront bloqués.
        ';
MLI18n::gi()->{'formfields__price.signal__label'} = 'Décimale après la virgule';
MLI18n::gi()->{'formfields__price.signal__help'} = '
                Cette zone de texte sera utilisée dans les transmissions de données vers Kaufland, (prix après la virgule).<br><br>
 
               <strong>Par exemple :</strong><br><br> 
               Valeur dans la zone de texte: 99<br> 
               Prix d\'origine: 5.58<br> 
               Prix final: 5.99<br><br> 
               La fonction aide en particulier, pour les majorations ou les rabais en pourcentage sur les prix.<br>
               Laissez le champ vide si vous souhaitez ne pas transmettre de prix avec une virgule. Le format d\'entrée est un chiffre entier avec max. 2 chiffres.';
MLI18n::gi()->{'formfields__price.signal__hint'} = 'Décimale après la virgule';
MLI18n::gi()->{'formfields__checkin.status__label'} = 'Filtre de statut';
MLI18n::gi()->{'formfields__config_invoice_footerCell3__default'} = 'Your tax number
Your Ust. ID. No.
Your jurisdiction
Your details';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumber__label'} = 'Numéro de facture';
MLI18n::gi()->{'formfields__config_invoice_mailCopy__help'} = '';
MLI18n::gi()->{'formfields__config_invoice_invoiceDir__buttontext'} = 'Afficher';
MLI18n::gi()->{'formfields__config_invoice_mailCopy__label'} = 'Copie de la facture à';
MLI18n::gi()->{'formfields__orderstatus.carrier.default__label'} = 'Expéditeur';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumberPrefix__label'} = 'Préfixe numéro de facture d\'annulation';
MLI18n::gi()->{'formfields__exchangerate_update__valuehint'} = 'Actualiser automatiquement les taux de change';
MLI18n::gi()->{'formfields__mail.send__label'} = 'Envoyer';
MLI18n::gi()->{'formfields__exchangerate_update__label'} = 'Taux de change';
MLI18n::gi()->{'formfields__config_invoice_preview__buttontext'} = 'Aperçus';
MLI18n::gi()->{'formfields__config_invoice_footerCell3__label'} = 'Pied de page colonne 3';
MLI18n::gi()->{'formfields__importactive__label'} = 'Activez l\'importation';
MLI18n::gi()->{'formfields__preimport.start__hint'} = 'Premier lancement';
MLI18n::gi()->{'formfields__erpReversalInvoiceSource__label'} = 'Répertoire source pour les avoirs (chemin d’accès)';
MLI18n::gi()->{'formfields__config_invoice_invoiceHintText__default'} = 'Votre texte d\'information pour la facture';
MLI18n::gi()->{'formfields__erpInvoiceSource__buttontext'} = 'Wählen';
MLI18n::gi()->{'formfields__erpReversalInvoiceDestination__hint'} = '';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumber__label'} = 'Numéro de facture d’annulation';
MLI18n::gi()->{'formfields__erpReversalInvoiceDestination__label'} = 'Répertoire de destination pour les avoirs transmis à {#setting:currentMarketplaceName#} (chemin d’accès)';
MLI18n::gi()->{'formfields__mail.subject__default'} = 'Votre commande sur #SHOPURL#';
MLI18n::gi()->{'formfields__prepare.status__help'} = 'Dans la boutique en ligne, vous pouvez activer ou désactiver des articles. Selon le paramètre choisi ici, seuls les articles actifs seront affichés lors de la préparation des produits.';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumberMatching__label'} = 'Champ de texte libre pour les commandes Shopware';
MLI18n::gi()->{'formfields__config_invoice_headline__default'} = 'Votre facture';
MLI18n::gi()->{'formfields__config_uploadInvoiceOption__help'} = '<p>Choisissez ici comment vous souhaitez envoyer vos factures à {#setting:currentMarketplaceName#} :</p>
<ol>
    <li>
        <p>Ne pas transmettre les factures à {#setting:currentMarketplaceName#}</p>
        <p>Si vous sélectionnez cette option, vos factures ne seront pas transmises à {#setting:currentMarketplaceName#}. Cela signifie que vous
            devrez
            télécharger vos factures par vos propres soins.</p>
    </li>
    {#i18n:formfields_config_uploadInvoiceOption_help_webshop#}
    {#i18n:formfields_config_uploadInvoiceOption_help_erp#}

    <li>
        <p>Charger magnalister de la création et de la transmission des factures</p>
        <p>Sélectionnez cette option si vous souhaitez que magnalister prenne en charge la création et la transmission
            des
            factures. Pour ce faire, veuillez remplir les champs sous “Données pour la création des factures via
            magnalister”.
            La transmission est effective toutes les 60 minutes.</p>
    </li>
</ol>';
MLI18n::gi()->{'formfields__orderimport.shippingmethod__label'} = 'Mode d\'expédition';
MLI18n::gi()->{'formfields_orderimport.shippingmethod_label'} = 'Mode de livraison de la commande';
MLI18n::gi()->{'formfields__erpInvoiceDestination__buttontext'} = 'Wählen';
MLI18n::gi()->{'formfields__config_invoice_footerCell2__default'} = 'Your telephone number
Your fax number
Your homepage
Your e-mail';
MLI18n::gi()->{'formfields__config_invoice_preview__hint'} = 'Vous pouvez ici afficher un aperçu de votre facture avec les données que vous avez saisies.';
MLI18n::gi()->{'formfields__quantity__label'} = 'Variation de stock';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumberMatching__label'} = 'Champ de texte libre pour les commandes Shopware';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__label'} = 'Variation du stock boutique';
MLI18n::gi()->{'formfields__erpInvoiceDestination__label'} = 'Répertoire de destination pour les factures transmises à {#setting:currentMarketplaceName#} (chemin d’accès)';
MLI18n::gi()->{'formfields__erpReversalInvoiceSource__hint'} = '';
MLI18n::gi()->{'formfields__price.addkind__label'} = '';
MLI18n::gi()->{'formfields_orderimport.paymentmethod_label'} = 'Mode de paiement de la commande';
MLI18n::gi()->{'formfields__priceoptions__label'} = 'Groupe clients';
MLI18n::gi()->{'formfields_config_uploadInvoiceOption_help_webshop'} = ' <li><p>Transmettre les factures créées dans la boutique</p>
        <p>Si votre système de e-boutique prend en charge la création des factures, vous pouvez les télécharger sur
            {#setting:currentMarketplaceName#}.</p></li>';
MLI18n::gi()->{'formfields__inventorysync.price__label'} = 'Prix d&apos;article';
MLI18n::gi()->{'formfields__config_invoice_footerCell4__default'} = 'Additional
Information
in the fourth
column';
MLI18n::gi()->{'formfields__preimport.start__label'} = 'Premier lancement de l\'importation';
MLI18n::gi()->{'formfields__config_invoice_companyAddressLeft__default'} = 'Your name, Your street 1, 12345 Your town';
MLI18n::gi()->{'formfields_config_invoice_invoiceNumberOption_values_matching'} = 'Tirer le numéro de commande d’un {#i18n:shop_order_attribute_name#}';
MLI18n::gi()->{'formfields_config_uploadInvoiceOption_help_erp'} = '<li><p>Les factures créées par des systèmes tiers (par exemple un système ERP) sont transmises à {#setting:currentMarketplaceName#}.</p>
        <p>Les factures que vous avez créées à l’aide d’un système tiers (par exemple un système ERP) peuvent être
            déposées
            sur
            le serveur de votre boutique en ligne, récupérées par magnalister et chargées sur la plateforme {#setting:currentMarketplaceName#}. Des
            informations complémentaires apparaissent après le choix de cette option dans l’icône info sous “Paramètres
            pour
            la
            transmission des factures créées à partir d’un système tiers [...]”.</p></li>';
MLI18n::gi()->{'formfields__mwst.fallback__help'} = '
            Si la référence d\'un article n\'est pas reconnue par la boutique lors de l\'importation d\'une commande, la TVA ne peut pas être calculée.<br />
            Nous proposons une solution alternative : la valeur donnée ici s\'applique en pourcentage à chaque produit, dont la TVA n\'est pas connue, lors d\'une importation idealo.
        ';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumberPrefix__label'} = 'Préfixe numéro de facture';
MLI18n::gi()->{'formfields__config_invoice_invoiceHintHeadline__default'} = 'Notes de facturation';
MLI18n::gi()->{'formfields__mail.content__default'} = '<style><!--
    body {
        font: 12px sans-serif;
    }
    table.ordersummary {
            width: 100%;
            border: 1px solid #e8e8e8;
    }
    table.ordersummary td {
            padding: 3px 5px;
    }
    table.ordersummary thead td {
            background: #cfcfcf;
            color: #000;
            font-weight: bold;
            text-align: center;
    }
    table.ordersummary thead td.name {
            text-align: left;
    }
    table.ordersummary tbody tr.even td {
            background: #e8e8e8;
            color: #000;
    }
    table.ordersummary tbody tr.odd td {
            background: #f8f8f8;
            color: #000;
    }
    table.ordersummary td.price,
    table.ordersummary td.fprice {
            text-align: right;
            white-space: nowrap;
    }
    table.ordersummary tbody td.qty {
            text-align: center;
}
--></style>
<p>Bonjour  #FIRSTNAME# #LASTNAME#,</p>
<p>Merci beaucoup pour votre commande! Vous avez commander sur #MARKETPLACE# l’article suivant:</p>
#ORDERSUMMARY#
<p>Plus frais de port.</p>

<p>Sincères salutations</p>
<p>Votre équipe #ORIGINATOR#</p>';
MLI18n::gi()->{'formfields__importactive__help'} = '
            Est-ce que les importations de commandes doivent être effectuées à partir de la place de marché?<br />
            <br />
            Si la fonction est activée, les commandes seront automatiquement importées toutes les heures.<br />
            <br />
            Vous pouvez régler vous-même la durée de l\'importation automatique des commandes en cliquant sur <br />
            "magnalister Admin" → "Configuration globale" → "Importation des commandes".<br />
            <br />
            Vous pouvez déclencher une importation manuellement, en cliquant sur la touche de fonction correspondante dans l\'en-tête de magnalister (à gauche).<br />
            <br />
            En outre, vous pouvez également déclencher l\'importation des commandes (dès le "tarif Enterprise" - au maximum toutes les 15 minutes) Via CronJob, en suivant le lien suivant vers votre boutique: <br />
            <i>{#setting:sImportOrdersUrl#}</i><br />
            <br />
            Les importations de commandes effectuées via CronJob par des clients, qui ne sont pas en "Enterprise tarif", ou qui ne respectent pas les 15 minutes de délai, seront bloqués.
        ';
MLI18n::gi()->{'formfields__config_invoice_invoiceDir__label'} = 'Factures téléchargées';
MLI18n::gi()->{'formfields__config_invoice_invoiceNumberOption__label'} = '';
MLI18n::gi()->{'formfields__exchangerate_update__help'} = '{#i18n:form_config_orderimport_exchangerate_update_help#}';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumberPrefix__hint'} = 'Si vous définissez un préfixe, celui-ci sera placé automatiquement devant le numéro de facture d\'annulation. Exemple : S20000. Le numéro des factures d\'annulation générées par magnalister commence par 20000';
MLI18n::gi()->{'formfields__mail.send__help'} = 'Activez cette fonction si vous voulez qu’un courriel soit envoyé à vos clients, afin de promouvoir votre boutique en ligne.';
MLI18n::gi()->{'formfields__mail.originator.adress__default'} = 'exemple@onlineshop.de';
MLI18n::gi()->{'formfields__inventorysync.price__help'} = '
            Synchronisation automatique via CronJob (recommandée)<br />
            <br />
            Cette mise à jour aura lieu toutes les quatre heures, à moins que vous n’ayez défini d’autres paramètres de configuration. <br />
            Les donnés sont vérifiées et transférées de la base de données, même si les modifications ont été effectuées directement dans la base de données.<br />
            <br />
            Vous pouvez à tout moment effectuer une synchronisation manuelle des prix en cliquant sur le bouton “synchroniser les prix et les stocks” en haut à droite du module, dans le groupe de boutons gris. <br />
            <br />
            Il est aussi possible de synchroniser vos prix, en utilisant un CronJob personnel. Ceci n’est possible qu’à partir du tarif “Enterprise”. CronJob vous permet de réduire le délais maximal de synchronisation de vos données à 15 minutes d\'intervalle. Pour opérer la synchronisation utilisez le lien suivant :<br />
            <i>{#setting:sSyncInventoryUrl#}</i><br />
            <br />
            Toute importation provenant d’un client n’utilisant pas le tarif “Enterprise” ou ne respectant pas le délai de 15 minutes sera bloqué.<br />
            <br />
            Remarque : les paramètres configurés dans “Configuration” → “calcul du prix”, affecterons cette fonction.
        ';
MLI18n::gi()->{'formfields__priceoptions__help'} = '{#i18n:configform_price_field_priceoptions_help#}';
MLI18n::gi()->{'formfields__config_invoice_reversalInvoiceNumberOption__label'} = '';
