import template from './sm-zalando-order-detail.html.twig';


Shopware.Component.register('sm-zalando-order-detail', {
    template,
    inject: [
        'ShopmasterZalandoConnectorApiOrderService',
    ],
});