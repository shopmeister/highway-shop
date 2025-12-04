import template from './swag-amazon-pay-cancel-modal.html.twig';
import './swag-amazon-pay-cancel-modal.scss';

const { Component, Mixin } = Shopware;

Component.register('swag-amazon-pay-cancel-modal', {
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
    },

    data() {
        return {
            isLoading: false,
            reason: '',
        };
    },

    methods: {
        closeModal() {
            this.$emit('modal-close');
        },

        onConfirm() {
            if (!this.reason) {
                return;
            }

            this.isLoading = true;

            this.SwagAmazonPayOrderService.cancelPayment(
                this.paymentDetails.chargePermission.reference,
                this.reason,
            ).then((response) => {
                if (response.statusDetails && response.statusDetails.state === 'Closed') {
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('swag-amazon-pay-order.cancel-modal.notification.success'),
                    });

                    this.$emit('reload-payment');

                    return;
                }

                const notificationData = {
                    title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                    message: this.$tc('swag-amazon-pay-order.cancel-modal.notification.error'),
                };

                if (response.message) {
                    notificationData.message = response.message;
                }

                this.createNotificationError(notificationData);
            }).catch((response) => {
                this.createNotificationError({
                    title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                    message: response.message,
                });
            }).finally(() => {
                this.isLoading = false;
            });
        },
    },
});
