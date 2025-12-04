<?php

MLI18n::gi()->{'hood_config_price__field__fixed.price.signal__hint'} = 'Importe decimal';
MLI18n::gi()->{'hood_config_sync__field__syncproperties__help'} = 'Si activas la sincronización EAN y MPN, podrás transferir los valores correspondientes a Hood con un solo clic (medíante el nuevo botón de sincronización situado a la izquierda del botón de importación de pedidos).<br />
 <br />
 Esto también sincroniza los artículos que no aparecen en magnalister. El stock de Hood se indica mediante el número de artículo, siempre que sea idéntico tanto en Hood como en tu tienda online (compara en &ldquo;magnalister&rdquo; > &ldquo;Hood&rdquo; > &ldquo;Stock&rdquo;). La primera sincronización puede tardar hasta 24 horas.<br />
 <br />
 Para las <b>Variaciones</b>, se utiliza el EAN del artículo maestro, si no se encuentra ningún EAN para la variación en la base de datos de la tienda (la mayoría de los sistemas de tienda de la familia OsCommerce no pueden manejar los EAN de las variaciones). Si no hay un EAN para el artículo principal, y no todas las variaciones tienen EAN, uno de los EAN existentes se utilizará también para las variaciones restantes del artículo. Si has reservado el complemento "Sincronización EAN y NMP", los valores también se rellenan durante la sincronización "normal" de precios y existencias.<br />
 <br />
 *También puedes introducir el ISBN o el UPC en el campo EAN. magnalister reconoce automáticamente qué número de identificación has introducido.<br /><br />
 <b>Importante</b> para osCommerce: Instalación de la expansión de EAN<br />
 osCommerce no tiene por defecto ningún campo para el EAN. ante en contacto con nosotros para obtener más información sobre cómo ampliar la base de datos de la tienda y los formularios para que gestionen EAN.<br /><br />
 <b>Consejos:</b><br />
 Hood te permite enviar números comodín para EAN y MPN en lugar del número real. Los productos con estos números comodín tendrán una clasificación inferior en Hood, lo que significa que no se encontrarán tan fácilmente.<br />
 <br />
 magnalister transfiere estos números comodín desde Hood para los artículos en los que no se encuentra ningún EAN o MPN para que puedas realizar cambios en los artículos existentes.';
MLI18n::gi()->{'hood_config_account__field__mpusername__help'} = 'Por favor, introduce tu nombre de usuario Hood.';
MLI18n::gi()->{'hood_config_prepare__field__mwst__label'} = 'IVA por defecto';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__18'} = '18 días';
MLI18n::gi()->{'hood_config_account_price'} = 'Cálculo del precio';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.originator.name__label'} = 'Nombre del remitente';
MLI18n::gi()->{'hood_config_price__field__chinese.price.signal__hint'} = 'Importe decimal';
MLI18n::gi()->{'hood_config_price__field__chinese.price.signal__help'} = 'Este campo de texto muestra el valor decimal que aparecerá en el precio del artículo en Hood.< br/><br/> 
 <strong>Ejemplo:</strong> <br /> 
 Valor en textfeld: 99 <br /> 
 Precio original: 5,58 <br /> 
 Importe final: 5,99 <br /><br /> 
 Esta función es útil cuando se marca el precio hacia arriba o hacia abajo***. <br/> 
 Deja este campo en blanco si no quieres establecer una cantidad decimal. <br/> 
 El formato requiere un máximo de 2 números.';
MLI18n::gi()->{'hood_config_account__field__currency__label'} = 'Moneda';
MLI18n::gi()->{'hood_config_price__field__chinese.duration__help'} = 'Ajuste anticipado de la duración de la subasta. Este ajuste puede modificarse en la preparación del artículo.';
MLI18n::gi()->{'hood_config_account_orderimport'} = 'Importación de pedidos';
MLI18n::gi()->{'hood_configform_sync_values__no'} = '{#i18n:hood_config_general_nosync#}';
MLI18n::gi()->{'hood_config_price__field__chinese.price__help'} = 'Por favor, introduce un margen o una reducción de precio, ya sea como porcentaje o como importe fijo. Utiliza un signo menos (-) antes del importe para indicar la reducción de precio.';
MLI18n::gi()->{'hood_configform_pricesync_values__no'} = '{#i18n:hood_config_general_nosync#}';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.shipped__hint'} = '';
MLI18n::gi()->{'hood_config_account__field__token__label'} = 'Token de Hood';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.revoked__label'} = 'Cancelar (vía cliente)';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__5'} = '5 días';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.shipped__help'} = 'Por favor, configura el estado de la tienda para que se active el estado "Envío confirmado" en Hood.';
MLI18n::gi()->{'hood_config_prepare__field__picturepack__help'} = '<b>Paquete de imágenes</b><br /><br /> 
 Si activas la función &quot;Paquete de imágenes&quot;, puedes mostrar hasta 12 imágenes por cada artículo. El comprador puede ver las imágenes en un formato mayor y ampliar partes de la imagen. No se requiere ninguna configuración especial en tus cuentas de Hood.<br /><br /> 
 <b>Imágenes de variación</b><br /><br /> 
 Si tienes imágenes de variaciones de un artículo, también puedes transferirlas a Hood (hasta 12 imágenes por variación)..<br /><br /> 
 <b>Nota</b><br /><br /> 
 magnalister sólo puede procesar los datos proporcionados por tu sistema de tienda. Si tu sistema de tienda no admite imágenes de variación, esta función no estará disponible en magnalister.<br /><br /> 
 <b>&quot;Imágenes de gran tamaño&quot; y &quot;Zoom&quot; </b><br /><br /> 
 Por favor, utiliza imágenes de tamaño suficiente para poder utilizar las funciones &quot;Imágenes grandes&quot; y &quot;Zoom&quot;. Si una imagen es demasiado pequeña (menos de <b>1000px</b> en el lado más largo), se utilizará pero recibirás una advertencia en la vista de registro de errores de magnalister.<br /><br /> 
 <b>Uso de direcciones https para imágenes (URLs seguras)</b><br /><br /> 
 Hood no permite URL seguras para las imágenes si se especifican directamente como dirección en los datos del artículo. Nuestro paquete de imágenes utiliza el servicio de imágenes de Hood para almacenar las imágenes, por lo que sí admite URL seguras.<br /><br /> 
 <b>Duración del procesamiento</b><br /><br /> 
 Con el paquete de imágenes, las imágenes se suben primero a Hood y luego se adjuntan al artículo correspondiente. Esto puede llevar entre 2 y 5 segundos por imagen, dependiendo del tamaño de la misma.<br /><br /> 
 Para que la tienda tenga una velocidad de procesamiento razonable, los datos se almacenan en el servidor de magnalister. Los posibles mensajes de error de Hood se pueden ver en el registro de errores de magnalister sólo después de que se haya completado la carga a Hood..<br /><br />
 <b>Actualización de imágenes en Hood</b><br /><br /> 
 Con el paquete de imágenes, sólo tienes que cambiar la imagen en tu tienda y volver a subir el artículo para que el cambio sea visible en Hood.<br /> 
 Sin ella, una imagen en Hood sólo cambiará si cambias la URL de la imagen (y luego subes el artículo).<br /><br /> 
 <b>Posibles tarifas en la parte de Hood</b><br /><br /> 
 El uso del Paquete de Imágenes es gratuito para los sitios de Hood en Alemania y Austria. Para otros países, consulta las páginas de ayuda de Hood o el soporte de Hood del país correspondiente.<br /><br /> 
 RedGecko GmbH no se hace responsable de las tasas de Hood causadas.';
MLI18n::gi()->{'hood_config_prepare__field__hoodplus__valuehint'} = 'Publicar un artículo con Hood Plus';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.send__help'} = '¿Debería enviarse un correo electrónico desde tu tienda a tus clientes para promocionarla?';
MLI18n::gi()->{'hood_config_prepare__legend__shipping'} = 'Envío';
MLI18n::gi()->{'hood_config_prepare__field__shippingtime.max__label'} = 'Plazo de entrega (máx.)';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.signal__help'} = 'Este campo de texto muestra el valor decimal que aparecerá en el precio del artículo en Hood.< br/><br/> 
 <strong>Ejemplo:</strong> <br /> 
 Valor en campo de texto: 99 <br /> 
 Precio original: 5,58 <br /> 
 Importe final: 5,99 <br /><br 
 />Esta función es útil cuando se marca el precio hacia arriba o hacia abajo***. 
 <br/> Deja este campo en blanco si no quieres introducir una cantidad decimal. 
 <br/>El formato requiere un máximo de 2 números.';
