import template from './swag-amazon-pay-configuration-log-download.html.twig';

const { Component, Mixin } = Shopware;

Component.register('swag-amazon-pay-configuration-log-download', {
    template,

    inject: [
        'SwagAmazonPayConfigService',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    props: {
        availableLogFiles: {
            type: Array,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            selectedLogFiles: [],
            isPreparingArchive: false,
            downloadPath: null,
        };
    },

    methods: {
        onDownloadLogFile() {
            if (!this.selectedLogFiles) {
                return;
            }

            if (this.downloadPath) {
                this.downloadFile(this.downloadPath, Shopware.Context.api);

                return;
            }

            this.isPreparingArchive = true;
            this.SwagAmazonPayConfigService.generateLogArchive(this.selectedLogFiles).then((downloadPath) => {
                this.downloadFile(downloadPath, Shopware.Context.api);
            }).catch(() => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('swag-amazon-pay-configuration.exception.genericError'),
                });
            }).finally(() => {
                this.isPreparingArchive = false;
            });
        },

        downloadFile(downloadPath) {
            this.downloadPath = downloadPath;

            this.SwagAmazonPayConfigService.downloadLogArchive(downloadPath).then((fileData) => {
                const link = document.createElement('a');

                link.setAttribute('download', 'swag-amazon-pay-logs.zip');
                link.href = window.URL.createObjectURL(new Blob([fileData]));

                document.body.appendChild(link);

                link.click();

                document.body.removeChild(link);
            });
        },

        onChangeFileSelection() {
            this.downloadPath = null;
        },
    },
});
