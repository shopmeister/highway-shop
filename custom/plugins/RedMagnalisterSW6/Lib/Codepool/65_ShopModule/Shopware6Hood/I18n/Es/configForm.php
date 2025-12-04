<?php

MLI18n::gi()->{'hood_config_orderimport__field__updateable.paymentstatus__help'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__label'} = 'Método de envío de los pedidos';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentstatus__help'} = 'Selecciona aquí qué estado de pago de la tienda online debe almacenarse en los detalles del pedido durante la importación de pedidos de magnalister.';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__label'} = 'Forma de pago de los pedidos';
MLI18n::gi()->{'hood_config_producttemplate_content'} = '<style>
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
<p>#TITLE#</p>
<p>#ARTNR#</p>
<p>#SHORTDESCRIPTION#</p>
<p>#PICTURE1#</p>
<p>#PICTURE2#</p>
<p>#PICTURE3#</p>
<p>#DESCRIPTION#</p>
<p>#Descripción1# #Campo de texto libre1#</p>
<p>#Descripción2# #Campo de texto libre2#</p>
<div>#PROPERTIES#</div>';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__updateable.paymentstatus__label'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentstatus__label'} = 'Estado del pago en la tienda';
MLI18n::gi()->{'hood_config_orderimport__field__update.paymentstatus__label'} = 'Estado y cambio activo';
MLI18n::gi()->{'hood_config_producttemplate__field__template.content__hint'} = 'Lista de marcadores de posición disponibles para la descripción del producto:
<dl>
    <dt>#TITLE#</dt>
        <dd>Nombre del producto (título)</dd>
    <dt>#ARTNR#</dt>
        <dd>Número de artículo en la tienda</dd>
    <dt>#PID#</dt>
        <dd>ID del producto en la tienda</dd>
    <!--<dt>#PRICE#</dt>
            <dd>Precio</dd>
    <dt>#VPE#</dt>
            <dd>Precio por unidad de embalaje</dd>-->
    <dt>#SHORTDESCRIPTION#</dt>
        <dd>Breve descripción de la tienda</dd>
    <dt>#DESCRIPTION#</dt>
        <dd>Descripción de la tienda</dd>
    <dt>#PCITURE1#</dt>
        <dd>Primera imagen del producto</dd>
    <dt>#PCITURE2# etc.</dt>
            <dd>segunda imagen del producto; con #PCITURE3#, #PCITURE4# etc. se pueden proporcionar más imágenes, tantas como estén disponibles en la tienda.</dd><br><dt>Campos de texto libre del artículo:</dt><br><dt>#Descripción1# #Campo de texto libre1#</dt><dt>#Descripción2# #Campo de texto libre2#</dt><dt>#Descripción..# #Campo de texto libre..#</dt><br><dd> El número después del marcador de posición (por ejemplo #Campo de texto libre1#) corresponde a la posición del campo de texto libre.
                <br> Ver Ajustes > Ajustes básicos > Artículos > Campos de texto libre del artículo</dd><dt>#PROPERTIES#</dt><dd>Una lista de todas las propiedades del producto. La apariencia se puede controlar mediante CSS (ver código de la plantilla estándar)</dd></dl>';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__help'} = '<p>Método de pago que se asigna a todos los pedidos de Hood durante la importación del pedido.
<p>
También puede definir todos los métodos de pago en la lista en Shopware > Configuración > Métodos de pago y luego utilizarlos aquí.
</p>
<p>
Este ajuste es importante para la impresión de facturas y albaranes y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.
</p>';
MLI18n::gi()->{'hood_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'hood_config_orderimport__field__paidstatus__label'} = 'Hood estado de pago en la tienda';
MLI18n::gi()->{'hood_config_orderimport__field__paidstatus__help'} = '<p>Aquí se configura el pago y el estado del pedido que recibe un pedido en la tienda en cuanto se paga en Hood con PayPal.</p> <p>Aquí se configura el pago y el estado del pedido que recibe un pedido en la tienda en cuanto se paga en Hood con PayPal.
<p>
Cuando un cliente realiza una compra en Hood, el pedido se transfiere inmediatamente a su tienda web.
La forma de pago se establece primero en "Hood", o el valor que haya almacenado en "Forma de pago de los pedidos".</p> <p>

<p>
magnalister seguirá supervisando durante 16 días si un comprador en Hood ha realizado un pago posterior o ha cambiado su dirección de envío después de la primera importación del pedido.
Recuperamos los cambios en los siguientes intervalos:


	<ul>
        <li> 1,5 horas después del pedido cada 15 minutos,</li>
	<li> cada hora hasta 24 horas después de realizar el pedido,</li>
	<li> hasta 48 horas - cada 2 horas</li>
	<li> hasta 1 semana - cada 3 horas</li>
	<li> hasta 16 días después del pedido - cada 6 horas</li>
        </ul>

magnalister utiliza la información de Hood que también puede ver en su cuenta de Hood en "Actividad" > "Resumen" > "Sales Manager Pro" > "Vendido" en la 12ª columna (símbolo del euro): Un símbolo en negrita es la indicación de "pagado".
</p><br /><br />
                <b>Nota:</b> El estado de los pedidos resumidos sólo se modifica cuando se han pagado todas las piezas.';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.paid__help'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__paymentstatus.paid__label'} = 'Estado de los pagos';
MLI18n::gi()->{'hood_config_orderimport__field__updateablepaymentstatus__help'} = 'Estado de los pedidos que se puede cambiar para los pagos de Hood.
			                Si el pedido tiene un estado diferente, no se modificará para los pagos de Hood.<br /><br />
			                Si no deseas que el estado del pago se modifique en absoluto para los pagos de Hood, desactiva la casilla de verificación.';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.paid__label'} = 'Estado del pedido';
MLI18n::gi()->{'hood_config_orderimport__field__updateablepaymentstatus__label'} = 'Permitir el estado del número y cambiarlo si';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__paymentstatus.paid__help'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__help'} = '<p>Tipo de envío que se asigna a todos los pedidos de Hood durante la importación del pedido.
Por defecto: "Transferencia desde el marketplace"</p>.
<p>Si seleccionas "Adoptar del marketplace", magnalister adopta el método de envío seleccionado por el comprador en Hood.
Esto también se crea entonces en Shopware > Configuración > Gastos de envío.</p>
<p>También puedes definir todos los demás métodos de envío disponibles en la lista bajo Shopware > Configuración > Gastos de envío y luego utilizarlos aquí.</p>
<p>Este ajuste es importante para la impresión de facturas y albaranes, así como para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.</p>';
MLI18n::gi()->{'hood_config_producttemplate__field__template.content__label'} = 'Plantilla de descripción del producto';
