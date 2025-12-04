import template from './swag-amazon-pay-configuration-input-key-modal.html.twig';

const { Component, Mixin } = Shopware;

Component.register('swag-amazon-pay-configuration-input-key-modal', {
    template,

    inject: [
        'SwagAmazonPayConfigService',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    data() {
        return {
            privateKey: '',
        };
    },

    methods: {
        /**
         *
         */
        onConfirm() {
            if (this.privateKey.includes('BEGIN RSA PRIVATE KEY') === false
                && this.privateKey.includes('BEGIN PRIVATE KEY') === false
            ) {
                this.createNotificationWarning({
                    title: this.$tc('swag-amazon-pay-configuration.inputKeyModal.notification.warning'),
                    message: this.$tc('swag-amazon-pay-configuration.inputKeyModal.notification.invalidKeyFormat'),
                });

                return;
            }

            this.$emit('update-private-key', this.privateKey);
        },

        /**
         * Called when the user clicks the close or cancel button of the modal.
         */
        onCancel() {
            this.$emit('modal-close');
        },
    },
});