MLI18n::gi()->{'hood_config_prepare__field__restrictedtobusiness__help'} = 'Función activada: sólo los clientes comerciales pueden comprar los artículos.';
MLI18n::gi()->{'hood_config_price__field__chinese.price__label'} = 'Precio inicial';
MLI18n::gi()->{'hood_config_orderimport__field__updateableorderstatus__help'} = 'Estado del pedido que puede ser activado por los pagos de Hood. 
 Si el pedido tiene un estado diferente, éste no puede ser modificado por un pago de Hood.<br /><br />.
 Si no deseas que se modifique tu estado debido al pago de Hood, desmarca la casilla.<br /><br />
 <b>Ten en cuenta:</b>El estado de los pedidos combinados no se modificará hasta que se paguen en su totalidad.';
MLI18n::gi()->{'hood_config_account__field__site__help'} = 'Página del país Hood en la que figura';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__help'} = 'Método de envío que se asigna a todos los pedidos de Hood. Estándar: "Tomar del marketplace".<br><br>
Si seleccionas "Tomar del marketplace", magnalister adoptará el método de envío que el comprador haya elegido en Hood.<br><br>
Esta configuración es importante para la impresión de facturas y albaranes, así como para el procesamiento posterior del pedido en la tienda y en algunos sistemas de gestión de mercancías.';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__3'} = '3 días';
MLI18n::gi()->{'hood_config_producttemplate__legend__product__info'} = 'Plantilla para la descripción del producto en Hood. (Puedes cambiar el editor en "Configuración global" > "Configuración avanzada").';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocaldiscount__label'} = 'Utilizar reglas especiales de precios de envío';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.defect__label'} = 'Cancelar (el artículo es defectuoso)';
MLI18n::gi()->{'hood_config_producttemplate__field__template.content__label'} = 'Plantilla estándar';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__30'} = '30 días';
MLI18n::gi()->{'hood_config_orderimport__field__preimport.start__help'} = 'La fecha a partir de la cual deben importarse los pedidos. Ten en cuenta que no es posible fijar esta fecha demasiado lejos en el pasado, ya que los datos sólo estarán disponibles en Hood durante unas semanas.';
MLI18n::gi()->{'hood_config_price__field__fixed.priceoptions__hint'} = '';
MLI18n::gi()->{'hood_configform_stocksync_values__no'} = '{#i18n:hood_config_general_nosync#}';
MLI18n::gi()->{'hood_config_prepare__legend__pictures'} = 'Configuración para imágenes';
MLI18n::gi()->{'hood_config_price__field__buyitnowprice__hint'} = '';
MLI18n::gi()->{'hood_config_sync__field__syncproperties__valuehint'} = 'Sincronización de EAN y MPN activa';
MLI18n::gi()->{'hood_config_account__field__password__help'} = '';
MLI18n::gi()->{'hood_config_prepare__field__useprefilledinfo__help'} = 'Función activada: Si el catálogo de Hood contiene información detallada sobre el producto, esta se mostrará en la página del producto. Para ello, también se debe proporcionar el EAN.';
MLI18n::gi()->{'hood_config_sync_inventory_import__false'} = 'No';
MLI18n::gi()->{'hood_config_price__field__chinese.priceoptions__help'} = '{#i18n:configform_price_field_priceoptions_help#}';
MLI18n::gi()->{'hood_config_orderimport__field__importactive__label'} = 'Activa la importación';
MLI18n::gi()->{'hood_config_prepare__field__mwst__help'} = 'Importe del IVA que se muestra en Hood, si no está almacenado en el artículo. Los valores distintos de 0 solo se permiten si tienes una cuenta comercial con Hood.';
MLI18n::gi()->{'hood_config_account__field__apikey__label'} = 'Contraseña de la API de Hood';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.copy__help'} = 'Se enviará una copia a la dirección de correo electrónico del remitente.';
MLI18n::gi()->{'hood_config_sync__field__synczerostock__help'} = 'Las ofertas agotadas normalmente se finalizan en Hood. Al volver a listar el artículo y asignarle un nuevo número de oferta de Hood, se pierde la clasificación del producto.
<br /><br />
Para que tus artículos agotados en Hood se finalicen automáticamente y se vuelvan a ofrecer después de la reposición de stock sin perder la clasificación del producto, magnalister admite con esta función la opción de Hood "No disponible" para ofertas "Válidas hasta su cancelación".
<br /><br />
Además de esta función, activa directamente en tu cuenta de Hood la opción "No disponible" en "Mi Hood" > "Configuración del vendedor".
<br /><br />
Ten en cuenta que esta función solo tiene efecto en ofertas "Válidas hasta su cancelación".
<br /><br />
Lee más información sobre este tema en las páginas de ayuda de Hood (término de búsqueda "No disponible").';
MLI18n::gi()->{'hood_config_price__field__chinese.price.factor__hint'} = '';
MLI18n::gi()->{'hood_config_sync__field__inventorysync.price__label'} = 'Precio del artículo';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__21'} = '21 días';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.paid__help'} = 'El estado que recibe el pedido en la tienda cuando se paga en Hood.<br /><br />
                <b>Nota:</b> El estado de los pedidos agrupados solo se cambia cuando todas las partes están pagadas.';
MLI18n::gi()->{'hood_config_orderimport__legend__orderupdate__title'} = 'Sincronización del estado del pedido';
MLI18n::gi()->{'hood_config_prepare__field__usevariations__help'} = 'Función activada: Los productos que están disponibles en varias variantes (como tamaño o color) en la tienda también se enviarán de esta manera a Hood.<br /><br /> La configuración "Cantidad" se aplicará a cada variante individual.<br /><br /><b>Ejemplo:</b> Tienes un artículo 8 veces en azul, 5 veces en verde y 2 veces en negro, bajo la cantidad "Adoptar inventario de la tienda menos el valor del campo derecho", y el valor 2 en el campo. El artículo se enviará 6 veces en azul y 3 veces en verde.<br /><br /><b>Nota:</b> Puede suceder que algo que usas como variante (por ejemplo, tamaño o color) también aparezca en la selección de atributos para la categoría. En ese caso, se usará tu variante y no el valor del atributo.';
MLI18n::gi()->{'hood_configform_orderimport_payment_values__textfield__textoption'} = '1';
MLI18n::gi()->{'hood_config_price__field__fixed.price.factor__hint'} = '';
MLI18n::gi()->{'hood_config_sync__field__inventory.import__label'} = 'Sincronizar artículos externos';
MLI18n::gi()->{'hood_config_sync__field__synczerostock__valuehint'} = 'Sincronizar OutOfStock activo';
MLI18n::gi()->{'hood_config_prepare__field__productfield.brand__label'} = 'Marca';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocalprofile__optional__select__false'} = 'No utilices el perfil de envío';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__29'} = '29 días';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__11'} = '11 días';
MLI18n::gi()->{'hood_config_prepare__field__forcefallback__help'} = 'Si esta opción está activada, siempre se utiliza el valor por defecto para el IVA, independientemente de lo que se haya guardado para el artículo.';
MLI18n::gi()->{'hood_config_account__legend__tabident'} = 'Pestaña';
MLI18n::gi()->{'hood_config_prepare__field__returnsellerprofile__help'} = '                <b>Selecciona el perfil de condiciones para devoluciones</b><br /><br />
                Utilizas la función "Condiciones para tus ofertas" en Hood. Esto significa que las opciones de pago, envío y devolución ya no se pueden seleccionar individualmente, sino que están determinadas por los datos en el perfil correspondiente en Hood.<br /><br />
                Por favor, selecciona aquí el perfil preferido para las condiciones de devolución.';
MLI18n::gi()->{'hood_config_prepare__field__shippingtime.min__help'} = 'Introduce aquí el tiempo de entrega más corto (como un número). Usa 0 si entregas el mismo día. Si no introduces ningún número aquí, se utilizará el valor almacenado en tu cuenta de Hood.';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.open__help'} = '                El estado que debe recibir automáticamente en la tienda un nuevo pedido entrante de Hood.<br />
                Si utilizas un sistema de recordatorios de pago conectado, se recomienda establecer el estado del pedido en "Pagado" (Configuración → Estado del pedido).';
MLI18n::gi()->{'hood_config_account__field__apikey__help'} = '';
MLI18n::gi()->{'hood_config_prepare__field__fixed.duration__label'} = 'Duración de los listados';
MLI18n::gi()->{'hood_config_prepare__field__postalcode__help'} = 'Por favor, introduce la ubicación de tu tienda. Ésta será visible como dirección del vendedor en Hood.';
MLI18n::gi()->{'hood_config_orderimport__field__importonlypaid__alert'} = '            <p>Al activar la función, los pedidos solo se importarán cuando se marquen como "pagados" en Hood. Para métodos de pago como PayPal, Amazon Pay o transferencia inmediata, esto se realiza automáticamente; de lo contrario, el pago debe marcarse como realizado en Hood.</p>
            <p><strong>Ventaja:</strong> El pedido importado se puede enviar de inmediato. Con PayPal y Amazon Pay, el código de transacción estará disponible y podrá ser procesado por tu sistema de gestión de mercancías.</p>';
