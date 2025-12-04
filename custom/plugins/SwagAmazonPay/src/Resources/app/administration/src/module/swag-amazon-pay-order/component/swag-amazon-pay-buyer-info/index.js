import template from './swag-amazon-pay-buyer-info.html.twig';
import './swag-amazon-pay-buyer-info.scss';

const { Component } = Shopware;

Component.register('swag-amazon-pay-buyer-info', {
    template,

    props: {
        paymentDetails: {
            type: Object,
            required: true,
        },

        order: {
            type: Object,
            required: true,
        },
    },

    computed: {
        customerSalutation() {
            const salutationKey = this.order.orderCustomer.salutation.salutationKey;

            return salutationKey === 'not_specified' ? '' : this.order.orderCustomer.salutation.displayName;
        },
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    },
});
