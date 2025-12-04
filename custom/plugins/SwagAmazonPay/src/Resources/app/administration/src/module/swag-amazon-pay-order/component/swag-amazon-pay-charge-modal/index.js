import template from './swag-amazon-pay-charge-modal.html.twig';
import './swag-amazon-pay-charge-modal.scss';

const {Component, Mixin} = Shopware;
const {currency} = Shopware.Utils.format;

Component.register('swag-amazon-pay-charge-modal', {
    template,

    inject: ['SwagAmazonPayOrderService'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    props: {
        paymentDetails: {
            type: Object,
            required: true,
        },
        chargeId: {
            type: String,
            required: true,
        }
    },

    data() {
        return {
            isLoading: false,
            chargeAmount: null,
        };
    },

    beforeMount() {
        this.chargeAmount = this.chargeAmountLimit;
    },

    computed: {
        chargeAmountLimitFormatted() {
            return currency(
                this.chargeAmountLimit,
                this.paymentDetails.chargePermission.currency
            );
        },

        chargeAmountLimit() {
            const charge = this.getCharge();
            return charge.amount - charge.capturedAmount;
        },
    },

    methods: {
        closeModal() {
            this.$emit('modal-close');
        },
        getCharge() {
            return this.paymentDetails.charges[this.chargeId] ? this.paymentDetails.charges[this.chargeId].details : null;
        },

        onConfirm() {
            if (!this.chargeAmount
                // eslint-disable-next-line no-restricted-globals
                || isNaN(this.chargeAmount)
                || !Number.isFinite(this.chargeAmount
                    || this.chargeAmount === 0)
            ) {
                this.chargeAmount = this.chargeAmountLimit;
            }

            const currencyCode = this.paymentDetails.chargePermission.currency;
            const amountFormatted = currency(this.chargeAmount, currencyCode);

            this.isLoading = true;

            this.SwagAmazonPayOrderService.chargePayment(
                this.chargeId,
                this.chargeAmount,
                currencyCode,
            ).then((response) => {
                if (!response.chargeId) {
                    this.createNotificationError({
                        title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                        message: response.message,
                    });

                    this.isLoading = false;
                } else {
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('swag-amazon-pay-order.charge-modal.notification.success', 0, {
                            amount: amountFormatted,
                        }),
                    });

                    this.$emit('reload-payment');
                }
            }).catch((response) => {
                this.createNotificationError({
                    title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                    message: response.message,
                });

                this.isLoading = false;
            });
        },
    },
});