MLI18n::gi()->{'hood_config_sync__legend__sync__info'} = 'Determina qué atributos de los productos de tu tienda deben actualizarse automáticamente en Hood.<br /><br /><b>Configuración de listados de precio fijo</b>.';
MLI18n::gi()->{'hood_config_prepare__field__shippingtime.max__help'} = 'Introduce aquí el plazo de entrega más largo (en forma de número). Utiliza 0 si realizas la entrega el mismo día. Si no introduce un número aquí, se utilizará el valor almacenado en su cuenta de Hood.';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__9'} = '9 días';
MLI18n::gi()->{'hood_config_orderimport__field__customergroup__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shop__label'} = '{#i18n:form_config_orderimport_shop_lable#}';
MLI18n::gi()->{'hood_config_orderimport__field__preimport.start__label'} = 'por primera vez a partir de';
MLI18n::gi()->{'hood_config_prepare__field__dispatchtimemax__help'} = 'Tiempo máximo necesario antes de que se envíe el artículo. Esto será visible en Hood.';
MLI18n::gi()->{'hood_config_prepare__legend__misc'} = '<b>Configuración Adicional</b>';
MLI18n::gi()->{'hood_config_prepare__field__useprefilledinfo__valuehint'} = 'Mostrar información del producto Hood';
MLI18n::gi()->{'hood_config_account__field__mpusername__label'} = 'Nombre de Usuario en Hood';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.send__label'} = '¿Enviar el correo electrónico?';
MLI18n::gi()->{'hood_config_orderimport__field__importonlypaid__help'} = '<p> Al activar esta función, los pedidos de Hood sólo se importan cuando se marcan en Hood como "Pagados". En caso de PayPal, Amazon Pay o Transferencia Bancaria Instantánea, esto ocurre automáticamente. De lo contrario, el pedido debe estar marcado en Hood como "Pagado".
 </p><p> 
 <strong>Beneficio:</strong> 
 El pedido importado puede enviarse inmedíatamente. Para PayPal y Amazon Pay, el código de transacción está disponible para tu ERP.</p>';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price__help'} = 'Indica un aumento o descuento de precio, ya sea en porcentaje o en un valor fijo. Para un descuento, utiliza un signo de menos (-) delante del número.<br/>
        El precio de "Compra Ahora" debe ser al menos un 40% más alto que el precio de inicio.';
MLI18n::gi()->{'hood_config_prepare__legend__location__title'} = 'Ubicación';
MLI18n::gi()->{'hood_config_account_sync'} = 'Sincronización';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.open__label'} = 'Estado del pedido en la tienda';
MLI18n::gi()->{'hood_config_sync__field__syncrelisting__label'} = 'Auto-Relisting';
MLI18n::gi()->{'hood_configform_pricesync_values__auto'} = '{#i18n:hood_config_general_autosync#}';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.addkind__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__country__label'} = 'País';
MLI18n::gi()->{'hood_config_orderimport__field__update.orderstatus__label'} = 'Cambio de Pedido activo';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__label'} = 'Métodos de pago';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.revoked__help'} = 'Motivo de la cancelación: El cliente ha cancelado el artículo o ya no desea comprarlo. 
 Selecciona el estado apropiado en esta pestaña desplegable (ajustable en tu sistema de tienda). Este estado se mostrará en la cuenta Hood de tu cliente. <br /> 
 El cambio de estado del pedido se activa cuando cambia el estado del producto en tu tienda. magnalister sincroniza automáticamente el cambio de estado con Hood.';
