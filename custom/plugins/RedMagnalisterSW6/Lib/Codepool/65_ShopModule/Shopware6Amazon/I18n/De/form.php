<?php
/**
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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLI18n::gi()->sAmazon_automatically = '-- Automatisch zuordnen --';
MLI18n::gi()->set('sAmazon_product_bolletpoints_fieldName', 'Kurzbeschreibung', true);
MLI18n::gi()->{'amazon_prepare_apply_form__field__keywords__help'} = '<h3>Produkt-Ranking mit Amazon Schlüsselwörtern optimieren</h3>
<br>
Allgemeine Schlüsselwörter dienen zur Optimierung des Rankings und zur besseren Filterbarkeit auf Amazon. Sie werden während des magnalister Produkt-Uploads unsichtbar am Produkt hinterlegt.
<br><br>
<h2>Optionen für die Übergabe von Allgemeinen Schlüsselwörtern</h2>
1. Keywords immer aktuell aus Web-Shop verwenden (SEO Schlüsselwörter): 
<br><br>
Dabei werden die Schlüsselwörter aus dem SEO keywords-Feld des jeweiligen Produktes im Web-Shop gezogen und an Amazon übermittelt.
<br><br>
2. Allgemeine Schlüsselwörter in magnalister manuell eintragen: 
<br><br>
Wenn Sie nicht die am Web-Shop-Produkt hinterlegten SEO Schlüsselwörter übernehmen möchten, können Sie eigene Schlüsselwörter in diesem Freitextfeld eintragen.
<br><br>
<b>Wichtige Hinweise:</b>
<br><ul>
<li>Wenn Sie Schlüsselwörter manuell eintragen, trennen Sie sie mit einem Leerzeichen (nicht mit Komma!) und achten Sie darauf, dass Sie insgesamt 250 Bytes (Faustregel: 1 Zeichen = 1 Byte. Ausnahme: Sonderzeichen wie Ä, Ö, Ü = 2 Byte) nicht überschreiten.
</li><li>
Wenn im SEO keywords-Feld des Web-Shop-Produkts die Keywords kommagetrennt vorliegen, wandelt magnalister beim Produkt-Upload die Kommas automatisch in Leerzeichen um. Auch hier gilt die Begrenzung auf 250 Bytes.
</li><li>
Wird die zulässige Byte-Zahl überschritten, gibt Amazon nach dem Produkt-Upload möglicherweise eine Fehlermeldung zurück, die Sie im magnalister Fehler-Log einsehen können (Wartezeit bis zu 60 Minuten).
</li><li>
Übergabe von Platinum-Keywords: Sofern Sie Amazon Platin-Händler sind, informieren Sie den magnalister Support darüber. Wir schalten dann die Übergabe der Platinum-Keywords frei. Dabei greift magnalister auf die Allgemeinen Schlüsselbegriffe zurück und übermittelt diese 1:1 an Amazon. Allgemeine Schlüsselbegriffe und Platinum-Keywords sind also identisch.
</li><li>
Abweichende Platinum-Keywords übermitteln: Nutzen Sie das magnalister Attributs-Matching in der Produktvorbereitung. Wählen Sie dafür aus der Liste der verfügbaren Amazon Attribute “Platinum-Schlüsselwörter 1-5” und matchen Sie das entsprechende Webshop-Attribut.
</li><li>
Neben Allgemeinen Schlüsselwörtern gibt es weitere Amazon-relevante Keywords (z.B. Thesaurus Attributschlüsselwörter, Zielgruppen-Keywords oder Themenschlüsselwörter), die Sie ebenfalls über das Attributs-Matching an Amazon übergeben können.
</li></ul>
';
MLI18n::gi()->{'amazon_prepare_apply_form__field__keywords__optional__checkbox__labelNegativ'} = 'Keywords immer aktuell aus Web-Shop verwenden (SEO keywords)';