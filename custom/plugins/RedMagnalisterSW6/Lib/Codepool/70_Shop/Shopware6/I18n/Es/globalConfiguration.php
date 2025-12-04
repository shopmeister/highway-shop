<?php
/*
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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
MLI18n::gi()->{'general_shopware6_master_sku_migration_options__label'} = 'Utilizar SKU maestro de Shopware 5';
MLI18n::gi()->{'general_shopware6_flow_skipped__valuehint'} = 'Omitir Flow Builder durante la importación de pedidos';
MLI18n::gi()->{'general_shopware6_flow_skipped__label'} = 'Compatibilización con Shopware 6 Flow Builder';
MLI18n::gi()->{'general_shopware6_flow_skipped__hint'} = 'Mas información en el icono de información';
MLI18n::gi()->{'general_shopware6_flow_skipped__help'} = 'Actualmente admitimos los siguientes eventos:<br>
 * «El pedido llega al estado ...» (state_enter.order.state....)<br>
 * «Pedido recibido» (checkout.order.placed)';
MLI18n::gi()->{'general_shopware6_master_sku_migration_options__help'} = '<p>Este ajuste solo es relevante para los comerciantes que ya han enviado artículos con variantes a las plataformas a través de magnalister con «Shopware 5»:
 <ul>
 <li>Si <b>no activas</b> el ajuste, los productos definidos como artículos «maestros» en la gestión de productos de Shopware 6 se crean con todas las variantes asociadas como nuevos productos en las plataformas.
 </li>
 <li><b></b> Si activas esta opción, el código SKU ("Stock Keeping Unit") del artículo magnalister "maestro" se configurará automáticamente para actualizar el artículo existente en el marketplace cuando se vuelva a cargar el producto.
 </li>
 </ul></p>
 <p>
 <b>Razones:</b> Shopware 6 distingue entre variantes y artículos "maestros" a la hora de asignar un código SKU. Si utilizas el asistente de migración de Shopware 6 para migrar tus productos de Shopware 5 a 6, se añadirá una M al SKU del artículo "maestro" (ejemplo SKU: "1234M"). Para las variantes no se añade esta letra.
 </p><p>
 En Shopware 5, no existe la distinción entre «maestro» y variante. Sin embargo, para algunos marketplaces la identificación de un artículo «maestro» es relevante. Por lo tanto, al cargar un producto desde Shopware 5, magnalister identifica de forma independiente el SKU de la variante principal del artículo añadiendo: «_Master» (ejemplo: «1234_Master»).
 </p><p>
 Si el ajuste «Shopware 5 Master-SKU» esta activado, magnalister convierte automáticamente el sufijo «M» a «_Master» durante la carga del producto.
 </p>
 <p><b>Notas adicionales:</b>
 <ul>
 <li>La comparación de precios y stock entre la tienda web y los marketplaces de artículos que se transmitieron a través de magnalister desde Shopware 5,
 funciona en Shopware 6 incluso si este ajuste no está activado.
 </li>
 <li>En la descripción general de la preparación del producto, la carga del producto y la pestaña de inventario, puedes identificar los artículos «maestros» al añadirlos después del SKU.
 </li>
 </ul></p>';