MLI18n::gi()->{'hood_configform_orderimport_shipping_values__textfield__textoption'} = '1';
MLI18n::gi()->{'hood_config_prepare__legend__payment'} = '<b>Configuración de los métodos de pago</b>';
MLI18n::gi()->{'hood_config_orderimport__field__mwstfallback__hint'} = 'El tipo impositivo que se aplicará a los artículos no pertenecientes a la tienda en las importaciones de pedidos, en %.';
MLI18n::gi()->{'hood_config_sync__field__chinese.inventorysync.price__label'} = 'Precio del artículo';
MLI18n::gi()->{'hood_config_prepare__field__lang__label'} = 'Idioma';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationalcontainer__help'} = 'Selecciona ninguna o varias opciones de envío y países que quieras usar por defecto.';
MLI18n::gi()->{'hood_config_price__field__fixed.price__help'} = 'Por favor, introduce un margen o una reducción de precio, ya sea como porcentaje o como importe fijo. Utiliza un signo menos (-) antes del importe para indicar la reducción de precio.';
MLI18n::gi()->{'hood_config_account__field__site__label'} = 'Sitio de Hood';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.carrier.default__label'} = 'Portador';
MLI18n::gi()->{'hood_config_prepare__field__imagesize__help'} = '<p>Introduce la anchura en píxeles de la imagen tal y como debe aparecer en el marketplace. La altura se ajustará automáticamente en función de la relación de aspecto original. </p> 
 <p>Los archivos de origen se procesarán desde la carpeta de imágenes {#setting:sSourceImagePath#}, y se almacenarán en la carpeta {#setting:sImagePath#} con la anchura en píxeles seleccionada para su uso en el marketplace.</p>';
MLI18n::gi()->{'hood_config_price__field__fixed.price.addkind__label'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__importonlypaid__label'} = 'Importar sólo los pedidos marcados como "pagados".';
MLI18n::gi()->{'hood_config_sync__field__inventorysync.price__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.carrier.default__help'} = 'Transportista preseleccionado al confirmar el envío a Hood. <br /><br />
        Para que el código de seguimiento se transmita a Hood, debe haber un transportista registrado.';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.addkind__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.shipped__label'} = 'Confirma el envío con';
MLI18n::gi()->{'hood_config_account__field__username__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationalcontainer__label'} = 'Envíos internacionales';
MLI18n::gi()->{'hood_config_prepare__field__maxquantity__label'} = 'Limitación del número de artículos';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocalcontainer__label'} = 'Envío Nacional';
MLI18n::gi()->{'hood_config_orderimport__field__updateable.orderstatus__help'} = '';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.content__hint'} = 'Lista de marcadores de posición disponibles para Asunto y Contenido: 
 <dl> 
 <dt>#MARKETPLACEORDERID#</dt> 
 <dd>Identificación de pedido de Marketplace</dd> 
 <dt>#FIRSTNAME#</dt> 
 <dd>Nombre del comprador</dt> 
 <dt>#LASTNAME#</dt> 
 <dd>Apellido del comprador</dt> 
 <dt>#EMAIL#</dt> 
 <dd>Dirección de correo electrónico del comprador</dt> 
 <dt>#PASSWORD#</dt> 
 <dd>Contraseña del comprador para acceder a su Tienda. Sólo para los clientes a los que se les asigna automáticamente una contraseña - de lo contrario el marcador de posición será sustituido por &apos;(como se sabe)&apos;***.</dd> 
 <dt>#ORDERSUMMARY#</dt> 
 <dd>Resumen de los artículos comprados. Debe escribirse en una línea separada. <br/><i>¡No puede utilizarse en el Asunto!< /i></dd> 
 <dt>#ORIGINATOR#</dt> 
 <dd>Nombre del remitente</dd> 
 </dl>.';
MLI18n::gi()->{'hood_config_price__field__fixed.price__label'} = 'Precio';
MLI18n::gi()->{'hood_config_account_prepare'} = 'Preparación del artículo';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__15'} = '15 días';
MLI18n::gi()->{'hood_config_orderimport__legend__orderupdate__info'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__10'} = '10 días';
MLI18n::gi()->{'hood_config_sync__field__stocksync.tomarketplace__hint'} = '';
MLI18n::gi()->{'hood_configform_sync_values__auto'} = '{#i18n:hood_config_general_autosync#}';
MLI18n::gi()->{'hood_config_price__field__exchangerate_update__label'} = 'Tipo de cambio';
MLI18n::gi()->{'hood_config_account__field__tabident__label'} = '{#i18n:ML_LABEL_TAB_IDENT#}';
MLI18n::gi()->{'hood_configform_orderstatus_sync_values__auto'} = '{#i18n:hood_config_general_autosync#}';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.defect__hint'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__26'} = '26 días';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled__label'} = 'Deshacer la confirmación del envío cuando';
MLI18n::gi()->{'hood_config_account__field__tabident__help'} = '{#i18n:ML_TEXT_TAB_IDENT#}';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.copy__label'} = 'Copiar al remitente';
MLI18n::gi()->{'hood_config_price__field__exchangerate_update__alert'} = '{#i18n:form_config_orderimport_exchangerate_update_alert#}';
MLI18n::gi()->{'hood_config_prepare__field__shippingtime.min__label'} = 'Tiempo de entrega (min)';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shop__help'} = '{#i18n:form_config_orderimport_shop_help#}';
MLI18n::gi()->{'hood_config_price__field__fixed.duration__help'} = 'Preparación de la duración de las listas de precios fijos. La configuración puede modificarse en la preparación de la partida.';
MLI18n::gi()->{'hood_config_prepare__field__fixed.quantity__help'} = 'Indica aquí cuánta cantidad de inventario de un artículo debería estar disponible en el marketplace.<br/><br/>Para evitar sobreventas, puedes activar el valor "Tomar el inventario del almacén del negocio menos el valor del campo derecho".<br/><br/><strong>Ejemplo:</strong> Establecer el valor en "2". Resultará en → Inventario del negocio: 10 → Inventario de Hood: 8<br/><br/><strong>Nota:</strong> Si deseas terminar ofertas de artículos que se hayan desactivado en la tienda, independientemente de las cantidades de inventario utilizadas, en Hood, sigue estos pasos:<br/><ul><li>Configura "Sincronización de inventario" > "Cambios de inventario en la tienda" a "Sincronización automática mediante CronJob"</li><li>Activa "Configuración global" > "Estado del producto" > "Cuando el estado del producto es inactivo, el inventario se trata como 0"</li></ul>';
MLI18n::gi()->{'hood_config_account_emailtemplate_sender_email'} = 'ejemplo@tiendaonline.com';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.priceoptions__label'} = 'Opciones de precios';
MLI18n::gi()->{'hood_config_orderimport__field__updateable.orderstatus__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__restrictedtobusiness__label'} = 'Restringir a las empresas';
MLI18n::gi()->{'hood_configform_prepare_hitcounter_values__RetroStyle'} = 'Estilo retro';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.defect__help'} = 'Motivo de la cancelación: El artículo es defectuoso. <br /> 
 Selecciona el estado apropiado en esta pestaña desplegable (ajustable en tu sistema de tienda). Este estado se mostrará en la cuenta Hood de tu cliente. <br /> 
 El cambio de estado del pedido se activa cuando cambia el estado del producto de tu tienda. magnalister sincroniza automáticamente el cambio de estado con Hood.';
MLI18n::gi()->{'hood_config_price__field__fixed.duration__label'} = 'duración de los listados';
MLI18n::gi()->{'hood_config_prepare__field__hitcounter__label'} = 'Contador de visitas';
MLI18n::gi()->{'hood_config_price__field__chinese.price.usespecialoffer__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__legend__location__info'} = 'Por favor, introduce la ubicación de tu tienda. Ésta será visible como dirección del vendedor en Hood.';
MLI18n::gi()->{'hood_configform_orderimport_payment_values__matching__title'} = 'Tomar del marketplace';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__hint'} = '';
MLI18n::gi()->{'hood_config_price__legend__fixedprice'} = '<b>Configuración de los listados de precio fijo</b>';
MLI18n::gi()->{'hood_config_account_emailtemplate'} = 'Plantilla de correo electrónico de promoción';
MLI18n::gi()->{'hood_config_prepare__field__picturepack__valuehint'} = 'Activa el paquete de imágenes';
MLI18n::gi()->{'hood_configform_sync_chinese_values__no'} = '{#i18n:hood_config_general_nosync#}';
MLI18n::gi()->{'hood_configform_orderimport_shipping_values__matching__title'} = 'Tomar del marketplace';
MLI18n::gi()->{'hood_config_prepare__field__paypal.address__help'} = 'La dirección de correo electrónico proporcionada a Hood para los pagos de PayPal. Esto es necesario para cargar los artículos de la tienda Hood.';
MLI18n::gi()->{'hood_config_price__field__chinese.price.addkind__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__restrictedtobusiness__valuehint'} = 'Los artículos sólo pueden ser comprados por clientes comerciales';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocalprofile__option'} = '{#NAME#} ({#AMOUNT#} por artículo adicional)';
MLI18n::gi()->{'hood_config_general_nosync'} = 'sin sincronización';
MLI18n::gi()->{'hood_config_sync__legend__sync__title'} = 'Sincronización de inventarios';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__24'} = '24 días';
MLI18n::gi()->{'hood_config_price__field__chinese.price.group__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__topten__help'} = 'Mostrar la categoría de selección rápida en Preparar elementos.';
MLI18n::gi()->{'hood_config_prepare__field__dispatchtimemax__label'} = 'Tiempo hasta el envío';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationaldiscount__label'} = 'Utilizar reglas especiales de precios de envío';
MLI18n::gi()->{'hood_config_sync__legend__stocksync__title'} = 'Sincronización de Hood a la Tienda';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__16'} = '16 días';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.nopayment__label'} = 'Cancelar (el cliente no ha pagado)';
MLI18n::gi()->{'hood_config_orderimport__field__customergroup__help'} = 'El grupo de clientes en el que deben clasificarse los clientes de los nuevos pedidos.';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.sendmail__help'} = 'Si activas esta opción, Hood informarás al comprador del cambio de estado por correo electrónico.';
MLI18n::gi()->{'hood_config_prepare__field__conditiontype__help'} = 'Especifica el estado del artículo (para las categorías de Hood que requieren u ofrecen esta opción). No todas las descripciones son válidas para todas las categorías. Una vez seleccionada la categoría, asegúrate de que el estado del artículo es correcto.';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__23'} = '23 días';
MLI18n::gi()->{'hood_config_price__field__maxquantity__help'} = 'Aquí puedes limitar el número de artículos publicados en Hood.<br /><br />. 
 <strong>Ejemplo:</strong>. 
 Para el "número de artículos" selecciona "tomar del inventario de la tienda" e introduce "20" en este campo. Al subir el número de artículos se tomará del inventario disponible pero no más de 20. La sincronización de inventario (si está activada) adaptará el número de artículos en Hood al inventario de la tienda siempre que el inventario de la tienda sea inferior a 20. Si hay más de 20 artículos en el inventario, el número de artículos en Hood se ajustará a 20.<br /><br />.
 Introduce "0" o deja este campo en blanco si no deseas una limitación.<br /><br /> 
 <strong>Consejo:</strong>. 
 Si la opción "número de elementos" es "global (del campo de la derecha)", la limitación no tiene efecto.';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.paymentmethod__help'} = '<p>Método de pago que se aplicará a todos los pedidos importados de Hood. Estándar: "Asignación automática"</p> 
 <p>Si eliges "Asignación automática", magnalister aceptará el método de pago elegido por el comprador en Hood.</p> 
 <p>Añade métodos de pago adicionales a la lista a través de Shopware > Configuración > Métodos de pago, y luego actívalos aquí.</p> 
 <p>Estos ajustes son necesarios para la factura y la notificación de envío, y para editar los pedidos más tarde en el Shopware o a través del ERP.</p>';
MLI18n::gi()->{'hood_config_price__field__chinese.duration__label'} = 'Duración de la subasta';
MLI18n::gi()->{'hood_config_prepare__field__usevariations__valuehint'} = 'Variaciones de la transferencia';
MLI18n::gi()->{'hood_config_prepare__field__paypal.address__label'} = 'Dirección de correo electrónico de PayPal';
MLI18n::gi()->{'hood_config_producttemplate_content'} = '<p>#TITLE#</p><p>#ARTNR#</p><p>#SHORTDESCRIPTION#</p><p>#PICTURE1#</p><p>#PICTURE2#</p><p>#PICTURE3#</p><p>#DESCRIPTION#</p>';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationalprofile__optional__select__false'} = 'No utilizar el perfil de envío';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.paid__label'} = 'Estado de Pago de Hood en la Tienda';
MLI18n::gi()->{'hood_config_sync__field__syncrelisting__help'} = '<ul>
<li>La oferta termina sin recibir ninguna oferta</li>
<li>Cancela la transacción</li>
<li>Finaliza la oferta prematuramente</li>
<li>El artículo no se ha vendido o</li>
<li>El comprador no ha pagado el artículo.</li>
</ul>

Tenga en cuenta que Hood permite un máximo de 2 re-listados.
<br />
Consulta más información sobre este tema en las páginas de ayuda de Hood (buscar "Re-listado de artículos").';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.originator.adress__label'} = 'Dirección de correo electrónico del remitente';
MLI18n::gi()->{'hood_configform_price_chinese_quantityinfo'} = 'En las subastas ascendentes, la cantidad solo puede ser exactamente 1.';
MLI18n::gi()->{'hood_config_orderimport__field__import__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__chinese.duration__help'} = 'Ajuste anticipado de la duración de la subasta. Este ajuste puede modificarse en la preparación del artículo.';
MLI18n::gi()->{'hood_config_price__field__chinese.price.factor__label'} = '';
MLI18n::gi()->{'hood_config_producttemplate__field__template.content__hint'} = '<dl>
    <dt>#TITLE#</dt>
    <dd>Nombre del producto (Título)</dd>
    <dt>#ARTNR#</dt>
    <dd>Número de artículo en la tienda</dd>
    <dt>#PID#</dt>
    <dd>ID del producto en la tienda</dd>
    <!--<dt>#PRICE#</dt>
    <dd>Precio</dd>
    <dt>#VPE#</dt>
    <dd>Precio por unidad de empaque</dd>-->
    <dt>#SHORTDESCRIPTION#</dt>
    <dd>Descripción corta de la tienda</dd>
    <dt>#DESCRIPTION#</dt>
    <dd>Descripción de la tienda</dd>
    <dt>#PICTURE1#</dt>
    <dd>Primera imagen del producto</dd>
    <dt>#PICTURE2# etc.</dt>
    <dd>Segunda imagen del producto; con #PICTURE3#, #PICTURE4# etc. se pueden transferir más imágenes, tantas como estén disponibles en la tienda.</dd>
