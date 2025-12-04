import template from './swag-amazon-pay-amount-info.html.twig';
import './swag-amazon-pay-amount-info.scss';

const { Component } = Shopware;
const { currency } = Shopware.Utils.format;

Component.register('swag-amazon-pay-amount-info', {
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
        refundStatus: {
            type: Object,
            required: false,
            default: null,
        },
        isInvalidChargePermission: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    computed: {
        isLoading() {
            return !this.paymentDetails.chargePermission.reference;
        },

        totalAmount() {
            return currency(
                this.paymentDetails.chargePermission.amount || this.totalOrderPrice,
                this.paymentDetails.chargePermission.currency
            );
        },

        totalOrderPrice() {
            return currency(
                this.order.price.totalPrice,
                this.order.currency.isoCode
            );
        },

        chargedAmount() {
            return currency(
                this.paymentDetails.chargePermission.capturedAmount || 0.0,
                this.paymentDetails.chargePermission.currency
            );
        },

        refundedAmount() {
            return currency(
                this.paymentDetails.totalRefundedAmount || 0.0,
                this.paymentDetails.chargePermission.currency
            );
        },

        refundPendingAmount() {
            return currency(
                this.paymentDetails.totalRefundPendingAmount || 0.0,
                this.paymentDetails.chargePermission.currency
            );
        },

        hasPendingRefunds() {
            return this.paymentDetails.hasPendingRefunds;
        },
        dateFilter() {
            return Shopware.Filter.getByName('date');
        },
    },
    //
    // watch: {
    //     refundPending() {
    //         this.$emit('refund-pending-change', this.refundPending);
    //     },
    // },
});
