import template from './swag-amazon-pay-checkout-info.html.twig';
import './swag-amazon-pay-checkout-info.scss';

const { Component } = Shopware;

Component.register('swag-amazon-pay-checkout-info', {
    template,

    props: {
        paymentDetails: {
            type: Object,
            required: true,
        },
        transaction: {
            type: Object,
            required: true,
        },
        order: {
            type: Object,
            required: true,
        },
    },

    computed: {
        billingAddress() {
            const billingAddressId = this.order.billingAddressId;
            return this.order.addresses.get(billingAddressId);
        },

        shippingAddress() {
            return this.order.deliveries.last().shippingOrderAddress;
        },

        paymentStatusText() {
            return this.paymentDetails.chargePermission.status;
        },
    },
});