</dl>';
MLI18n::gi()->{'hood_configform_orderimport_shipping_values__textfield__title'} = 'Desde el campo de texto';
MLI18n::gi()->{'hood_config_sync__field__inventorysync.price__help'} = '<dl> 
 <dt>Sincronización automática a través de CronJob (recomendado)</dt> 
 <dd>La función &apos;Sincronización automática&apos; sincroniza el precio de Hood con el precio de la Tienda cada 4 horas, a partir de las 0.00 horas (con ***, dependiendo de la configuración).<br>Los valores serán transferidos desde la base de datos, incluyendo los cambios que se produzcan a través de un ERP o similar.<br><br>La comparación manual se puede activar haciendo clic en el botón correspondiente en la cabecera del magnalister (a la izquierda del carrito de la compra).<br><br> 
 Además, puedes activar la comparación de acciones a través de CronJon (tarifa plana*** - máximo cada 4 horas) con el enlace:<br>
 <i>{#setting:sSyncInventoryUrl#}</i><br> 
 Algunas peticiones de CronJob pueden ser bloqueadas, si se realizan a través de clientes que no están en la tarifa plana*** o si la petición se realiza más de una vez cada 4 horas. 
 </dd> 
 <dt>La edición de pedidos / artículos sincronizará Hood y el precio de la Tienda. </dt> 
 <dd>Si se cambia el precio de la Tienda al editar un artículo, el precio actual de la Tienda se transferirá entonces a Hood.<br> 
 ¡Los cambios que sólo se realizan en la base de datos, por ejemplo a través de un ERP, <b>no se</b> registran ni se transmiten!</dd>
 <dt>La edición de artículos cambia el precio de Hood.</dt> 
 <dd>Si cambias el precio del artículo en la tienda, en "Editar artículo", el precio actual del artículo se transfiere a Hood.<br> 
 ¡Los cambios que sólo se realizan en la base de datos, por ejemplo a través de un ERP, <b>no se</b> registran ni se transmiten!</dd>
 </dl> 
 <b>Nota:</b> Se tienen en cuenta los ajustes "Configuración", "Carga de artículos" y "Cantidad de existencias".';
MLI18n::gi()->{'hood_config_prepare__field__mwst__hint'} = 'Tipo impositivo para vendedores comerciales en %';
MLI18n::gi()->{'hood_config_price__field__fixed.priceoptions__label'} = 'Opciones de precios';
MLI18n::gi()->{'hood_config_orderimport__legend__orderstatus'} = 'Sincronización del estado del pedido entre la tienda y Hood';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.nostock__help'} = 'Motivo de la cancelación: El artículo no se puede entregar o está agotado. <br /> 
 Selecciona el estado apropiado en esta pestaña desplegable (ajustable en tu sistema de tienda). Este estado se mostrará en la cuenta Hood de tu cliente. <br /> 
 El cambio de estado del pedido se activa cuando cambia el estado del producto de tu tienda. magnalister sincroniza automáticamente el cambio de estado con Hood.';
MLI18n::gi()->{'hood_config_prepare__field__hoodplus__help'} = '<a href="http://verkaeuferportal.hood.de/hood-plus" target="_blank">Hood Plus</a> se puede activar a través de tu cuenta de Hood si Hood ha activado la función para ti. Actualmente, esta función sólo se ofrece para Hood Alemania.<br /><br />
 La casilla que se encuentra aquí es una configuración por defecto para subir a través de Magnalister. Puede marcarse si Hood Plus está activo en tu cuenta. No afecta a la configuración por defecto para los artículos de Hood (sólo se puede activar a través de tu cuenta de Hood).<br /><br /> 
 Si la casilla no se puede seleccionar aunque hayas activado la función en Hood, guarda tu configuración (magnalister recuerda la última configuración de Hood en este contexto).<br /><br /> 
 <b>Consejo:</b> 
 <ul> 
 <li>Se deben cumplir condiciones adicionales para los listados de Hood Plus:
 Periodo de reenvío de 1 mes, posibilidad de pago por paypal, un <a href="http://verkaeuferportal.hood.de/versand-bei-hood-plus" target="_blank">método de envío que esté acreditado por Hood</a>.
 No recibiremos respuesta</b> de eBay si estas condiciones son correctas. Tienes que encargarte tú mismo de esto.
 <li> Por favor, permite que se modifique el pedido (mediante la sincronización de pedidos) o utiliza la función &quoimportar pedidos marcados como pagados&quot (vie importación de pedidos). La etiqueta Hood plus no se transmite con el primer pedido. Se transmite en cuanto el comprador ha seleccionado el método de pago y envío.</li> 
 <li>A veces parece que los pedidos de Hood plus se transmiten sin métodos de pago autorizados. En estos casos se mostrará un aviso previo en la vista detallada del pedido.</li></ul>';
MLI18n::gi()->{'hood_config_account__field__mppassword__help'} = 'Por favor, introduce tu contraseña de Hood.';
MLI18n::gi()->{'hood_config_orderimport__field__importactive__help'} = '¿Importar pedidos del marketplace? <br/><br/>Si está activada, los pedidos se importan automáticamente cada hora.<br><br>La importación manual se puede activar haciendo clic en el botón correspondiente en la cabecera del magnalister (a la izquierda de la cesta de la compra). <br><br>Además, puedes activar la comparación de existencias a través de CronJon (tarifa plana*** - máximo cada 4 horas) con el enlace:<br> 
 <i>{#setting:sImportOrdersUrl#}</i><br> 
 Algunas solicitudes de CronJob pueden bloquearse si se realizan a través de clientes que no están en tarifa plana*** o si la solicitud se realiza más de una vez cada 4 horas';
MLI18n::gi()->{'hood_config_price__field__chinese.price.signal__label'} = 'Importe decimal';
MLI18n::gi()->{'hood_config_prepare__field__hitcounter__help'} = 'Preselección para el contador de visitas de los listados.';
MLI18n::gi()->{'hood_config_prepare__field__fixed.duration__help'} = 'Preparación de la duración de las listas de precios fijos. La configuración puede modificarse en la preparación de la partida.';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocal__cost'} = 'Gastos de envío';
MLI18n::gi()->{'hood_config_price__legend__price'} = 'Cálculo del precio';
MLI18n::gi()->{'hood_config_orderimport__field__preimport.start__hint'} = 'Hora de inicio';
MLI18n::gi()->{'hood_config_prepare__field__forcefallback__label'} = 'Utilizar siempre por defecto';
MLI18n::gi()->{'hood_config_price__field__chinese.priceoptions__label'} = 'Opciones de precios';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__17'} = '17 días';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.priceoptions__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__mwstfallback__label'} = 'IVA sobre artículos no destinados a tiendas***.';
MLI18n::gi()->{'hood_config_price__field__exchangerate_update__help'} = '{#i18n:form_config_orderimport_exchangerate_update_help#}';
MLI18n::gi()->{'hood_config_sync__field__chinese.stocksync.tomarketplace__label'} = 'Cambio de inventario en la tienda';
MLI18n::gi()->{'hood_config_producttemplate__legend__product__title'} = 'Plantilla de productos';
MLI18n::gi()->{'hood_config_prepare__field__imagesize__hint'} = 'Guardado en: {#setting:sImagePath#}';
MLI18n::gi()->{'hood_config_orderimport__field__customergroup__label'} = 'Grupo de clientes';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.closed__help'} = 'Si un pedido se establece en uno de los estados de pedido seleccionados, los nuevos pedidos de ese cliente no se añadirán a ese estado de pedido.<br />
 Si no quieres un resumen de pedido, selecciona cada estado de pedido.***';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.closed__label'} = 'Finalizar resumen de pedidos';
