import './sm-zalando-connector';

Shopware.Module.register('sm-zalando-connector', {
    type: 'plugin',
    name: 'ShopmasterZalandoConnectorSix',
    title: 'Zalando Connector',
    description: 'sm-zalando-connector.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'default-action-settings',

    routes: {
        extension: {
            component: 'sm-zalando-connector-extension-index',
            path: 'extension',
            children: {
                settings: {
                    component: 'sm-zalando-connector-extension-settings',
                    path: 'settings',
                    children: {
                        orderImport: {
                            component: 'sm-zalando-connector-extension-settings-orderImport',
                            path: 'orderImport',
                        },
                        stockSync: {
                            component: 'sm-zalando-connector-extension-settings-stockSync',
                            path: 'stockSync',
                        },
                        priceSync: {
                            component: 'sm-zalando-connector-extension-settings-priceSync',
                            path: 'priceSync',
                        },
                        priceReportSync: {
                            component: 'sm-zalando-connector-extension-settings-priceReportSync',
                            path: 'priceReportSync',
                        },
                        listing: {
                            component: 'sm-zalando-connector-extension-settings-listing',
                            path: 'listing',
                        }
                    }
                }
            },
        },
        order: {
            component: 'sm-zalando-order',
            path: 'order',
            children: {
                index: {
                    component: 'sm-zalando-order-list',
                    path: 'index',
                },
                detail: {
                    component: 'sm-zalando-order-detail',
                    path: 'detail/:zalandoId',
                    props: {
                        default(route) {
                            return {
                                zalandoId: route.params.zalandoId,
                            };
                        },
                    }
                },
            }
        }
    },
    settingsItem: [{
        to: 'sm.zalando.connector.extension.settings',
        group: 'plugins',
        icon: 'regular-marketplace-stall',}],
    navigation: [{
        label: 'Zalando Orders',
        color: '#ff3d58',
        path: 'sm.zalando.connector.order.index',
        parent: 'sw-order',
        icon: 'regular-cog',
        position: 10000
    }]
});
