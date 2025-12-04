<?php

MLI18n::gi()->{'obi_config_account_emailtemplate_sender_email'} = 'ejemplo@tiendaonline.de';
MLI18n::gi()->{'formfields__price__label'} = 'Precio';
MLI18n::gi()->{'obi_configform_orderimport_shipping_values__textfield__textoption'} = '1';
MLI18n::gi()->{'formfields_obi_freightforwarding_values__false'} = 'No';
MLI18n::gi()->{'obi_config_account__field__clientid__help'} = '<p>Recibirás tu "Usuario API de OBI" automáticamente por correo electrónico, después de registrar una cuenta de comerciante en OBI y haber pasado la verificación de legitimación.</p>
<strong>Más información:</strong>
<ul>
    <li>Puedes registrarte como comerciante en <a href="https://www.obi.market/" target="_blank">https://www.obi.market/</a>.</li>
    <li>Para que los productos subidos a través de magnalister se muestren en OBI, como nuevo comerciante, debes eliminar la restricción de visibilidad en el backend de OBI para comerciantes y enviar a OBI una "Confirmación de tu comprensión del proceso de pedidos". </br>Más información al respecto la encontrarás aquí: <a href="https://account.obi.market/s/article/Vorbereitungen-Livegang/" target="_blank">https://account.obi.market/s/article/Vorbereitungen-Livegang/</a></li>
</ul>
';
MLI18n::gi()->{'obi_config_account_producttemplate'} = 'Plantilla de producto';
MLI18n::gi()->{'obi_config_general_nosync'} = 'sin sincronización';
MLI18n::gi()->{'obi_config_free_text_attributes_opt_group_value'} = 'magnalister añade un campo en «Detalles del pedido» en los pedidos';
MLI18n::gi()->{'obi_config_account__field__clientid__label'} = 'Usuario API de OBI';
MLI18n::gi()->{'obi_configform_orderstatus_cancelreason__299'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'obi_config_account__legend__account'} = 'Datos de acceso';
MLI18n::gi()->{'formfields__importactive__hint'} = 'Ten en cuenta que los pedidos realizados en el marketplace OBI se registran automáticamente con la compra en la tienda online (importación de pedidos).';
MLI18n::gi()->{'obi_configform_orderimport_payment_values__textfield__title'} = 'Desde el campo de texto';
MLI18n::gi()->{'obi_config_emailtemplate_content'} = '<style><!--
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
 <p>Hola, #NOMBRE# #APELLIDO#:</p>
 <p>Muchas gracias por tu pedido. Has realizado un pedido en nuestra tienda a través de #MARKETPLACE#:
 </p>#RESUMENPEDIDO#
 <p>Se aplican además gastos de envío.
 </p><p>&nbsp;</p>
 <p>Saludos,</p>
 <p>El equipo de la tienda online</p>';