MLI18n::gi()->{'hood_configform_account_sitenotselected'} = 'Selecciona primero el sitio web de Hood';
MLI18n::gi()->{'hood_config_producttemplate__field__template.name__hint'} = 'Marcador de posición: #TITLE# - Nombre del producto; #BASEPRICE# - Precio base';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__7'} = '7 días';
MLI18n::gi()->{'hood_config_sync__field__chinese.stocksync.frommarketplace__help'} = 'Por ejemplo, si un artículo se compra tres veces en Hood, el inventario de la tienda se reducirá en 3.';
MLI18n::gi()->{'hood_config_prepare__field__hoodplus__label'} = 'hitmeister_prepareform_days';
MLI18n::gi()->{'hood_config_prepare__field__variationdimensionforpictures__help'} = 'Si has guardado imágenes de variación con los datos de tu producto, el Paquete de imágenes las enviará a Hood con la carga del producto.
 Hood sólo permite una dimensión de variación: Por ejemplo, si tomas el color, la imagen principal de la página de producto de Hood cambiará si el comprador selecciona un color diferente.<br /><br /> <br />. 
 Esta configuración es la predeterminada. Puedes cambiarla en el formulario de preparación de cada producto. <br /> 
 Si quieres cambiarlo más tarde, tienes que preparar y subir el producto de nuevo.';
MLI18n::gi()->{'hood_config_sync__field__stocksync.tomarketplace__label'} = 'Cambio de inventario en la tienda';
MLI18n::gi()->{'hood_config_price__field__chinese.price.usespecialoffer__label'} = 'también utilizar precios especiales';
MLI18n::gi()->{'hood_config_price__field__fixed.quantity__help'} = 'Por favor, introduce la cantidad de existencias que deben estar disponibles en el marketplace.<br/> 
 <br/> Puedes cambiar el número de elementos individuales directamente en "Subir". En este caso se recomienda desactivar
 la<br/> sincronización automática en "Sincronización de la acción" > "Sincronización de la acción con el marketplace".<br/> 
 <br/> Para evitar la sobreventa, puedes activar "Transferir existencias de la tienda menos el valor del campo derecho".
 <br/> 
 <strong>Ejemplo:</strong> Al establecer el valor en 2 se obtiene &#8594; Inventario de la tienda: 10 &#8594; Inventario del DummyModule: 8<br/> 
 <br/> 
 <strong> Ten en cuenta:</strong>Si quieres establecer en "0" el inventario de un artículo en el marketplace, que ya está establecido en "Inactivo" en la Tienda, independientemente del inventario real, procede de la siguiente forma:<br/> 
 <li>"Sincronizar inventario">Configura "Editar inventario de la tienda" en "Sincronizar automáticamente con CronJob"</li>.
 <li>" Configuración global" > "Estado del producto" > Activa el ajuste "Si el estado del producto es inactivo, trata las existencias como 0"</li>.
 <ul>.';
MLI18n::gi()->{'hood_config_account__field__password__label'} = '';
MLI18n::gi()->{'hood_config_sync__field__chinese.stocksync.frommarketplace__label'} = 'Cambio de inventario Hood';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.content__label'} = 'Contenido del correo electrónico';
MLI18n::gi()->{'hood_config_orderimport__legend__mwst'} = 'IVA';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price__label'} = 'Precio de compra inmediata';
MLI18n::gi()->{'hood_config_price__field__exchangerate_update__valuehint'} = 'Actualizar automáticamente el tipo de cambio';
MLI18n::gi()->{'hood_config_orderimport__field__mwstfallback__help'} = 'Si un artículo no se introduce a través de magnalister, no se puede calcular el IVA. <br /> 
 El valor porcentual introducido aquí se tomará como tipo de IVA para todos los pedidos importados en Hood.';
MLI18n::gi()->{'hood_config_producttemplate__field__template.name__help'} = '<dl> 
 <dt>Nombre del producto en Hood</dt> 
 <dd>Decide cómo nombrar el producto en Hood. 
 El marcador de posición <b>#TITLE#</b> será sustituido por el nombre del producto de la tienda, 
 <b>#BASEPRICE#</b> por el precio por unidad, siempre que el dato exista en la tienda.</dd> 
 <dt>Ten en cuenta:</dt> 
 <dd>El marcador de posición <b>#BASEPRICE#</b> no es necesario en la mayoría de los casos, ya que enviamos los precios base automáticamente a Hood, si se rellena en la Tienda y se permite para la categoría de Hood.</dd> 
 <dd>Utiliza este marcador de posición si tienes unidades no métricas (que Hood no ofrece), o si quieres mostrar precios base en categorías en las que Hood no los ofrece.</dt> 
 <dd>Si utilizas este marcador de posición, <b>desactiva la sincronización de precios</b>. El título del artículo no se puede cambiar en Hood. Por lo tanto, si el precio cambia, el precio base dentro del título ya no se ajustará.</dd> 
 <dd><b>#BASEPRICE#</b> se reemplaza mientras se sube el producto a Hood.</dd> 
 <dd>Hood no puede manejar <b>diferentes precios base para Variaciones</b>. Por lo tanto, lo añadimos a los títulos de las Variaciones.</dd> 
 <dd>Ejemplo: 
 <br />&nbsp;Grupo de variaciones: cantidad de relleno<ul> 
 <li>Variación: 0,33 l (3 EUR / l)</li> 
 <li>Variación: 0,5 l (2,50 EUR / l)</li> 
 <li>etc.</li></ul></dd> 
 <dd>En este caso, por favor <b>desactiva la sincronización de precios</b> (porque los títulos de la Variación no se pueden cambiar en Hood).</dd> 
 <dl>dd';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shop__hint'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__22'} = '22 días';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.signal__label'} = 'Importe decimal';
MLI18n::gi()->{'hood_config_price__field__fixed.price.usespecialoffer__label'} = 'también utilizar los precios de las ofertas especiales';
MLI18n::gi()->{'hood_config_prepare__field__location__label'} = 'Ciudad';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternational__optional__select__true'} = 'Enviar al extranjero';
MLI18n::gi()->{'hood_config_emailtemplate_content'} = '<style><!--
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
 <p>Hola, #FIRSTNAME# #LASTNAME#:</p>
 <p>Muchas gracias por tu pedido. Has realizado un pedido en nuestra tienda a través de #MARKETPLACE#:
 </p>#ORDERSUMMARY#
 <p>Se aplican además gastos de envío.
 </p><p> </p>
 <p>Saludos,</p>
 <p>El equipo de la tienda online</p>';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.open__hint'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__1'} = '1 día';
MLI18n::gi()->{'hood_config_prepare__field__postalcode__label'} = 'Código postal';
MLI18n::gi()->{'hood_config_account_producttemplate'} = 'Plantilla de producto';
MLI18n::gi()->{'hood_config_prepare__field__picturepack__label'} = 'Paquete de imágenes';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__20'} = '20 días';
MLI18n::gi()->{'hood_config_prepare__legend__chineseprice'} = '<b>Configuración de la subasta</b>';
MLI18n::gi()->{'hood_config_price__field__fixed.priceoptions__help'} = '{#i18n:configform_price_field_priceoptions_help#}';
MLI18n::gi()->{'hood_config_account__field__token__help'} = 'Para solicitar un nuevo token de Hood, haz clic en el botón. < br> 
 Si esto no abre Hood en una ventana nueva, desactiva tu bloqueador de ventanas emergentes. < br>< br> 
 El token es necesario para acceder a Hood a través de la interfaz magnalister. < br> 
 Sigue los pasos de la ventana de Hood para solicitar un token y conectar tu tienda online a Hood a través de magnalister.';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__hint'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__0'} = 'el mismo día';
