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
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

import './acl';
import './page/magnalister-admin-page';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Module.register('magnalister-admin', {
    type: 'plugin',
    name: 'Bundle',
    title: 'magnalister-admin.general.mainMenuItemGeneral',
    description: '',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        page: {
            component: 'magnalister-admin-page',
            path: 'page'
        }
    },

    navigation: [{
        label: 'magnalister-admin.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'magnalister.admin.page',
        icon: 'default-shopping-paper-bag-product',
        privilege: 'magnalister.admin.page:read',
        parent: 'sw-marketing'
    }]
});
