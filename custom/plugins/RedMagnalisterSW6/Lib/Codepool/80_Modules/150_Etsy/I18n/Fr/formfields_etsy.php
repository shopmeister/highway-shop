<?php

MLI18n::gi()->{'formfields_etsy__fixed.price.signal__help'} = 'This textfield shows the decimal value that will appear in the item price on Etsy.';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__BILL'} = 'bill';
MLI18n::gi()->{'formfields__quantity__help'} = 'As stock {#setting:currentMarketplaceName#} supports only "availible" or "not availible".<br />Here you can define how the threshold for availible items.';
MLI18n::gi()->{'formfields_etsy__whenmade__values__2010_2019'} = '2010-2019';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1700s'} = '1700s';
MLI18n::gi()->{'formfields_etsy__whenmade__values__before_2004'} = 'Before 2004';
MLI18n::gi()->{'formfields_etsy__shippingprofilemindeliverydays__help'} = 'Le durée minimale de livraison de la marchandise.';
MLI18n::gi()->{'formfields_etsy__shippingprofileoriginpostalcode__help'} = 'Le code postal de la ville à partir de laquelle l\'offre est envoyée (pas nécessairement un chiffre).';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__EUR'} = '€ Euro';
MLI18n::gi()->{'formfields_etsy__prepare_description__optional__checkbox__labelNegativ'} = 'Utiliser la description de l\'article toujours à jour depuis la boutique en ligne';
MLI18n::gi()->{'formfields_etsy__shop.currency__label'} = 'Etsy Currency';
MLI18n::gi()->{'formfields_etsy__whenmade__values__2020_2024'} = '2020-2024';
MLI18n::gi()->{'formfields_etsy__shop.language__values__it'} = 'Italiano';
MLI18n::gi()->{'formfields_etsy__shippingprofile__label'} = 'Default delivery profile';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__AUD'} = '$ Australian Dollar';
MLI18n::gi()->{'formfields_etsy__shop.language__values__ru'} = 'Русский';
MLI18n::gi()->{'formfields_etsy__prepare.issupply__label'} = 'What is it?';
MLI18n::gi()->{'formfields_etsy__prepare_price__label'} = 'Price';
MLI18n::gi()->{'formfields_etsy__shippingprofileprimarycost__label'} = 'Primary cost';
MLI18n::gi()->{'formfields_etsy__category__label'} = 'Category';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__SGD'} = '$ Singapore Dollar';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__help'} = '<p>Définissez ici si et comment magnalister doit transférer les modifications de stock de votre boutique en ligne vers Etsy :</p>
<p>1. Pas de synchronisation</p>
<p>Le stock ne sera pas synchronisé de votre boutique en ligne vers Etsy.</p>
<p>2. Synchronisation automatique <strong>avec</strong> stock nul (recommandé)</p>
<p>Le stock sera automatiquement synchronisé de votre boutique en ligne vers Etsy. Cela s\'applique également aux produits avec des stocks < 1. Ces produits seront désactivés et réactivés automatiquement dès que le stock sera > 0.</p>
<p><strong>Note importante :</strong> Des frais sont appliqués par Etsy pour la réactivation des articles.</p>
<p>3. Synchronisation automatique <strong>sans</strong> stock nul</p>
<p>Le stock sera automatiquement synchronisé uniquement s\'il est > 0. Les articles ne seront <strong>pas réactivés automatiquement</strong> sur Etsy, même s\'ils sont de nouveau en stock dans la boutique en ligne. Cela permet d\'éviter des frais imprévus.</p>
<p><strong>Remarques générales :</strong></p>
<ul>
<li>Variantes de produits : La synchronisation automatique des stocks des variantes de produits (même avec un stock < 1) est gratuite sur Etsy, tant qu\'au moins une variante du produit a un stock > 0.<br /><br /></li>
<li>Vous pouvez réactiver manuellement des produits individuels en mettant leur stock > 0 dans la boutique en ligne et en relançant le téléchargement du produit via l\'interface magnalister.<br /><br /></li>
<li>La synchronisation automatique des stocks s\'effectue toutes les 4 heures via un CronJob. Le cycle commence quotidiennement à 00:00. Les valeurs de la base de données sont vérifiées et mises à jour, même si les modifications ont été faites uniquement dans la base de données, par exemple via un système de gestion des stocks.<br /><br /></li>
<li>Vous pouvez également lancer une synchronisation des stocks (à partir du tarif Enterprise - toutes les 15 minutes maximum) via votre propre CronJob en utilisant le lien suivant vers votre boutique :<br /><br />{#setting:sSyncInventoryUrl#}<br /><br />Les appels CronJob par les clients qui ne sont pas au tarif Enterprise ou qui sont plus fréquents que toutes les 15 minutes seront bloqués.<br /><br /></li>
<li>Vous pouvez lancer une synchronisation manuelle en cliquant sur le bouton de fonction correspondant dans l\'en-tête en haut à droite.<br /><br /></li>
<li>Pour plus d\'informations sur les frais Etsy, consultez le <a href="https://help.etsy.com/hc/en-us/articles/360000344908">Centre d\'aide Etsy</a><br /><br /></li>
</ul>
<p> </p>';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Direktkauf & Suchmaschine:__PAYPAL'} = 'PayPal';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Direktkauf & Suchmaschine:__CREDITCARD'} = 'Credit Card';
MLI18n::gi()->{'formfields_etsy__shippingprofileorigincountry__help'} = 'Country from which the listing ships';
MLI18n::gi()->{'formfields_etsy__whomade__values__i_did'} = 'I did';
MLI18n::gi()->{'formfields_etsy__shop.language__values__pt'} = 'Português';
MLI18n::gi()->{'formfields_etsy__shop.language__values__pl'} = 'Polski';
MLI18n::gi()->{'formfields_etsy__whenmade__values__2000_2003'} = '2000-2003';
MLI18n::gi()->{'formfields_etsy__fixed.price__label'} = 'Price';
MLI18n::gi()->{'formfields_etsy__issupply__values__true'} = 'A supply or tool to make things';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__GBP'} = '£ British Pound';
MLI18n::gi()->{'formfields_etsy__shippingprofiletitle__label'} = 'Delivery profile title';
MLI18n::gi()->{'formfields_etsy__orderstatus.shipping__label'} = 'Shipping provider';
MLI18n::gi()->{'formfields_etsy__shippingprofileminprocessingtime__label'} = 'Durée minimale de traitement';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1930s'} = '1930s';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__SKRILL'} = 'Skrill';
MLI18n::gi()->{'formfields_etsy__fixed.price.signal__label'} = 'Decimal Amount';
MLI18n::gi()->{'formfields_etsy__shippingprofiledestinationcountry__help'} = 'Country where the listing is shipped';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1900s'} = '1900s';
MLI18n::gi()->{'formfields_etsy__shippingprofileoriginpostalcode__label'} = 'Code postal du lieu de départ';
MLI18n::gi()->{'formfields_etsy__prepare_image__label'} = 'Product Images';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1990s'} = '1990s';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1800s'} = '1800s';
MLI18n::gi()->{'formfields_etsy__shippingprofilesend__label'} = 'Save delivery profile';
MLI18n::gi()->{'formfields_etsy__shop.language__values__es'} = 'Español';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__USD'} = '$ United States Dollar';
MLI18n::gi()->{'formfields_etsy__whenmade__values__before_1700'} = 'Before 1700';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__CHF'} = 'Swiss Franc';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1910s'} = '1910s';
MLI18n::gi()->{'formfields_etsy__shippingprofiledestinationregion__help'} = 'Region where the listing is shipped available values (inside EU, Outside EU and none)';
MLI18n::gi()->{'formfields_etsy__shippingprofilemaxdeliverydays__help'} = 'Le durée maximale de livraison en jours.';
MLI18n::gi()->{'formfields_etsy__prepare.language__label'} = 'Language';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1920s'} = '1920s';
MLI18n::gi()->{'formfields_etsy__whomade__values__collective'} = 'A member of my shop';
MLI18n::gi()->{'formfields_etsy__issupply__values__false'} = 'A finished product';
MLI18n::gi()->{'formfields_etsy__shippingprofilemaxprocessingtime__help'} = 'Le durée maximale du traitement de l\'offre.';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__HKD'} = '$ Hong Kong Dollar';
MLI18n::gi()->{'formfields_etsy__shop.language__values__ja'} = '日本語';
MLI18n::gi()->{'formfields_etsy__shippingprofilesecondarycost__label'} = 'Secondary cost';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__NOK'} = 'kr Norwegian Krone';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__COD'} = 'cash on delivery';
MLI18n::gi()->{'formfields_etsy__fixed.price.signal__hint'} = 'Decimal Amount';
MLI18n::gi()->{'formfields_etsy__shippingprofilemindeliverydays__label'} = 'Durée minimale de livraison';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1960s'} = '1960s';
MLI18n::gi()->{'formfields_etsy__prepare_title__optional__checkbox__labelNegativ'} = 'Always use product title from web-shop';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__SEK'} = 'kr Swedish Krona';
MLI18n::gi()->{'formfields_etsy__shippingprofiledestinationcountry__label'} = 'Destination country';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__CAD'} = '$ Canadian Dollar';
MLI18n::gi()->{'formfields_etsy__prepare_image__help'} = 'A maximum of 10 images can be set.<br/>The maximum allowed image size is 3000 x 3000 px.';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1970s'} = '1970s';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__GIROPAY'} = 'Giropay';
MLI18n::gi()->{'formfields_etsy__paymentmethod__help'} = '
            Select here the default payment methods for comparison shopping portal and direct-buy (multi selection is possible).<br />
            You can change these payment methods during item preparation.<br />
            <br />
            <strong>Caution:</strong> {#setting:currentMarketplaceName#} exclusively accepts PayPal, Sofortüberweisung and credit card as payment methods for direct-buy.';
MLI18n::gi()->{'formfields_etsy__shop.language__values__en'} = 'English';
MLI18n::gi()->{'formfields_etsy__prepare_price__help'} = 'Minimum item price on Etsy is 0.17£';
MLI18n::gi()->{'formfields_etsy__prepare_title__label'} = 'Title';
MLI18n::gi()->{'formfields_etsy__shippingprofiledestinationregion__label'} = 'Destination region';
MLI18n::gi()->{'formfields_etsy__shop.language__values__de'} = 'Deutsch';
MLI18n::gi()->{'formfields_etsy__shop.language__values__nl'} = 'Nederlands';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1950s'} = '1950s';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__CLICKBUY'} = 'Click&Buy';
MLI18n::gi()->{'formfields_etsy__shop.language__values__fr'} = 'Français';
MLI18n::gi()->{'formfields_etsy__shippingprofile__hint'} = '<button id="shippingprofileajax" class="mlbtn action add-matching" value="Secondary_color" style="display: inline-block; width: 45px;">+</button>';
MLI18n::gi()->{'formfields_etsy__prepare.imagesize__label'} = 'Image size';
MLI18n::gi()->{'formfields_etsy__shippingprofilesecondarycost__help'} = 'The shipping fee for this item, if shipped with another item';
MLI18n::gi()->{'formfields_etsy__whomade__values__someone_else'} = 'Another company or person';
MLI18n::gi()->{'formfields_etsy__shippingprofileprimarycost__help'} = 'The shipping fee for this item, if shipped alone';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__TWD'} = 'NT$ Taiwan New Dollar';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__PRE'} = 'payment in advance';
MLI18n::gi()->{'formfields_etsy__shippingprofilemaxdeliverydays__label'} = 'Durée maximale de livraison ';
MLI18n::gi()->{'formfields_etsy__shippingprofileminprocessingtime__help'} = 'La durée minimale de traitement de l\'offre.';
MLI18n::gi()->{'formfields_etsy__whenmade__values__made_to_order'} = 'Made to order';
MLI18n::gi()->{'formfields_etsy__shippingprofilemaxprocessingtime__label'} = 'Durée maximale du traitement';
MLI18n::gi()->{'formfields_etsy__whenmade__values__2004_2009'} = '2004-2009';
MLI18n::gi()->{'formfields_etsy__prepare.whenmade__label'} = 'When did you make it?';
MLI18n::gi()->{'formfields_etsy__prepare_quantity__help'} = 'Maximum item on Etsy is 999';
MLI18n::gi()->{'formfields_etsy__access.token__label'} = 'Etsy Token';
MLI18n::gi()->{'formfields_etsy__prepare_image__hint'} = 'Maximum 10 images';
MLI18n::gi()->{'formfields_etsy__shippingprofileorigincountry__label'} = 'Origin country';
MLI18n::gi()->{'formfields_etsy__prepare_description__help'} = 'Maximum number of characters is 63000.';
MLI18n::gi()->{'formfields_etsy__paymentmethod__label'} = 'Payment Methods';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Direktkauf & Suchmaschine:__SOFORT'} = 'Sofort&uuml;berweisung';
MLI18n::gi()->{'formfields_etsy__fixed.price__help'} = 'Please enter a price markup or markdown, either in percentage or fixed amount. Use a minus sign (-) before the amount to denote markdown.';
MLI18n::gi()->{'formfields_etsy__fixed.price.addkind__label'} = '';
MLI18n::gi()->{'formfields_etsy__paymentmethod__values__Nur Suchmaschine:__BANKENTER'} = 'bank enter';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1940s'} = '1940s';
MLI18n::gi()->{'formfields_etsy__shop.language__label'} = 'Etsy Language';
MLI18n::gi()->{'formfields_etsy__prepare.imagesize__help'} = '';
MLI18n::gi()->{'formfields_etsy__whenmade__values__1980s'} = '1980s';
MLI18n::gi()->{'formfields_etsy__prepare_description__label'} = 'Description';
MLI18n::gi()->{'formfields_etsy__prepare_quantity__label'} = 'Quantity';
MLI18n::gi()->{'formfields_etsy__prepare.whomade__label'} = 'Who made it?';
MLI18n::gi()->{'formfields_etsy__fixed.price.factor__label'} = '';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__NZD'} = '$ New Zealand Dollar';
MLI18n::gi()->{'formfields_etsy__prepare_image__optional__checkbox__labelNegativ'} = 'Utiliser des images toujours actuelles de la boutique en ligne';
MLI18n::gi()->{'formfields_etsy__shop.currency__values__DDK'} = 'kr Danish Krone';

