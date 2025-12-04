import template from './swag-amazon-pay-create-charge-modal.html.twig';
import './swag-amazon-pay-create-charge-modal.scss';

const {Component, Mixin} = Shopware;
const {currency} = Shopware.Utils.format;

Component.register('swag-amazon-pay-create-charge-modal', {
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
            return this.paymentDetails.chargePermission.amount - this.paymentDetails.totalChargedAmount;
        },
    },

    methods: {
        closeModal() {
            this.$emit('modal-close');
        },

        onConfirm() {
            if (!this.chargeAmount) {
                this.chargeAmount = this.chargeAmountLimit;
            }
            const currencyCode = this.paymentDetails.chargePermission.currency;
            const amountFormatted = currency(this.chargeAmount, currencyCode);

            this.isLoading = true;

            this.SwagAmazonPayOrderService.createCharge(
                this.paymentDetails.chargePermission.reference,
                this.chargeAmount
            ).then((response) => {
                if (!response.chargeId) {
                    this.createNotificationError({
                        title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                        message: response.message,
                    });
                    this.isLoading = false;
                } else {
                    this.$emit('reload-payment');
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('swag-amazon-pay-order.create-charge-modal.notification.success', 0, {
                            amount: amountFormatted,
                        }),
                    });

                }
            }).catch((response) => {
                this.createNotificationError({
                    title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                    message: response.message,
                });
            }).finally(() => {
                this.isLoading = false;
                this.closeModal();
            });

        },
    },
});
