<?php

MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.shippingmethod__label'} = 'Método de envío de los pedidos';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentmethod__label'} = 'Forma de pago de los pedidos';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__label'} = '{#i18n:formfields_orderimport.shippingmethod_label#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.shippingmethod__help'} = '<p>Cdiscount no proporciona ninguna información sobre el método de envío al importar pedidos.</p> <p
<p>Seleccione aquí los métodos de envío disponibles en la tienda web. Puede definir el contenido desde el menú desplegable en Shopware > Configuración > Gastos de envío.</p> <p
<p>Este ajuste es importante para imprimir facturas y albaranes, y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.</p> <p>';
MLI18n::gi()->{'cdiscount_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__label'} = '{#i18n:formfields_orderimport.paymentmethod_label#}';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.paymentmethod__help'} = '{#i18n:shopware6_configuration_paymentmethod_help#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentmethod__help'} = '<p>Método de pago que se asigna a todos los pedidos Cdiscount durante la importación del pedido.
Por defecto: "Cdiscount"</p> <p>Método de pago que se asigna a todos los pedidos Cdiscount durante la importación del pedido.
<p>
También puede definir todos los demás métodos de pago disponibles en la lista en Shopware > Configuración > Métodos de pago y luego utilizarlos aquí.
<p>
Este ajuste es importante para la impresión de facturas y albaranes y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentstatus__label'} = 'Estado del pago en la tienda';
MLI18n::gi()->{'amazon_config_orderimport__field__orderimport.shippingmethod__help'} = '{#i18n:shopware_marketplace_configuration_shippingmethod_withoutfrommarketplace_help#}';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'sCdiscount_automatically'} = '-- Asignar automáticamente --';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderstatus.carrier__help'} = 'Seleccione aquí la empresa de transporte que se asigna por defecto a los pedidos de Cdiscount.<br>
<br>
Dispone de las siguientes opciones:<br>
<ul>
    <li>
        <span class="negrita subrayada">Empresa de transporte sugerida por Cdiscount</span>.
        <p>Seleccione una empresa de transporte de la lista desplegable. Aparecerán las empresas recomendadas por Cdiscount.<br>
            <br>
            Esta opción es útil si <strong>desea utilizar siempre la misma empresa de transporte</strong> para los pedidos de Cdiscount.
        </p>
    </li> <li
    <li>
        <span class="negrita subrayada">{#i18n:amazon_config_carrier_option_group_shopfreetextfield_option_carrier#}</span>
        <p>{#i18n:shop_order_attribute_creation_instruction#}<br>
            <br>
            Esta opción es útil si desea utilizar <strong>diferentes empresas de transporte</strong> para los pedidos de Cdiscount.
        </p>
    </li> <li>
    <li>
        <span class="negrita subrayada">Coincidir empresas de transporte sugeridas por Cdiscount con proveedores de servicios de envío del módulo de envío de la tienda web</span>.
        <p>Puede hacer coincidir las empresas de transporte recomendadas por Cdiscount con los proveedores de servicios creados en el módulo de gastos de envío de Shopware. Puede realizar múltiples coincidencias utilizando el símbolo "+".<br>
            <br>
            Para obtener información sobre qué entrada del módulo de gastos de envío de Shopware se utiliza para la importación de pedidos de Cdiscount, consulte el icono de información en "Importación de pedidos" -> "Forma de envío de los pedidos".<br>
            <br>
            Esta opción es útil si desea utilizar <strong>configuraciones de método de envío existentes</strong> del módulo de gastos de envío de <strong>Shopware</strong>.<br>
        </p>
    </li> <li>
    <li>
        <span class="negrita subrayada">magnalister añade un campo de texto libre en los detalles del pedido</span>.
        <p>Si selecciona esta opción, magnalister añadirá un campo en los detalles del pedido del pedido Shopware. En este campo puede introducir la empresa de transporte.<br>
            <br>
            Esta opción es útil si desea utilizar <strong>diferentes empresas de transporte</strong> para pedidos Cdiscount.<br>
        </p>
    </li> <li>
    <li>
        <span class="negrita subrayada">Introduzca manualmente una empresa de transporte para todos los pedidos en un campo de texto magnalister.</span><br>
        <p>Esta opción es útil si <strong>desea introducir manualmente la misma empresa de transporte para todos los pedidos de Cdiscount</strong>.<br></p>
    </li> <li>
</ul>
<span class="negrita subrayada">Notas importantes:</span>
<ul>
    <li>La especificación de una empresa de transporte es obligatoria para las confirmaciones de envío en Cdiscount.<br><br></li>.
    <li>El hecho de no facilitar la empresa de transporte puede suponer la retirada temporal de la autorización de venta.</li> <li>La empresa de transporte es obligatoria para la confirmación de los envíos en Cdiscount.
</ul>
';
MLI18n::gi()->{'cdiscount_config_orderimport__field__orderimport.paymentstatus__help'} = '<p>Cdiscount no proporciona ninguna información sobre el método de envío al importar pedidos.</p> <p
<p>Seleccione aquí los métodos de envío disponibles en la tienda web. Puede definir el contenido desde el menú desplegable en Shopware > Configuración > Gastos de envío.</p> <p
<p>Este ajuste es importante para imprimir facturas y albaranes, y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.</p> <p>';
