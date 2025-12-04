import './extension/sw-order';
import './page/swag-amazon-pay-order-tab';
import './component/swag-amazon-pay-buyer-info';
import './component/swag-amazon-pay-amount-info';
import './component/swag-amazon-pay-action-toolbar';
import './component/swag-amazon-pay-checkout-info';
import './component/swag-amazon-pay-charge-modal';
import './component/swag-amazon-pay-create-charge-modal';
import './component/swag-amazon-pay-refund-modal';
import './component/swag-amazon-pay-cancel-modal';
import './component/swag-amazon-pay-payment-history';

const { Module } = Shopware;

Module.register('swag-amazon-pay-order', {
    type: 'plugin',
    name: 'SwagAmazonPayOrder',
    title: 'swag-amazon-pay-order.general.title',
    description: 'swag-amazon-pay-order.general.description',
    version: '1.0.0',
    targetVersion: '1.0.0',

    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.order.detail') {
            currentRoute.children.push({
                component: 'swag-amazon-pay-order-tab',
                name: 'swag-amazon-pay-order.payment.detail',
                isChildren: true,
                path: '/sw/order/swag-amazon-pay-order/detail/:id/:transactionId',
                meta: {
                    parentPath: 'sw.order.index',
                    privilege: 'order.viewer',
                },
            });
        }

        next(currentRoute);
    },
});