MLI18n::gi()->{'hood_config_price__field__fixed.price.group__label'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__13'} = '13 días';
MLI18n::gi()->{'hood_configform_prepare_hitcounter_values__HiddenStyle'} = 'oculto';
MLI18n::gi()->{'hood_config_price__field__fixed.price.signal__label'} = 'Importe decimal';
MLI18n::gi()->{'hood_config_prepare__field__lang__help'} = 'Idioma de los nombres y descripciones de los artículos. Tu tienda permite nombres y descripciones en más de un idioma; para subirlos a Hood, hay que seleccionar un idioma. 
 Los informes de error de Hood también se generan en el idioma seleccionado.';
MLI18n::gi()->{'hood_config_sync__field__chinese.inventorysync.price__help'} = '<dl>
    <dt>Sincronización automática por CronJob (recomendado)</dt>
    <dd>
        Con la función "Sincronización automática", el precio almacenado en la tienda online se transmite al marketplace de {#setting:currentMarketplaceName#} (si está configurado en magnalister, con aumentos o descuentos de precio). La sincronización se realiza cada 4 horas (punto de inicio: 0:00 h de la noche).<br />
        Durante este proceso, se verifican y adoptan los valores de la base de datos, incluso si los cambios solo se realizaron en la base de datos mediante, por ejemplo, un sistema de gestión de mercancías.<br />
        <br />
        Puedes iniciar una sincronización manual haciendo clic en el botón correspondiente "Sincronización de precios e inventario" en la parte superior derecha del plugin de magnalister.<br />
        <br />
        Además, puedes iniciar la sincronización de precios mediante tu propio CronJob accediendo al siguiente enlace de tu tienda:<br />
        <i>{#setting:sSyncInventoryUrl#}</i><br />
        Las llamadas de CronJob propias por parte de los clientes que no están en el plan Enterprise, o que se ejecutan con una frecuencia mayor a cada quince minutos, serán bloqueadas.<br />
    </dd>
</dl>
<br />
<strong>Notas:</strong>
<ul>
    <li>Durante la sincronización se tienen en cuenta las configuraciones bajo "Configuración" → "Cálculo de precios".</li>
    <li>Una vez que se ha realizado una oferta en la subasta, el precio no se puede cambiar.</li>
</ul>';
MLI18n::gi()->{'hood_config_prepare__field__returnsellerprofile__label'} = 'Condiciones generales: Amortización';
MLI18n::gi()->{'hood_config_account__field__currency__help'} = 'La moneda en la que quieres que se muestren tus artículos de Hood. Elige una moneda que se corresponda con la del sitio Hood.';
MLI18n::gi()->{'hood_config_prepare__field__chinese.duration__label'} = 'Duración de la subasta';
MLI18n::gi()->{'hood_configform_prepare_hitcounter_values__BasicStyle'} = 'sencillo';
MLI18n::gi()->{'hood_config_account_emailtemplate_sender'} = 'Tienda de ejemplo';
MLI18n::gi()->{'hood_config_prepare__legend__prepare'} = 'Preparación de artículos';
MLI18n::gi()->{'hood_config_price__field__maxquantity__label'} = 'Limitación del número de artículos';
MLI18n::gi()->{'hood_config_sync__field__synczerostock__label'} = 'Sincronizar los stocks cero';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.factor__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationalprofile__notavailible'} = 'Sólo cuando se activa `<i>Envío Internacional</i>`.';
MLI18n::gi()->{'hood_config_prepare__field__paymentinstructions__help'} = 'Por favor, introduce aquí el texto que debe aparecer en la página del artículo bajo &apos;Instrucciones de pago del vendedor&apos;***. Máximo 500 caracteres (sólo texto, no HTML).';
MLI18n::gi()->{'hood_config_account_emailtemplate_subject'} = 'Tu pedido en #SHOPURL#';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocalcontainer__help'} = 'Selecciona al menos uno o varios métodos de envío que se utilizarán por defecto.<br /><br />Puedes introducir un número para los gastos de envío (sin especificar la moneda) o "=PESO" para establecer los gastos de envío iguales al peso del artículo.';
MLI18n::gi()->{'hood_config_prepare__field__fixed.quantity__label'} = 'Número de artículos';
MLI18n::gi()->{'hood_config_prepare__legend__upload'} = 'Cargar preajustes de elementos';
MLI18n::gi()->{'hood_config_price__field__fixed.price.factor__label'} = '';
MLI18n::gi()->{'hood_config_prepare__field__privatelisting__valuehint'} = 'Hacer que la lista de compradores y licitadores sea privada';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.nostock__label'} = 'Cancelar (no se puede entregar)';
MLI18n::gi()->{'hood_config_price__field__chinese.price.group__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__field__usevariations__label'} = 'Variaciones';
MLI18n::gi()->{'hood_configform_sync_chinese_values__auto'} = '{#i18n:hood_config_general_autosync#}';
MLI18n::gi()->{'hood_config_prepare__field__conditiontype__label'} = 'Estado del artículo';
MLI18n::gi()->{'hood_configform_prepare_hitcounter_values__NoHitCounter'} = 'ninguno';
MLI18n::gi()->{'hood_config_prepare__field__topten__label'} = 'Selección rápida de categorías';
MLI18n::gi()->{'hood_config_price__field__fixed.quantity__label'} = 'número de artículos';
MLI18n::gi()->{'hood_config_account_title'} = 'Datos de acceso';
MLI18n::gi()->{'hood_config_sync__field__stocksync.frommarketplace__label'} = 'Cambio de existencias Hood';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__25'} = '25 días';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternational__cost'} = 'Gastos de envío';
MLI18n::gi()->{'hood_config_account__field__mpusername__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__field__paymentmethods__label'} = 'Métodos de pago';
MLI18n::gi()->{'hood_config_sync__legend__syncchinese'} = '<b>Configuración de la subasta</b>';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__19'} = '19 días';
MLI18n::gi()->{'hood_config_prepare__field__imagesize__label'} = 'Tamaño de la imagen';
MLI18n::gi()->{'hood_config_sync__field__syncrelisting__valuehint'} = 'Auto-Relisting activo';
MLI18n::gi()->{'hood_config_prepare__field__paymentmethods__help'} = 'Configuración por defecto de los métodos de pago (selecciona varias opciones manteniendo pulsado cmd/ctrl mientras haces clic). Las opciones son las predeterminadas por Hood.';
MLI18n::gi()->{'hood_config_price__field__chinese.priceoptions__hint'} = '';
MLI18n::gi()->{'hood_config_price__legend__chineseprice'} = '<b>Configuración de la subasta</b>';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationalprofile__option'} = '{#NAME#} ({#AMOUNT#} por artículo adicional)';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternationalprofile__optional__select__true'} = 'Utilizar el perfil de envío';
MLI18n::gi()->{'hood_config_prepare__field__privatelisting__help'} = 'Activa esta opción para marcar las ofertas como "privadas". Esto hará que tu lista de compradores/ofertantes no sea visible públicamente.';
MLI18n::gi()->{'hood_config_price__field__fixed.price.group__hint'} = '';
MLI18n::gi()->{'hood_config_sync__field__inventory.import__help'} = '¿Deseas que los artículos que no se hayan listado a través de magnalister se muestren y sincronicen también? <br/><br/>Si esta función está activada, todos los artículos que se ofrezcan en Hood para esta cuenta de Hood se cargarán en la base de datos de magnalister cada noche y se mostrarán en el plugin bajo \'Listings\'.<br/><br/>La sincronización de precios e inventario también funcionará para estos artículos, siempre que la SKU (unidad de inventario) en Hood coincida con un número de artículo en la tienda.<br/><br/>Además, debes haber configurado "Configuración global" > "Sincronización de rangos de números" > "Número de artículo (Tienda) = SKU (Marketplace)".<br/>Ten en cuenta que si cambias los rangos de números, estos deben renovarse completamente en los marketplaces para asegurar una correcta sincronización.<br/>Obtén asesoramiento si es necesario.<br/><br/>Esta funcionalidad actualmente no está disponible para artículos externos con variantes.<br/><br/><b>Atención:</b> Los artículos que se listaron a través de magnalister pero que luego se re-listaron en Hood serán reconocidos por magnalister como artículos externos debido a la asignación de un nuevo número de oferta de Hood. ¡Por lo tanto, no desactives esta función si deseas que los artículos re-listados también se sincronicen automáticamente!';
MLI18n::gi()->{'hood_config_sync__field__syncproperties__label'} = 'Sincronización de EAN, MPN y fabricante';
MLI18n::gi()->{'hood_config_account__legend__account'} = 'Datos de acceso';
MLI18n::gi()->{'hood_config_prepare__field__shippinginternational__optional__select__false'} = 'No enviar al extranjero';
MLI18n::gi()->{'hood_config_price__field__fixed.price.signal__help'} = 'Este campo de texto muestra el valor decimal que aparecerá en el precio del artículo en Hood.< br/><br/> 
 <strong>Ejemplo:</strong> <br /> 
 Valor en el campo de texto: 99 <br /> 
 Precio original: 5,58 <br /> 
 Importe final: 5,99 <br /><br 
 />Esta función es útil cuando se marca el precio hacia arriba o hacia abajo. 
 <br/> Deja este campo en blanco si no quieres introducir una cantidad decimal. 
 <br/>El formato requiere un máximo de 2 números.';
MLI18n::gi()->{'hood_config_orderimport__legend__importactive'} = 'Importación de pedidos';
MLI18n::gi()->{'hood_config_orderimport__field__import__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__field__maxquantity__help'} = 'Aquí puedes limitar el número de artículos publicados en Hood.<br /><br />. 
 <strong>Ejemplo:</strong>. 
 Para el "número de artículos" selecciona "tomar del inventario de la tienda" e introduce "20" en este campo. Al subir el número de artículos se tomará del inventario disponible pero no más de 20. La sincronización de inventario (si está activada) adaptará el número de artículos en Hood al inventario de la tienda siempre que el inventario de la tienda sea inferior a 20. Si hay más de 20 artículos en el inventario, el número de artículos en Hood se ajustará a 20.<br /><br />.
 Introduce "0" o deja este campo en blanco si no deseas una limitación.<br /><br /> 
 <strong>Consejo:</strong>. 
 Si la opción "número de elementos" es "global (del campo de la derecha)", la limitación no tiene efecto.';
MLI18n::gi()->{'hood_config_general_autosync'} = 'Sincronización automática mediante CronJob (recomendado)';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.revoked__hint'} = '';
MLI18n::gi()->{'hood_configform_orderimport_payment_values__textfield__title'} = 'Desde el campo de texto';
MLI18n::gi()->{'hood_config_price__field__fixed.price__hint'} = '';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__28'} = '28 días';
MLI18n::gi()->{'hood_config_prepare__field__paymentinstructions__label'} = 'Más información sobre el proceso de compra';
MLI18n::gi()->{'hood_config_prepare__legend__fixedprice'} = '<b>Opción de listas de precios fijos</b>';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__12'} = '12 días';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__14'} = '14 días';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.sendmail__label'} = 'Envío por correo electrónico';
MLI18n::gi()->{'hood_config_sync__field__stocksync.frommarketplace__hint'} = '';
MLI18n::gi()->{'hood_configform_orderstatus_sync_values__no'} = '{#i18n:hood_config_general_nosync#}';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__27'} = '27 días';
MLI18n::gi()->{'hood_config_prepare__field__returnsellerprofile__help_subfields'} = '                <b>Nota</b>:<br />
                Este campo no es editable ya que está utilizando el framework Hood. Por favor, utiliza el campo de selección
                <b>Condiciones generales: Canje</b> para definir el perfil de las condiciones de canje.
            ';
MLI18n::gi()->{'hood_config_price__field__buyitnowprice__label'} = 'Precio de venta inmediata activado';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.nostock__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.cancelled__help'} = 'Por favor, establece el estado de la tienda para eliminar el estado enviado en Hood.<br/><br/>.
 
 Nota: Las cancelaciones parciales no son posibles con la API de Hood. Con esta función, se cancela todo el pedido y se reembolsa al comprador.';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.factor__label'} = '';
MLI18n::gi()->{'hood_config_sync_inventory_import__true'} = 'Sí';
MLI18n::gi()->{'hood_config_emailtemplate__field__mail.subject__label'} = 'Asunto';
MLI18n::gi()->{'hood_configform_stocksync_values__rel'} = 'El pedido reduce el stock de la tienda (recomendado)';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__6'} = '6 días';
MLI18n::gi()->{'hood_config_account__field__username__help'} = '';
MLI18n::gi()->{'hood_config_price__field__chinese.buyitnow.price.signal__hint'} = 'Importe decimal';
MLI18n::gi()->{'hood_config_orderimport__field__updateableorderstatus__label'} = 'Actualizar el estado del pedido cuando';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__8'} = '8 días';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__2'} = '2 días';
MLI18n::gi()->{'hood_config_sync__field__stocksync.frommarketplace__help'} = 'Si, por ejemplo, un artículo se compra 3 veces en Hood, el inventario de la tienda se reducirá en 3.<br /><br /> 
 <strong>Importante:</strong> ¡Esta función sólo funciona si has activado la importación de pedidos!';