MLI18n::gi()->{'formfields_etsy__processingprofile__label'} = 'Profil de traitement par défaut';
MLI18n::gi()->{'formfields_etsy__processingprofile__hint'} = '';
MLI18n::gi()->{'formfields_etsy__processingprofile__help'} = 'Un profil de traitement définit comment et quand votre commande et son produit seront préparés et expédiés au client. Sur Etsy, cela inclut des options comme:
                    <ul>
                    <li>"<strong>Prêt à expédier</strong>" - le produit est déjà fabriqué et peut être expédié immédiatement</li>
                    <li>"<strong>Fait sur commande</strong>" - le produit est créé après l\'achat</li>
                    </ul>
                    <strong>Créer des profils de traitement:</strong><br>
                    Les nouveaux profils de traitement doivent être créés directement sur Etsy:<br>
                    → <a href="https://www.etsy.com/your/shops/me/tools/shipping-profiles" target="_blank">https://www.etsy.com/your/shops/me/tools/shipping-profiles</a><br>
                    ou dans le portail Etsy sous <strong>Paramètres → Paramètres de livraison</strong>.<br>
                    Après les avoir créés sur Etsy, attendez quelques minutes et actualisez cette page (F5) pour que les profils apparaissent ici.<br><br>
                    Le profil de traitement aide les acheteurs à comprendre le délai d\'expédition prévu pour chaque produit.';
