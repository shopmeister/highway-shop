import './extension/sw-order-detail';
import './extension/sw-order-list';
import './page/magnalister-tab';


import deDE from './snippet/de_DE.json';
import enGB from './snippet/en_GB.json';

const { Module } = Shopware;

Module.register('magnalister-order', {
    type: 'plugin',
    name: 'Bundle',
    title: 'magnalister-order.general.title',
    description: 'magnalister-order.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routeMiddleware(next, currentRoute) {        
        if (currentRoute.name === 'sw.order.detail') {
            currentRoute.children.push({
                component: 'magnalister-tab',
                name: 'magnalister-order.payment.detail',
                isChildren: true,
                meta: {
                    parentPath: 'sw.order.index'
                },
                path: '/sw/order/detail/:id/magnalister'
            });
        }

        next(currentRoute);
    }
});
