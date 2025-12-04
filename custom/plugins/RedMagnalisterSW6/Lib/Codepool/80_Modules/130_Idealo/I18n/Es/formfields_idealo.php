<?php

MLI18n::gi()->{'formfields__quantity__help'} = 'As stock {#setting:currentMarketplaceName#} supports only "available" or "not available".<br />Here you can define how the threshold for available items.';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__COD'} = 'cash on delivery';
MLI18n::gi()->{'formfields_idealo__shippingmethod__values__Spedition'} = 'Haulage';
MLI18n::gi()->{'formfields_idealo__prepare_title__label'} = 'Title';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__SKRILL'} = 'Skrill';
MLI18n::gi()->{'formfields_idealo__shippingmethod__values__Paketdienst'} = 'Parcel Service';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__PAYPAL'} = 'PayPal';
MLI18n::gi()->{'formfields_idealo__shippingcostmethod__values____ml_weight'} = 'Shipping cost = Product weight';
MLI18n::gi()->{'formfields_idealo__shippingmethod__help'} = 'Select which shipping method should be used for direct-buy offers.';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__1-3days__title'} = '1-3 days';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__BILL'} = 'bill';
MLI18n::gi()->{'formfields_idealo__prepare_image__label'} = 'Product Images';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__CREDITCARD'} = 'Credit Card';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__GIROPAY'} = 'Giropay';
MLI18n::gi()->{'formfields_idealo__prepare_description__label'} = 'Description';
MLI18n::gi()->{'formfields_idealo__shippingtime__optional__checkbox__labelNegativ'} = 'use from configuration';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values____ml_lump__title'} = 'General (taken from right field)';
MLI18n::gi()->{'formfields_idealo__prepare_image__optional__checkbox__labelNegativ'} = '{#i18n:ML_PRODUCTPREPARATION_ALWAYS_USE_FROM_WEBSHOP#}';
MLI18n::gi()->{'formfields_idealo__shippingmethod__values__Download'} = 'Download';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__help'} = '
    <strong>Nota:</strong> Dado que {#setting:currentMarketplaceName#} sólo conoce "disponible" o "no disponible" para sus ofertas, se tiene en cuenta lo siguiente:<br>
    <br>
    <ul>
        <li>Stock cantidad tienda &gt; 0 = disponible en {#setting:currentMarketplaceName#}</li> <li>
        <li>Stock quantity shop &lt; 1 = no disponible en {#setting:currentMarketplaceName#}</li>
    </ul>
    <br>
    <strong>Función:</strong><br>
    <dl>
        <dt>Sincronización automática mediante CronJob (recomendado)</dt>
        <dd>
            La función "Sincronización automática" ajusta el nivel de existencias actual {#setting:currentMarketplaceName#} al nivel de existencias de la tienda cada 4 horas (a partir de las 0:00 horas) (con deducción si es necesario, en función de la configuración).<br /> <br /> <br />Sincronización automática mediante CronJob.
            <br />
            Los valores de la base de datos se comprueban y adoptan, incluso si los cambios solo se han realizado en la base de datos, por ejemplo, mediante un sistema de gestión de mercancías.<br />
            <br />
            Puede iniciar la sincronización manual haciendo clic en el correspondiente botón de función "Sincronización de precios y existencias" en la parte superior derecha del plugin magnalister.<br />
            Además, también puede activar la sincronización de existencias (desde Tarifa plana - máximo cada cuarto de hora) mediante su propio CronJob llamando al siguiente enlace de su tienda:<br />
            <i>{#setting:sSyncInventoryUrl#}</i><br />
            Se bloquean las llamadas a CronJob personalizados de clientes que no estén en la tarifa plana o que se ejecuten con una frecuencia superior a cada cuarto de hora.<br />
        </dd>
    </dl>
    <br />
    <strong>Nota:</strong> Los ajustes en "Configuración" → "Procedimiento de ajuste" ...<br>
    <br>
    → "Límite de pedido por día natural" y<br>
    → "Número de artículos en stock" para las dos primeras opciones<br><br>... se tienen en cuenta.
';
MLI18n::gi()->{'formfields_idealo__paymentmethod__label'} = 'Payment Methods <span class="bull">•</span>';
MLI18n::gi()->{'formfields_idealo__prepare_description__optional__checkbox__labelNegativ'} = '{#i18n:ML_PRODUCTPREPARATION_ALWAYS_USE_FROM_WEBSHOP#}';
MLI18n::gi()->{'formfields_idealo__shippingmethodandcost__label'} = 'Shipping Cost';
MLI18n::gi()->{'formfields_idealo__currency__hint'} = '';
MLI18n::gi()->{'formfields_idealo__shippingcostmethod__values____ml_lump'} = 'ML_COMPARISON_SHOPPING_LABEL_LUMP';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__PRE'} = 'payment in advance';
MLI18n::gi()->{'formfields_idealo__campaignlink__label'} = 'Campaign link';
MLI18n::gi()->{'formfields_idealo__campaignparametername__label'} = 'Nombre del parámetro de campaña';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__3days__title'} = '3 days';
MLI18n::gi()->{'formfields_idealo__currency__label'} = 'Moneda';
MLI18n::gi()->{'formfields_idealo__shippingtime__label'} = 'Shipping Time';
MLI18n::gi()->{'formfields_idealo__shippingcountry__label'} = 'Shipping to';
MLI18n::gi()->{'formfields_idealo__prepare_title__optional__checkbox__labelNegativ'} = '{#i18n:ML_PRODUCTPREPARATION_ALWAYS_USE_FROM_WEBSHOP#}';
MLI18n::gi()->{'formfields_idealo__prepare_image__hint'} = 'Maximum 3 images';
MLI18n::gi()->{'formfields_idealo__paymentmethod__help'} = '
            Select here the default payment methods for comparison shopping portal and direct-buy (multi selection is possible).<br />
            You can change these payment methods during item preparation.<br />
            <br />
            <strong>Caution:</strong> {#setting:currentMarketplaceName#} exclusively accepts PayPal, Sofortüberweisung and credit card as payment methods for direct-buy.';
MLI18n::gi()->{'formfields_idealo__shippingmethodandcost__help'} = 'Please specify the default shipping costs here. You can then adjust the values for the chosen items in the item preparation form.';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__SOFORT'} = 'Sofort&uuml;berweisung';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__4-6days__title'} = '4-6 days';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__CLICKBUY'} = 'Click&Buy';
MLI18n::gi()->{'formfields_idealo__paymentmethod__values__BANKENTER'} = 'bank enter';
MLI18n::gi()->{'formfields_idealo__campaignlink__help'} = 'Aquí puede definir el nombre del parámetro que se utilizará en el enlace de campaña dentro de la URL. Si no se especifica un valor personalizado, se utilizará "mlcampaign" por defecto. Introduzca una cadena sin caracteres especiales (por ejemplo, sin acentos, signos de puntuación ni espacios), como "campaña1".';
MLI18n::gi()->{'formfields_idealo__campaignparametername__help'} = 'To create a campaign link that can be specifically tracked, please enter a string without special characters (e.g., umlauts, punctuation marks, and spaces), such as "everythingmustgoout.".';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__2-3days__title'} = '2-3 days';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__immediately__title'} = 'disponible de inmediato';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__24h__title'} = '24 houers';
MLI18n::gi()->{'formfields_idealo__shippingtimeproductfield__help'} = '            Puedes utilizar la coincidencia de hora de envío para cargar automáticamente atributos almacenados en el artículo como hora de envío en {#setting:currentMarketplaceName#}.<br />
            En el dropdown puedes ver todos los atributos que están definidos actualmente para los artículos. Puedes añadir y utilizar nuevos atributos en cualquier momento a través de la administración de la tienda.';
MLI18n::gi()->{'formfields_idealo__shippingtimeproductfield__label'} = 'Shipping Time (matching)';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__1-2days__title'} = '1-2 days';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__4weeks__title'} = '4 weeks';
MLI18n::gi()->{'formfields_idealo__access.inventorypath__label'} = 'Direction to your CSV table';
MLI18n::gi()->{'formfields_idealo__shippingtimetype__values__3-5days__title'} = '3-5 days';
MLI18n::gi()->{'formfields_idealo__shippingmethod__label'} = 'Direct-buy Shipping Methods';
