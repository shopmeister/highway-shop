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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLSetting::gi()->add('aPredefinedQuerys',array(
    '<dl><dt>General:</dt><dd>Show all tables</dd></dl>' => 'Show tables;',
    '<dl><dt>General:</dt><dd>Show columns of a table </dd></dl>' => 'Show columns FROM ',
    '<dl><dt>General:</dt><dd>processlist()</dd></dl>'=>"SELECT concat ('<a href=\"".MLHttp::gi()->getUrl(array('mp'=>'tools','tools'=>'sql', 'SQL'=>"KILL QUERY"))." ',ID,'"."; \">Kill</a>') as `Kill`, pl.* FROM INFORMATION_SCHEMA.PROCESSLIST pl",
    '<dl><dt>General:</dt><dd>Show all magnalister products</dd></dl>'=>'SELECT * FROM magnalister_products;',
    '<dl><dt>General:</dt><dd>Show all magnalister orders</dd></dl>'=>'SELECT * FROM magnalister_orders;',
    '<dl><dt>General:</dt><dd>Count orders per marketplace</dd></dl>'=>'SELECT platform, mpid, COUNT(*) AS order_count FROM magnalister_orders GROUP BY platform,mpid;',
    '<dl><dt>Amazon:</dt><dd>Show amazon prepare</dd></dl>'=>'SELECT * FROM magnalister_amazon_prepare;',
));