MLI18n::gi()->{'obi_config_account_emailtemplate'} = 'Plantilla de correo electrónico de promoción';
MLI18n::gi()->{'obi_configform_orderimport_payment_values__matching__title'} = 'Asignación automática';
MLI18n::gi()->{'obi_config_account__field__clientsecret__label'} = 'Contraseña de la API de OBI';
MLI18n::gi()->{'formfields__stocksync.tomarketplace__help'} = 'Consejo: idealo sólo admite "disponible" y "no disponible" para tus ofertas.<br /> 
 <br /> 
 Tienda de stock > 0 = disponible en {#i18n:sModuleNameObi#}<br /> 
 Tienda de stock < 1 = no disponible en {#i18n:sModuleNameObi#}<br /> 
 <br /> 
 <br /> 
 Función:<br /> 
 Sincronización automática por CronJob (recomendado)<br /> 
 <br /> 
 <br />
 La función "Sincronización automática por CronJob" comprueba el stock de la tienda cada 4 horas*<br /> 
 <br /> 
 <br /> 
 Este procedimiento comprueba si se han producido cambios en los valores de la base de datos. Los nuevos datos se muestran aunque los cambios hayan sido determinados por un sistema de gestión de inventario.<br /> 
 <br /> 
 Puedes sincronizar manualmente los cambios de existencias haciendo clic en el botón de la cabecera del magnalister, a la izquierda del logotipo de la hormiga.<br /> 
 <br /> 
 También puedes sincronizar los cambios de existencias configurando un cronjob independiente para el enlace de tu tienda:<br /> 
 <i>{#setting:sSyncInventoryUrl#}</i><br /> 
 <br /> Las llamadas de cronjob propias, que superen un cuarto de hora serán bloqueadas.<br /> 
 <br /> 
 <br /> 
 Sugerencia: El valor de configuración "Configuración" → "Presets" ...<br /> 
 <br /> 
 "Orderlimit for one day" y<br /> 
 → "shop stock"<br /> 
 será considerado.';
MLI18n::gi()->{'obi_configform_pricesync_values__auto'} = '{#i18n:obi_config_general_autosync#}';
MLI18n::gi()->{'obi_config_prepare__legend__upload'} = 'Subir artículos: Configuración predeterminada';
MLI18n::gi()->{'obi_configform_orderstatus_cancelreason__249'} = '{#i18n:obi_config_orderstatus_customerwish#}';
MLI18n::gi()->{'obi_config_matching_options'} = 'Opciones de vinculación';
MLI18n::gi()->{'obi_config_carrier_option_matching_option'} = 'Vincula los proveedores de servicios de envió admitidos por el marketplace con los proveedores de servicios de envió definidos en el sistema de la tienda';
MLI18n::gi()->{'formfields__price__help'} = 'Introduce un porcentaje o una constante definida de adición o reducción al precio. Puedes introducir una reducción añadiendo un menos delante del valor.';
MLI18n::gi()->{'obi_config_prepare__field__vat__matching__titledst'} = 'Códigos de impuestos de OBI';
MLI18n::gi()->{'formgroups_legend_quantity'} = 'Almacén';
MLI18n::gi()->{'obi_config_matching_shop_values'} = 'Valores de la tienda';
MLI18n::gi()->{'obi_configform_stocksync_values__rel'} = 'El pedido reduce el stock de la tienda (recomendado)';
MLI18n::gi()->{'obi_config_account__field__warehouseid__label'} = 'ID de almacén';
MLI18n::gi()->{'obi_config_account__field__tabident__label'} = '{#i18n:ML_LABEL_TAB_IDENT#}';
MLI18n::gi()->{'obi_config_prepare__legend__prepare'} = 'Preparación de artículos';
MLI18n::gi()->{'obi_config_prepare__legend__shipping'} = 'Envío';
MLI18n::gi()->{'formfields_obi_freightforwarding_values__true'} = 'Si';
MLI18n::gi()->{'obi_config_orderstatus_nostock'} = 'Sin stock';
MLI18n::gi()->{'sObi_automatically'} = '-- Asignar de forma automática --';
MLI18n::gi()->{'obi_config_orderstatus_nopickup'} = 'No recogida';
MLI18n::gi()->{'obi_config_account_sync'} = 'Precio y existencias';
MLI18n::gi()->{'obi_config_carrier_matching_title_marketplace_carrier'} = 'Proveedores de servicios de envió compatibles con el marketplace';
MLI18n::gi()->{'obi_config_account__field__clientsecret__help'} = '<p>Después de registrar la cuenta de comerciante en OBI y pasar la verificación de legitimación por parte de OBI, recibirás un correo electrónico con tu “Usuario API” de OBI y un enlace para establecer la contraseña. </br>Introduce aquí la “Contraseña API” generada.</p>
<p>Por favor, también presta atención a las notas en el ícono de información bajo “Nombre de usuario API de OBI”.</p>
';
MLI18n::gi()->{'obi_config_account_prepare'} = 'Preparación del artículo';
MLI18n::gi()->{'obi_config_producttemplate_content'} = '<p>#TITLE#</p><p>#ARTNR#</p><p>#SHORTDESCRIPTION#</p><p>#PICTURE1#</p><p>#PICTURE2#</p><p>#PICTURE3#</p><p>#DESCRIPTION#</p>';
MLI18n::gi()->{'obi_configform_stocksync_values__no'} = '{#i18n:obi_config_general_nosync#}';
MLI18n::gi()->{'obi_config_orderstatus_wrongdelivery'} = 'Entrega incorrecta';
MLI18n::gi()->{'obi_config_order__legend__paymentandshipping'} = 'Servicio de pago y envió de pedidos';
MLI18n::gi()->{'obi_configform_orderimport_shipping_values__matching__title'} = 'Asignación automática';
MLI18n::gi()->{'obi_config_account__legend__tabident'} = 'Pestaña';
MLI18n::gi()->{'obi_configform_sync_values__auto'} = '{#i18n:obi_config_general_autosync#}';
MLI18n::gi()->{'obi_config_carrier_option_group_marketplace_carrier'} = 'Proveedores de servicios de envió admitidos por el marketplace:';
MLI18n::gi()->{'obi_config_orderstatus_customerwish'} = 'Deseo del cliente';
MLI18n::gi()->{'obi_config_account_title'} = 'Datos de acceso';
MLI18n::gi()->{'obi_config_account_emailtemplate_sender'} = 'Tienda de ejemplo';
MLI18n::gi()->{'obi_config_general_autosync'} = 'Sincronización automática mediante CronJob (recomendado)';
MLI18n::gi()->{'obi_config_account__field__tabident__help'} = '{#i18n:ML_TEXT_TAB_IDENT#}';
MLI18n::gi()->{'formfields__price__hint'} = '<span style="color: rojo">El recargo de envío seleccionado en "Preparación del artículo" se añade al precio definido aquí</span>.';
MLI18n::gi()->{'obi_config_sync_inventory_import__false'} = 'No';
MLI18n::gi()->{'obi_config_order__legend__orderstatusimport'} = 'Estado del pedido: importación (del marketplace a la tienda)';
MLI18n::gi()->{'obi_config_prepare__field__vat__help'} = 'Debes haber definido al menos un IVA en el sistema de la tienda.';
MLI18n::gi()->{'obi_config_orderstatus_wrongprice'} = 'falscher Preis';
MLI18n::gi()->{'obi_config_prepare__field__vat__matching__titlesrc'} = 'Clases de impuestos de la tienda';
MLI18n::gi()->{'obi_config_free_text_attributes_opt_group'} = 'Campos adicionales';
MLI18n::gi()->{'obi_config_account_price'} = 'Cálculo del precio';
MLI18n::gi()->{'obi_configform_orderstatus_sync_values__auto'} = '{#i18n:obi_config_general_autosync#}';
MLI18n::gi()->{'obi_configform_orderstatus_sync_values__no'} = '{#i18n:obi_config_general_nosync#}';
MLI18n::gi()->{'obi_config_prepare__legend__pictures'} = 'Ajustes para imágenes';
MLI18n::gi()->{'obi_configform_orderimport_payment_values__textfield__textoption'} = '1';
MLI18n::gi()->{'obi_config_sync_inventory_import__true'} = 'Si';
MLI18n::gi()->{'obi_config_prepare__field__vat__hint'} = '';
MLI18n::gi()->{'obi_configform_orderstatus_cancelreason__250'} = '{#i18n:obi_config_orderstatus_nostock#}';
MLI18n::gi()->{'obi_config_account__legend__additionalsettings'} = 'Ajustes avanzados';
MLI18n::gi()->{'obi_config_account_orderimport'} = 'Pedidos';
MLI18n::gi()->{'obi_configform_pricesync_values__no'} = '{#i18n:obi_config_general_nosync#}';
MLI18n::gi()->{'obi_config_account__field__warehouseid__help'} = '<p>Introduce aquí el ID de almacén que has recibido de OBI.</p>';
MLI18n::gi()->{'obi_config_account_emailtemplate_subject'} = 'Tu pedido en #SHOPURL#';
MLI18n::gi()->{'obi_config_account_orderimport_returntrackingkey_title'} = 'Número de envío de las devoluciones';
MLI18n::gi()->{'obi_config_account_orderimport_returntrackingkey_info'} = 'Para el servicio de envío estándar, el campo "Número de devolución" es obligatorio en el marketplace OBI. Como el sistema de la tienda no ofrece estos campos como opción por defecto, tenemos que adaptarlos a campos personalizados.';
MLI18n::gi()->{'obi_config_matching_obi_values'} = 'Valores de OBI';
MLI18n::gi()->{'obi_config_carrier_option_group_additional_option'} = 'Opción adicional:';
MLI18n::gi()->{'obi_config_prepare__field__vat__label'} = 'Impuestos';
MLI18n::gi()->{'obi_configform_sync_values__no'} = '{#i18n:obi_config_general_nosync#}';
MLI18n::gi()->{'obi_configform_orderimport_shipping_values__textfield__title'} = 'Desde el campo de texto';
MLI18n::gi()->{'obi_config_order__legend__orderstatus'} = 'Estado del pedido: sincronización (de la tienda al marketplace)';
MLI18n::gi()->{'obi_configform_pricaandstock_deliverytime'} = 'No matching';
MLI18n::gi()->{'obi_config_carrier_matching_title_shop_carrier'} = 'Proveedores de servicios de envío definidos en el sistema de la tienda (opciones de envío)';
MLI18n::gi()->{'obi_configform_orderstatus_cancelreason__251'} = '{#i18n:obi_config_orderstatus_wrongprice#}';