MLI18n::gi()->{'formfields_etsy__processingprofiletitle__label'} = 'Profil de traitement';
MLI18n::gi()->{'formfields_etsy__processingprofilereadinessstate__label'} = 'État de préparation';
MLI18n::gi()->{'formfields_etsy__processingprofilereadinessstate__help'} = 'Définir l\'état de préparation pour montrer aux acheteurs quand les produits sont expédiés: 
                    <ul>
                    <li><strong>Prêt à expédier</strong> - l\'article est déjà fabriqué et en stock. Il peut être emballé et expédié immédiatement après l\'achat.</li>
                    <li><strong>Fait sur commande</strong> - L\'article n\'est pas pré-fabriqué. Il sera créé ou personnalisé après que l\'acheteur ait passé commande, l\'expédition prendra donc plus de temps.</li>
                    </ul>';
MLI18n::gi()->{'formfields_etsy__processingprofileminprocessingtime__label'} = 'Jours minimum de traitement';
MLI18n::gi()->{'formfields_etsy__processingprofileminprocessingtime__help'} = 'Le nombre minimum de jours pour traiter la commande.';
MLI18n::gi()->{'formfields_etsy__processingprofilemaxprocessingtime__label'} = 'Jours maximum de traitement';
MLI18n::gi()->{'formfields_etsy__processingprofilemaxprocessingtime__help'} = 'Le nombre maximum de jours pour traiter la commande.';
MLI18n::gi()->{'formfields_etsy__processingprofilesend__label'} = '';
