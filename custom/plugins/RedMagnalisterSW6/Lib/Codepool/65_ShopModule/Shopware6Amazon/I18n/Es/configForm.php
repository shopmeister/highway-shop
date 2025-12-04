<?php

MLI18n::gi()->{'amazon_config_amazonvcsinvoice_reversalinvoicenumberoption_values_matching'} = 'Coincidir el número de factura de anulación con el campo adicional';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__label'} = 'Número de factura de anulación';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__label'} = 'Forma de envío de los pedidos (FBA)';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.carrier__help'} = 'Seleccione aquí la empresa de transporte que se asigna por defecto a los pedidos de Amazon.<br>
<br>
Tiene las siguientes opciones:<br>
<ul>
	<li><span class="negrita subrayada">Transportista sugerido por Amazon</span>.
        <p>
        Selecciona una empresa de transporte de la lista desplegable. Aparecerán las empresas recomendadas por Amazon.<br>
        <br>
        Esta opción es útil si <strong>quieres utilizar siempre la misma empresa de transporte</strong> para los pedidos de Amazon.</p
    </li> <li
    <li><span class="negrita subrayada">{#i18n:amazon_config_transportista_option_group_shopfreetextfield_option_carrier#}</span>
        <p>
        {#i18n:shop_order_attribute_creation_instruction#}<br>
        <br>
        Esta opción es útil si quieres utilizar <strong>diferentes empresas de transporte</strong> para los pedidos de Amazon.</p> <p>
    </li> <li>
    
    <li><span class="negrita subrayada">Coincidir empresas de transporte sugeridas por Amazon con proveedores de servicios de envío del módulo de gastos de envío de la tienda web</span>.
        <p>
        
        Puede hacer coincidir las empresas de transporte recomendadas por Amazon con los proveedores de servicios creados en el módulo de gastos de envío de la tienda web. Puede realizar múltiples coincidencias utilizando el símbolo "+".<br>
        <br>
        Para obtener información sobre qué entrada del módulo de gastos de envío de Shopware se utiliza para la importación de pedidos de Amazon, consulte el icono de información en "Importación de pedidos" -> "Forma de envío de los pedidos".<br>
        <br>
        Esta opción es útil si desea utilizar <strong>configuraciones de gastos de envío existentes</strong> del módulo de gastos de envío de <strong>Shopware</strong>.<br>
        </p>
    </li> <li>
    
    <li><span class="negrita subrayada">magnalister añade un campo de texto libre en los detalles del pedido</span>.
        <p>
        Si selecciona esta opción, magnalister añadirá un campo en los detalles del pedido del pedido Shopware. En este campo puede introducir la empresa de transporte.<br>
        <br>
        Esta opción es útil si desea utilizar <strong>diferentes empresas de transporte</strong> para los pedidos de Amazon.<br>
        </p>
    </li> <li
    <li><span class="negrita subrayada">Introduzca manualmente una empresa de transporte para todos los pedidos en un campo de texto magnalister</span>.
        <p>
        Si selecciona la opción "Otros" en "Empresa de transporte" en magnalister, puede introducir manualmente el nombre de una empresa de transporte en el campo de texto situado a la derecha de la misma.<br>
        <br>
        Esta opción es útil si <strong>desea introducir manualmente la misma empresa de transporte para todos los pedidos de Amazon</strong>.<br
        </p>
    </li> <li>
</ul>
<span class="negrita subrayada">Notas importantes:</span>
<ul>
	<li>La especificación de una empresa de transporte es obligatoria para las confirmaciones de envío en Amazon.<br><br></li>
	<li>El hecho de no proporcionar la empresa de transporte puede suponer la retirada temporal de la autorización de venta.</li> <li>Por favor, tenga en cuenta que la empresa de transporte es obligatoria para las confirmaciones de envío en Amazon.
</ul>
';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__help'} = 'Selecciona aquí qué estado de pago de la tienda online debe almacenarse en los detalles del pedido durante la importación de pedidos de magnalister.';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__label'} = 'Método de envío de los pedidos';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__help'} = '<p>Método de pago que se asigna a todos los pedidos de Amazon durante la importación de pedidos.
Por defecto: "Amazon"</p>
<p>
También puede definir todos los demás métodos de pago disponibles en la lista en Shopware > Configuración > Métodos de pago y luego utilizarlos aquí.
<p>
Este ajuste es importante para la impresión de facturas y albaranes y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__label'} = 'Estado del pago en la tienda';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__help'} = '<p>Amazon no proporciona ninguna información sobre el método de envío al importar pedidos.
<p>Seleccione aquí los métodos de envío disponibles en la tienda web. Puede definir el contenido desde el menú desplegable en Shopware > Configuración > Envío.</p> <p
<p>Este ajuste es importante para la impresión de facturas y albaranes, y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.</p> <p';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbapaymentstatus__label'} = 'Estado del pago en la tienda (FBA)';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__help'} = 'Selecciona aquí qué estado de pago de la tienda online debe almacenarse en los detalles del pedido durante la importación de pedidos de magnalister.';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumberoption__label'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber.matching__label'} = 'Campos adicionales del pedido Shopware';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.fbashippingmethod__help'} = '<p>Amazon no proporciona ninguna información sobre el método de envío al importar pedidos.
<p>Selecciona aquí los métodos de envío disponibles en la tienda web. Puede definir el contenido desde el menú desplegable en Shopware > Configuración > Envío.</p> <p
<p>Este ajuste es importante para la impresión de facturas y albaranes, y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.</p> <p';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__label'} = 'Número de factura';
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_carrier'} = 'Seleccionar empresa de transporte desde un campo adicional de la tienda online (pedidos)';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumberoption__label'} = '';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.invoicenumber__help'} = '<p>
Seleccione aquí si desea que magnalister genere sus números de factura o si deben tomarse de un campo adicional de Shopware.
</p><p>
<b>Generar números de factura a través de magnalister</b>
</p><p>
magnalister genera números de factura consecutivos al crear facturas. Puede definir un prefijo que se coloca delante del número de factura. Ejemplo: R10000.
</p><p>
Nota: Las facturas creadas por magnalister comienzan con el número 10000.
</p><p>
<b>Coincidir los números de factura con el campo adicional de Shopware</b>
</p><p>
Cuando se crea la factura, se adopta el valor del campo adicional de Shopware que hayas seleccionado.
</p><p>
{<i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Importante:</b><br/> magnalister genera y transmite la factura en cuanto el pedido se marca como enviado. Por favor, asegúrese de que el campo adicional se rellena en este momento, de lo contrario se generará un error (salida en la pestaña "Registro de errores").
<br/><br/>
Si utiliza la coincidencia del campo adicional, magnalister no se hace responsable de la creación correcta y consecutiva de los números de factura.
</p>
';
MLI18n::gi()->{'amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod'} = 'Seleccione el servicio de entrega en un campo adicional de la tienda web (pedidos)';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'amazon_config_orderimport__field__orderstatus.shipmethod__help'} = 'Selecciona aquí el servicio de entrega ( = método de envío), que se asigna por defecto a todos los pedidos de Amazon.<br>
<br>
Tienes las siguientes opciones:
<ul>
	<li><span class="bold underline">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_shipmethod#}</span>
        <p>
        Selecciona un servicio de entrega desde un campo adicional de la tienda web.<br>
        <br>
        {#i18n:shop_order_attribute_creation_instruction#}<br>
        <br>
        Esta opción es útil si deseas utilizar <strong>diferentes servicios de entrega</strong> para los pedidos de Amazon.<br>
        </p>
    </li>
	<li><span class="bold underline">Coincidir el servicio de entrega con las entradas del módulo de envío de la tienda web</span>
        <p>
        Puedes hacer coincidir cualquier servicio de entrega con las entradas creadas en el módulo de gastos de envío de Shopware. Puedes hacer múltiples coincidencias utilizando el símbolo "+".<br>
        <br>
        Para obtener información sobre qué entrada del módulo de gastos de envío de Shopware se utiliza para la importación de pedidos de Amazon, consulta el icono de información en "Importación de pedidos" -> "Forma de envío de los pedidos".<br>
        <br>
        Esta opción es útil si deseas utilizar <strong>configuraciones de gastos de envío existentes de</strong> el módulo de gastos de envío de <strong>Shopware</strong>.<br>
        </p>
    </li>
    <li><span class="bold underline">magnalister añade un campo de texto libre en los detalles del pedido</span>.
        <p>
        Si seleccionas esta opción, magnalister añade un campo en los detalles del pedido de Shopware durante la importación del pedido. En este campo puedes introducir el servicio de entrega.<br>
        <br>
        Esta opción es útil si desea utilizar <strong>diferentes servicios de entrega</strong> para los pedidos de Amazon.<br>
        </p>
    </li>
	<li><span class="bold underline">Introduce manualmente un servicio de entrega para todos los pedidos en un campo de texto magnalister</span>.
        <p>
    
        Si seleccionas esta opción en magnalister, puedes introducir manualmente el nombre de un servicio de entrega en el campo de texto situado a la derecha del mismo.<br><br>
        <br>
        Esta opción es útil si <strong>quieres introducir manualmente el mismo servicio de entrega para todos los pedidos de Amazon</strong>.<br>
        </p>
    </li>

</ul>
<span class="bold underline">Notas importantes:</span>
<ul>
	<li>La especificación de un servicio de entrega es obligatoria para las confirmaciones de envío en Amazon.<br><br></li>
	<li>El no proporcionar el servicio de entrega puede dar lugar a una retirada temporal de la autorización de venta.</li>
	<li>Las confirmaciones de envío son obligatorias.</li>
</ul>
';
MLI18n::gi()->{'amazon_config_amazonvcsinvoice_invoicenumberoption_values_matching'} = 'Hacer corresponder los números de factura con el campo adicional';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__label'} = 'Forma de pago de los pedidos';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber__help'} = '<p>
Seleccione aquí si desea que magnalister genere su número de factura de anulación o si debe tomarse de un campo adicional de Shopware.
</p><p>
<b>Generar número de factura de anulación a través de magnalister</b>
</p><p>
magnalister genera números de factura de anulación consecutivos cuando se crea la factura. Puede definir un prefijo que se coloca delante del número de factura. Ejemplo: R10000.
</p><p>
Nota: Las facturas creadas por magnalister comienzan con el número 10000.
</p><p>
<b>Coincidir el número de factura de anulación con el campo adicional de Shopware</b>
</p><p>
Cuando se crea la factura, se adopta el valor del campo adicional de Shopware que hayas seleccionado.
</p><p>
{<i18n:shop_order_attribute_creation_instruction#}
</p><p>
<b>Importante:</b><br/> magnalister genera y transmite la factura en cuanto el pedido se marca como enviado. Por favor, asegúrese de que el campo adicional se rellena en este momento, de lo contrario se generará un error (salida en la pestaña "Registro de errores").
<br/><br/>
Si utiliza la coincidencia del campo adicional, magnalister no se hace responsable de la creación correcta y consecutiva de números de factura de anulación.
</p>
';
MLI18n::gi()->{'amazon_config_vcs__field__amazonvcsinvoice.reversalinvoicenumber.matching__label'} = 'Campos adicionales del pedido Shopware';
