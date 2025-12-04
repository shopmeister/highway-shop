<?php

MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.paymentstatus__label'} = 'Estado del pago en la tienda';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.paymentmethod__help'} = '<p>Método de pago que se asigna a todos los pedidos de Ricardo durante la importación de pedidos.
<p>
También puedes definir todos los métodos de pago en la lista en Shopware > Configuración > Métodos de pago y luego utilizarlos aquí.
</p>
<p>
Este ajuste es importante para la impresión de facturas y albaranes y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.
</p>';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.shippingmethod__label'} = 'Método de envío de los pedidos';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.paymentstatus__hint'} = '';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.shippingmethod__help'} = '<p>Tipo de expedición que se asigna a todos los pedidos de Ricardo durante la importación de pedidos. </p>
<p>También puede definir todos los métodos de envío en la lista en Shopware > Ajustes > Gastos de envío y luego utilizarlos aquí.</p> <p>También puede definir todos los métodos de envío en la lista en Shopware > Ajustes > Gastos de envío y luego utilizarlos aquí.
<p>Este ajuste es importante para imprimir facturas y albaranes, y para el posterior procesamiento del pedido en la tienda y en los sistemas de gestión de mercancías.</p> <p>';
MLI18n::gi()->{'ricardo_config_orderimport__field__customergroup__help'} = '{#i18n:global_config_orderimport_field_customergroup_help#}';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.paymentmethod__label'} = 'Forma de pago de los pedidos';
MLI18n::gi()->{'ricardo_config_producttemplate__field__template.content__hint'} = '                Lista de marcadores de posición disponibles para la descripción del producto:
                <dl>
                    <dt>#TITLE#</dt>
                        <dd>Nombre del producto (título)</dd>
                    <dt>#VARIATIONDETAILS#</dt>
                            <dd>Dado que ricardo.ch no soporta variantes, magnalister transmite las variantes como artículos individuales a ricardo.ch.
                            Utiliza este marcador de posición para mostrar los detalles de la variante en la descripción de su artículo</dd>
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
                    <dt>#Imagen1#</dt>
                        <dd>Primera imagen del producto</dd>
                    <dt>#Imagen2# etc.</dt>
                        <dd>segunda imagen del producto; con #Imagen3#, #Imagen4# etc. se pueden transmitir más imágenes, tantas como haya disponibles en la tienda.</dd>
                    <dt>Campos de texto libre del artículo:</dt>
                    <dt>#Descripción1# #Campo de texto libre1#</dt>
                    <dt>#description2# #campo de texto libre2#</dt>
                    <dt>#description..# #campo de texto libre..#</dt>
                        <dd>Transferencia de los campos de texto libre del artículo: 
                        El número después del marcador de posición (por ejemplo, #Campo de texto libre1#) corresponde a la posición del campo de texto libre.
                        Véase Ajustes > Ajustes básicos > Artículos > Campos de texto libre del artículo</dd>.
                    <dt>#PROPERTIES#</dt>
                        <dd>Una lista de todas las propiedades del producto. La apariencia se puede controlar mediante CSS (ver código de la plantilla estándar)</dd>
                </dl>';
MLI18n::gi()->{'ricardo_config_producttemplate__field__template.content__label'} = 'Plantilla de descripción del producto';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'ricardo_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
