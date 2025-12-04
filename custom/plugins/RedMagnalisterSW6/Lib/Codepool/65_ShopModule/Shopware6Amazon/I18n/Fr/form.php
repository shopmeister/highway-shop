<?php

MLI18n::gi()->{'sAmazon_automatically'} = '-- Attribuer automatiquement --';
MLI18n::gi()->{'amazon_prepare_apply_form__field__keywords__optional__checkbox__labelNegativ'} = 'Toujours utiliser les mots clés actuels de la boutique (SEO keywords)';
MLI18n::gi()->{'amazon_prepare_apply_form__field__keywords__help'} = '<h3>Optimisez le classement de vos offres avec les mots-clés Amazon</h3>
<br>
Grâce aux mots-clés Amazon, les vendeurs peuvent optimiser le référencement de leurs produits sur la place de marché. Les mots-clés Amazon ne sont pas affichés dans la description du produit, mais sont stockés de manière invisible sur la fiche produit Amazon.
<br><br>
<h2>Prise en charge des mots-clés Amazon avec magnalister</h2>

1. Toujours utiliser les mots-clés actuels de la boutique (SEO Mots-clés) : 
<br><br>
Si cette case est cochée, magnalister charge les mots-clés depuis le champ “SEO Mots-clés” de vos fiches produits PrestaShop.
<br><br>
2. Saisir les mots-clés manuellement dans la préparation : 
<br><br>
Si vous ne souhaitez pas utiliser les mots-clés meta de vos fiches produits Amazon, vous pouvez les saisir manuellement dans le champ dédié dans la préparation.
<br><br>
<b>Remarques importantes :</b>
<ul><li>
Lorsque vous saisissez les mots-clés manuellement, veuillez les séparer par un espace (pas une virgule !) et assurez-vous que la taille totale ne dépasse pas 250 octets (en général 1 caractère = 1 octet à l’exception des caractères spéciaux tels que Ä, Ö, Ü = 2 octet) .
</li><li>
Si les mots-clés meta dans la fiche produit de votre boutique sont séparés par des virgules, magnalister converti automatiquement les virgules en espaces lors du téléchargement du produit. Vous n\'avez donc rien à changer dans votre boutique.
</li><li>
Si le nombre d\'octets autorisé est dépassé, Amazon peut renvoyer un message d\'erreur après le téléchargement du produit (les messages d’erreurs peuvent être consultés dans l’onglet “Rapports d’erreurs”). Veuillez noter qu\'il peut s\'écouler jusqu\'à 60 minutes avant que des messages d\'erreur ne soient chargés dans l’onglet “Rapports d’erreurs”.
</li><li>
Si vous êtes vendeur Platinum sur amazon, veuillez en informer le service support de magnalister pour que nous puissions débloquer cette option dans votre plugin. Une fois débloqués, magnalister transmet les mots-clés “normaux” en tant que mots-clés Platinum.
</li><li>
Si vous souhaitez envoyer des mots-clés Platinum différents des mots-clés normaux, veuillez utiliser l’appariement d’attributs dans la préparation de l’article et dans le menu déroulant des attributs facultatifs Amazon, sélectionner "Mots-clés platine 1-5" pour faire un mapping avec le champ de vos fiches produit de votre choix.
</li><li>
Il y d’autres types de mots-clés tels que les mots-clés thesaurus (“thesaurus_attribute_keywords”), les mots-clés groupe cible (“target_audience_keywords”), les mots-clés spécifiques (“specific_uses_keywords”) ou les mots-clé de thème (“subject_keywords”) que vous pouvez également sélectionner dans la liste des attributs facultatifs Amazon.
</li></ul>
';
MLI18n::gi()->{'sAmazon_product_bolletpoints_fieldName'} = 'brève description';
