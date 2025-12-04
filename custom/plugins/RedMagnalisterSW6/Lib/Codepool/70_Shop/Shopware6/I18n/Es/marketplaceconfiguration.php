<?php

MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_label'} = 'Precio de venta de las reglas de precios';
MLI18n::gi()->{'Shopware_Ebay_Configuration_ArticleDescriptionTemplate_sDefault'} = '<style>
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
 <p>#Ti_x008d_TULO#</p>
 <p>#NÚMERODEARTÍCULO#</p>
 <p>#DESCRIPCIONBREVE#</p>
 <p>#IMAGEN1#</p>
 <p>#IMAGEN2#</p>
 <p>#IMAGEN3#</p>
 <p>#DESCRIPCION#</p>
 <p>#DESCRIPCIONMOVIL#</p>
 <p>#Descripcion1# #Campoadicional1#</p>
 <p>#Descripcion2# #Campoadicional2#</p>
 <div>#PROPIEDADES#</div>';
MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_chinesePriceoptions_label'} = 'Precio de las reglas de precios';
MLI18n::gi()->{'Shopware_EBay_Configuration_ShippingMethod_Info'} = '<p>Método de envío que se asigna a todos los pedidos de {#setting:currentMarketplaceName#} en el momento de la importación del pedido. Estándar: «Asignación automática»</p>
 <p>Si seleccionas «Asignación automática», magnalister tomara el Método de envío que el comprador eligió en {#setting:currentMarketplaceName#}. El Método de envío También se puede crear en Shopware > Ajustes > Gastos de envío.</p>
 <p>
 También puedes definir y utilizar todos los demás Métodos de envío disponibles en el listado en Shopware > Ajustes > Gastos de envío.</p>
 <p>Este ajuste es importante para la impresión de facturas y albaranes, y para el procesamiento posterior del pedido en la tienda, así como en la gestión de mercancías.</p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_Payment_sLabel'} = 'Estado de los pagos';
MLI18n::gi()->{'Shopware6_Configuration_PaymentMethod_NotAvailable_Info'} = '<p>Método de pago asignado a todos los pedidos {#setting:currentMarketplaceName#} durante la importación.</p>
 <p>
 También puedes definir y utilizar todos los demás métodos de pago disponibles en la lista en WooCommerce > Configuración > Pago.</p>
 <p> Esta configuración es importante para imprimir facturas y albaranes y para el tratamiento posterior del pedido en la tienda, así como para la gestión de mercancías.</p>';
MLI18n::gi()->{'Shopware6_Marketplace_Configuration_SalesChannel_Info'} = '<p>Canal de ventas de Shopware</p>
<p>Seleccione aquí el canal de ventas de Shopware al que se asignarán los pedidos de este mercado.
Los métodos de envío y pago utilizados por magnalister para estos pedidos también se tomarán de este canal de ventas.</p>
<p>Puedes localizar los canales de ventas de Shopware en el men&uacute; principal de la izquierda, en "Canal de ventas".</p>
<p>Abre el canal de ventas de Shopware deseado y en "Pago y env&iacute;o", configura los m&eacute;todos de pago y env&iacute;o que prefieras.</p>
<p>Estos ajustes estar&aacute;n disponibles como opciones desplegables en el plugin de magnalister (consulta los ajustes posteriores para m&aacute;s detalles).</p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_sLabel'} = 'Estado del pago de eBay en la tienda';
MLI18n::gi()->{'Shopware6_Configuration_PaymentMethod_Available_Info'} = '<p>Método de pago que se asigna a todos los pedidos de eBay durante la importación del pedido. 
 Estándar: «Asignación automática»</p>
 <p>
 Si seleccionas "Asignación automática", magnalister utilizará la forma de pago que el comprador haya seleccionado en eBay.</p>
 <p>
 Esta configuración es importante para imprimir facturas y albaranes y para el tratamiento posterior del pedido en la tienda, así como para la gestión de mercancías.</p>';
MLI18n::gi()->{'Shopware_Amazon_Configuration_PaymentStatus_sLabel'} = 'Estado del pago en la tienda';
MLI18n::gi()->{'Shopware6_Marketplace_Configuration_SalesChannel_Label'} = 'Canal de ventas';
MLI18n::gi()->{'shopware6_configuration_paymentmethod_help'} = '<p>En el men&uacute; desplegable, selecciona el m&eacute;todo de pago que se asignar&aacute; a todos los pedidos de {#setting:currentMarketplaceName#} en el momento de la importaci&oacute;n. Puedes elegir entre los m&eacute;todos de pago que se han configurado en "Pago y env&iacute;o" en el canal de ventas de Shopware seleccionado.</p>
<p>Notas adicionales:</p>
<ul>
<li aria-level="1">
<p>Es obligatorio seleccionar un m&eacute;todo de pago. Si no se selecciona ning&uacute;n m&eacute;todo de pago, magnalister emitir&aacute; un mensaje de error en la parte superior de la pantalla cuando intentes guardar la configuraci&oacute;n.<br><br></p>
</li>
<li aria-level="1">
<p>Tambi&eacute;n aparecer&aacute; un mensaje de error si un m&eacute;todo de pago seleccionado en el desplegable se elimina posteriormente del canal de ventas de Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>La informaci&oacute;n del m&eacute;todo de pago es crucial para la impresi&oacute;n de facturas y albaranes, y para la posterior gesti&oacute;n del pedido tanto en la tienda como en los sistemas de gesti&oacute;n de inventario.</p>
</li>
</ul>';
MLI18n::gi()->{'Shopware_Amazon_Configuration_PaymentStatus_sDescription'} = 'El estado de pago que un nuevo pedido se convertirá automáticamente en la tienda.';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_Order_sLabel'} = 'Estado del pedido';
MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withfrommarketplace_help'} = '<p>Servicio de env&iacute;o de los pedidos</p>
<p>En el men&uacute; desplegable, selecciona el m&eacute;todo de env&iacute;o que se asignar&aacute; a todos los pedidos de {#setting:currentMarketplaceName#} en el momento de la importaci&oacute;n.</p>
<p>Opciones:</p>
<ul>
<li aria-level="1">
<p>Adoptar m&eacute;todo de env&iacute;o de {#setting:currentMarketplaceName#}: magnalister adoptar&aacute; el m&eacute;todo de env&iacute;o seleccionado por el comprador en {#setting:currentMarketplaceName#}. Si este m&eacute;todo de env&iacute;o no est&aacute; ya configurado en los ajustes de env&iacute;o de Shopware, magnalister lo crear&aacute; autom&aacute;ticamente. Tambi&eacute;n se a&ntilde;adir&aacute; autom&aacute;ticamente a la secci&oacute;n "Pago y env&iacute;o" del canal de ventas de Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>Selecciona un m&eacute;todo de env&iacute;o espec&iacute;fico: Elige cualquiera de los m&eacute;todos de env&iacute;o activos del desplegable para aplicarlo a todos los pedidos. Estos son los m&eacute;todos que has configurado en "Pago y env&iacute;o" en el canal de ventas de Shopware seleccionado.</p>
</li>
</ul>
<p>Notas adicionales:</p>
<ul>
<li aria-level="1">
<p>Es obligatorio seleccionar un m&eacute;todo de env&iacute;o. Si no se selecciona ning&uacute;n m&eacute;todo de env&iacute;o, magnalister emitir&aacute; un mensaje de error en la parte superior de la pantalla cuando intentes guardar la configuraci&oacute;n.<br><br></p>
</li>
<li aria-level="1">
<p>Tambi&eacute;n aparecer&aacute; un mensaje de error si un m&eacute;todo de env&iacute;o seleccionado en el desplegable se elimina posteriormente del canal de ventas de Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>La informaci&oacute;n del m&eacute;todo de env&iacute;o es crucial para la impresi&oacute;n de facturas y albaranes, y para la posterior gesti&oacute;n del pedido tanto en la tienda como en los sistemas de gesti&oacute;n de inventario.</p>
</li>
</ul>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_PaidStatus_sDescription'} = '<p>Aquí defines el pago y el estado del pedido que obtendrá un pedido en la tienda al pagar a través de PayPal en Ebay.</p>
 <p>Si un cliente compra tu producto en Hood, el pedido se transferirá a tu tienda inmedíatamente. Por lo tanto, el parámetro de arte de pago se tomará de tu configuración en "método de pago para pedidos" o se establecerá en "Hood".</p>
 <p>Además, magnalister controla cada hora si un cliente ha pagado después de la primera importación del pedido o si ha cambiado su dirección de envío durante 16 días. 
 Por lo tanto, comprobamos los cambios de pedido en el siguiente intervalo de tiempo:
 <ul> 
 <li> 1.5 horas después de la orden cada 15 minutos,</li> <li> base horaria 
 24 horas después de la orden,</li> 
 <li> hasta 48 horas - cada 2 horas</li> 
 <li> hasta 1 semana - cada 3 horas</li> 
 <li> hasta 16 días después de la orden cada 6 horas.</li> </ul>
 </p>';
MLI18n::gi()->{'Shopware_Amazon_Configuration_ShippingMethod_Info'} = '<p>Amazon no proporciona informacion sobre el Método de envío al importar un pedido.</p>
 <p> Por lo tanto, debes seleccionar aquí los métodos de envío disponibles en la tienda web. Puedes determinar el contenido del menú desplegable en Shopware > Configuración > Gastos de envío.
 </p><p> Esta configuración es importante para la impresión de facturas y albaranes, y para el tratamiento posterior del pedido en la tienda, así como en la gestión de mercancías.</p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_OrderStatus_Label'} = 'Permitir el cambio de estado del pedido cuando';
MLI18n::gi()->{'shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help'} = '<p>Servicio de env&iacute;o de los pedidos</p>
<p>En el men&uacute; desplegable, selecciona el m&eacute;todo de env&iacute;o que se asignar&aacute; a todos los pedidos de {#setting:currentMarketplaceName#} en el momento de la importaci&oacute;n.</p>
<p>Opciones:</p>
<ul>
<li aria-level="1">
<p>Selecciona un m&eacute;todo de env&iacute;o espec&iacute;fico: Elige cualquiera de los m&eacute;todos de env&iacute;o activos del desplegable para aplicarlo a todos los pedidos. Estos son los m&eacute;todos que has configurado en "Pago y env&iacute;o" en el canal de ventas de Shopware seleccionado.</p>
</li>
</ul>
<p>Notas adicionales:</p>
<ul>
<li aria-level="1">
<p>Es obligatorio seleccionar un m&eacute;todo de env&iacute;o. Si no se selecciona ning&uacute;n m&eacute;todo de env&iacute;o, magnalister emitir&aacute; un mensaje de error en la parte superior de la pantalla cuando intentes guardar la configuraci&oacute;n.<br><br></p>
</li>
<li aria-level="1">
<p>Tambi&eacute;n aparecer&aacute; un mensaje de error si un m&eacute;todo de env&iacute;o seleccionado en el desplegable se elimina posteriormente del canal de ventas de Shopware.<br><br></p>
</li>
<li aria-level="1">
<p>La informaci&oacute;n del m&eacute;todo de env&iacute;o es crucial para la impresi&oacute;n de facturas y albaranes, y para la posterior gesti&oacute;n del pedido tanto en la tienda como en los sistemas de gesti&oacute;n de inventario.</p>
</li>
</ul>';
MLI18n::gi()->{'form_config_orderimport_exchangerate_update_help'} = '<strong>Básicamente:</strong>
<p>
Si la moneda predeterminada de la tienda web difiere de la moneda del marketplace, magnalister calcula la importación de pedidos y la carga de artículos utilizando el tipo de moneda almacenado en la tienda web.
Al importar los pedidos, magnalister guarda las divisas y los importes 1:1 de la misma forma que lo hace la tienda web al recibir los pedidos.
</p>
<strong>Atención:</strong>
<p>
Al activar esta función aquí, el tipo de cambio almacenado en la tienda web se actualiza con el tipo actual de Yahoo Finanzas.
<u>Esto también mostrará los precios en su tienda web con el tipo de cambio actualizado para las ventas:</u
</p>
<p>
Las siguientes funciones activan la actualización
<ul>
<li>Importación de pedidos</li>
<li>Preparación de artículos</li>
<li>Carga de artículos</li>
<li>Sincronización de existencias/precios</li>
</ul>
</p>
<p>
Si el tipo de moneda de un marketplace no se ha creado en la configuración de moneda de la tienda web, magnalister emite un mensaje de error.
</p>';
MLI18n::gi()->{'form_config_orderimport_exchangerate_update_alert'} = '<strong>Atención:</strong>
<p>
Al activar, el tipo de cambio almacenado en la tienda web se actualiza con el tipo de cambio actual de Yahoo Finanzas.
<u>Esto también mostrará los precios en su tienda web con el tipo de cambio actualizado para las ventas:</u>
</p><p>
Las siguientes funciones activan la actualización
<ul>
<li>Importación de pedidos</li>
<li>Preparación de artículos</li>
<li>Subida de artículos</li>
<li>Sincronización de existencias/precios</li>
</ul>
<p>
';
MLI18n::gi()->{'Shopware6_eBay_Marketplace_Configuration_fixedPriceoptions_help'} = '<p>Con esta función puedes transferir diferentes precios a {#Configuración:NombreDeLugarDeComercioActual#} y sincronizarlos automáticamente.
 </p><p>Para ello, selecciona una regla de precios de tu tienda online utilizando el menú desplegable de al lado (ver más abajo).</p> <p>Selecciona una regla de precios de tu tienda online utilizando el menú desplegable de al lado (ver más abajo).
 <p>Si no introduces un precio para la nueva regla de precios, se utilizará automáticamente el precio por defecto de la tienda online. Esto hace que sea muy fácil guardar un precio diferente incluso para un pequeño número de artículos. También se aplican todos los demás ajustes de precios.
 </p><p><b>Ejemplo de aplicación:</b></p>
 <ul>
 <li>Guarda una regla para tu tienda online en Configuración > Tienda > Generador de reglas, por ejemplo "Clientes {#Configuración:nombredelmarketplaceactual#}"</li>
 <li>En la vista de productos de tu tienda online, añade los precios deseados para la regla en "Precios avanzados".
 </li> </ul> </ul> <ul>';
MLI18n::gi()->{'global_config_price_field_price.discountmode_label'} = 'Modalidad de descuento';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_PaymentStatus_Info'} = 'Estados del pedido que pueden ser activados por los pagos de Hood. 
 Si el pedido tiene un estado diferente, éste no puede ser cambiado por un pago de Hood.<br /><br /> 
 Si no desea ningún cambio de estado basado en el pago de Hood, por favor desactive la casilla de verificación.<br /><br /> 
 <b>Por favor, tenga en cuenta:</b>El estado de los pedidos resumidos sólo se cambiará cuando se pague en su totalidad.';
MLI18n::gi()->{'Shopware_Marketplace_Configuration_PaymentMethod_Info'} = '<p>Método de pago que se asigna a todos los pedidos por correo de {#platformName#} durante la importación del pedido. 
 Estándar: "Asignación automática"</p> <p>
 <p>
 Si seleccionas "Asignación automática", magnalister utilizará el método de pago que el comprador eligió en {#platformName#}.
 El método de pago también se puede crear en Tienda > Configuración > Métodos de pago.</p> <p>
 <p>
 También puedes definir y utilizar todas las demás formas de pago disponibles en la lista de Shopware > Configuración > Formas de pago.</p> <p>
 <p>
 Esta configuración es importante para imprimir facturas y albaranes, y para el posterior procesamiento de pedidos en la tienda, así como para la gestión de mercancías.</p> <p>';
MLI18n::gi()->{'Shopware_Ebay_Configuration_Updateable_PaymentStatus_Label'} = 'Permitir el cambio de estado del número cuando';
