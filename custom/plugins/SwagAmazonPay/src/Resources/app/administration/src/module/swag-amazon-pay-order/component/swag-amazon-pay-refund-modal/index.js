import template from './swag-amazon-pay-refund-modal.html.twig';
import './swag-amazon-pay-refund-modal.scss';

const {Component, Mixin} = Shopware;
const {currency} = Shopware.Utils.format;

Component.register('swag-amazon-pay-refund-modal', {
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
        },
    },

    data() {
        return {
            isLoading: false,
            refundAmount: null,
            reason: '',
        };
    },

    beforeMount() {
        this.refundAmount = this.getDefaultRefundAmount();
    },
    computed: {
        refundAmountLimit() {
            const charge = this.getCharge();
            if (charge === null) {
                return 0.0;
            }
            return charge.maxRefundableAmount;
        },

        refundAmountLimitFormatted() {
            return currency(
                this.refundAmountLimit,
                this.paymentDetails.chargePermission.currency,
                2
            );
        },

        refundAmountDefault() {
            this.getDefaultRefundAmount();
        },
    },

    methods: {
        closeModal() {
            this.$emit('modal-close');
        },

        getDefaultRefundAmount() {
            const charge = this.getCharge();
            if (charge === null) {
                return 0.0;
            }
            return charge.defaultRefundableAmount;
        },

        getCharge() {
            return this.paymentDetails.charges[this.chargeId] ? this.paymentDetails.charges[this.chargeId] : null;
        },

        validateForm() {
            if (!this.refundAmount
                // eslint-disable-next-line no-restricted-globals
                || isNaN(this.refundAmount)
                || !Number.isFinite(this.refundAmount)
                || this.refundAmount === 0
                || this.refundAmount > this.refundAmountLimit
            ) {
                this.refundAmount = this.refundAmountDefault;
            }

            return true;
        },

        onConfirm() {
            if (this.validateForm() !== true) {
                return;
            }

            const currencyCode = this.paymentDetails.chargePermission.currency;
            const amountFormatted = currency(this.refundAmount, currencyCode, 2);

            this.isLoading = true;

            this.SwagAmazonPayOrderService.refundPayment(
                this.chargeId,
                this.refundAmount,
                currencyCode,
                this.reason,
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
                        message: this.$tc('swag-amazon-pay-order.refund-modal.notification.success', 0, {
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
