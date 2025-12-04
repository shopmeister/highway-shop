import template from './swag-amazon-pay-configuration-upload-modal.html.twig';

const { Component, Mixin } = Shopware;

Component.register('swag-amazon-pay-configuration-upload-modal', {
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
            isLoading: false,
            selectedFile: null,
            versions: null,
        };
    },

    methods: {
        onSubmit(ignoreVersionMismatch = false) {
            this.versions = null;

            if (!this.selectedFile) {
                this.createNotificationError({
                    title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                    message: this.$tc('swag-amazon-pay-configuration.importModal.notification.noFileSelected'),
                });

                return;
            }

            this.isLoading = true;

            this.SwagAmazonPayConfigService.importConfig(this.selectedFile, ignoreVersionMismatch).then((response) => {
                this.isLoading = false;

                if ('current' in response && 'state' in response) {
                    this.versions = response;
                    return;
                }

                this.createNotificationSuccess({
                    title: this.$tc('global.default.success'),
                    message: this.$tc('swag-amazon-pay-configuration.importModal.notification.success'),
                });

                setTimeout(() => window.location.reload(), 1000);
            }).catch(() => {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                    message: this.$tc('swag-amazon-pay-configuration.importModal.notification.error'),
                });
            });
        },

        /**
         * Called when the user clicks the close or cancel button of the modal.
         */
        onCancel() {
            this.$emit('modal-close');
        },

        onCancelNotification() {
            this.versions = null;
        },
    },
});