MLI18n::gi()->{'hood_config_prepare__field__shippinglocalprofile__optional__select__true'} = 'Utilizar el perfil de envío';
MLI18n::gi()->{'hood_config_price__field__fixed.price.usespecialoffer__hint'} = '';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.nopayment__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__field__variationdimensionforpictures__label'} = 'Nivel de variante del paquete de imágenes';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled__help'} = 'Establece el estado del pedido en su tienda, que deshará el estado 
 &apos;enviado&apos; en Hood. <br/><br/> 
 Nota: Esto sólo significa que el estado del pedido en Hood ya no es &apos;enviado&apos;. No significa que el pedido se haya cancelado.';
MLI18n::gi()->{'hood_config_price__field__chinese.price.addkind__hint'} = '';
MLI18n::gi()->{'hood_config_account__field__mppassword__label'} = 'Contraseña de Hood';
MLI18n::gi()->{'hood_config_sync__field__chinese.stocksync.tomarketplace__help'} = '<dl>
    <dt>Sincronización automática por CronJob (recomendado)</dt>
    <dd>
        La función "Sincronización automática" actualiza cada 4 horas (comenzando a las 0:00 horas) el inventario actual de {#setting:currentMarketplaceName#} con el inventario de la tienda (según la configuración, posiblemente con deducciones).<br />
        <br />
        Durante este proceso, se verifican y adoptan los valores de la base de datos, incluso si los cambios solo se realizaron en la base de datos mediante, por ejemplo, un sistema de gestión de mercancías.<br />
        <br />
        Puedes iniciar una sincronización manual haciendo clic en el botón correspondiente "Sincronización de precios e inventario" en la parte superior derecha del plugin de magnalister.<br />
        Además, puedes iniciar la sincronización de inventario (a partir del plan Enterprise - máximo cada quince minutos) mediante tu propio CronJob accediendo al siguiente enlace de tu tienda:<br />
        <i>{#setting:sSyncInventoryUrl#}</i><br />
        Las llamadas de CronJob propias por parte de los clientes que no están en el plan Enterprise, o que se ejecutan con una frecuencia mayor a cada quince minutos, serán bloqueadas.<br />
    </dd>
</dl>
<br />
<strong>Nota:</strong>
<ul>
    <li>Una vez que se ha realizado una oferta en la subasta, no se puede eliminar.</li>
</ul>';
MLI18n::gi()->{'hood_config_emailtemplate__legend__mail'} = 'Correo electrónico al comprador';
MLI18n::gi()->{'hood_configform_prepare_dispatchtimemax_values__4'} = '4 días';
MLI18n::gi()->{'hood_config_orderimport__field__orderimport.shippingmethod__label'} = 'Servicio de envío de los pedidos';
MLI18n::gi()->{'hood_config_prepare__field__useprefilledinfo__label'} = 'Información sobre el producto';
MLI18n::gi()->{'hood_config_prepare__field__chinese.quantity__label'} = 'Número de artículos';
MLI18n::gi()->{'hood_config_sync__field__stocksync.tomarketplace__help'} = '<dl> 
 <dt>Sincronización automática a través de CronJob (recomendado)</dt> 
 <dd>El stock actual de Hood ser sincronizará con el stock de la tienda cada 4 horas, a partir de las 0:00 horas (con ***, dependiendo de la configuración).<br>Los valores serán transferidos desde la base de datos, incluyendo los cambios que se produzcan a través de un ERP o similar.<br><br>La comparación manual se puede activar haciendo clic en el botón correspondiente en la cabecera del magnalister (a la izquierda del carrito de la compra).<br><br> 
 Además, puedes activar la comparación de acciones a través de CronJon (tarifa plana*** - máximo cada 4 horas) con el enlace:<br>
 <i>{#setting:sSyncInventoryUrl#}</i><br> 
 Algunas peticiones de CronJob pueden ser bloqueadas, si se realizan a través de clientes que no están en la tarifa plana*** o si la petición se realiza más de una vez cada 4 horas. 
 </dd> 
 <dt>La edición de pedidos / artículos sincronizará el stock de Hood y de la tienda. </dt> 
 <dd>Si el inventario de la tienda se modifica debido a un pedido o a la edición de un artículo, el inventario actual de la tienda se transferirá entonces a Hood.<br> 
 ¡Los cambios que sólo se realizan en la base de datos, por ejemplo a través de un ERP, <b>no se</b> registran ni se transmiten!</dd>
 <dt>La edición de pedidos / artículos cambia el inventario de Hood.</dt> 
 <dd>Por ejemplo, si un artículo de la tienda se compra dos veces, el inventario de Hood se reducirá en 2.<br /> Si cambias el importe del artículo en la tienda en <strong>Editar artículo</strong>, la diferencia se suma o se resta del importe anterior.<br> 
 ¡Los cambios que sólo se realizan en la base de datos, por ejemplo a través de un ERP, <b>no se</b> registran ni se transmiten!</dd>
 </dl> 
 <b>Nota:</b> Se tienen en cuenta los ajustes "Configuración", "Carga de artículos" y "Cantidad de existencias".';
MLI18n::gi()->{'hood_config_price__field__fixed.price.addkind__hint'} = '';
MLI18n::gi()->{'hood_config_prepare__field__privatelisting__label'} = 'Listados privados';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.cancelled__label'} = 'Deshacer la confirmación del envío con';
MLI18n::gi()->{'hood_config_orderimport__field__orderstatus.canceled.nopayment__help'} = 'Motivo de la cancelación: El cliente no paga el artículo. <br /> 
 Selecciona el estado apropiado en esta pestaña desplegable (ajustable en tu sistema de tienda). Este estado se mostrará en la cuenta Hood de tu cliente. <br /> 
 El cambio de estado del pedido se activa cuando cambia el estado del producto en tu tienda. magnalister sincroniza automáticamente el cambio de estado con Hood.';
MLI18n::gi()->{'hood_config_producttemplate__field__template.name__label'} = 'Plantilla de nombres de productos';
MLI18n::gi()->{'hood_config_orderimport__field__importactive__hint'} = '';
