import template from './swag-amazon-pay-settings.html.twig';
import './swag-amazon-pay-settings.scss';

const { Component } = Shopware;

Component.register('swag-amazon-pay-settings', {
    template,

    inject: [
        'SwagAmazonPayConfigService',
        'systemConfigApiService',
        'acl',
    ],

    mixins: [
        'notification',
        'sw-inline-snippet',
    ],

    data() {
        return {
            isSaveSuccessful: false,
            isExporting: false,
            isImporting: false,
            showConfigImportModal: false,
            config: null,
            noInheritance: true,
            configDomain: 'SwagAmazonPay.settings',
            salesChannelId: null,
            isLoading: false,
        };
    },

    metaInfo() {
        return {
            title: this.$tc('swag-amazon-pay-configuration.module.title'),
        };
    },

    methods: {
        /**
         * Called when the save button was clicked. Saves the configuration.
         */
        async onSave() {
            this.isLoading = true;
            if(this.salesChannelId !== null) {
                if (this.noInheritance !== null) {
                    await this.systemConfigApiService.saveValues({
                        'SwagAmazonPay.settings.noInheritance': this.noInheritance
                    }, null);
                }
            }else{
                this.config.noInheritance = this.noInheritance;
            }

            const configToBeSaved = {};

            // Decide if all values should be saved (separate config for this sales channel)
            // or inheritance should come in place
            if (!this.config.inheritFromDefault) {
                Object.keys(this.config).forEach((key) => {
                    const configActualKey = this.configDomain.concat('.').concat(key);

                    configToBeSaved[configActualKey] = this.config[key];
                });
            } else {
                const key = this.configDomain.concat('.').concat('inheritFromDefault');

                configToBeSaved[key] = true;
            }

            this.$emit('before-save-config', configToBeSaved);

            this.systemConfigApiService.saveValues(configToBeSaved, this.salesChannelId).then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('global.default.success'),
                    message: this.$tc('swag-amazon-pay-configuration.notification.configSaveSuccessMessage'),
                });

                this.isSaveSuccessful = true;

                this.$emit('save-config-success');
            }).catch((error) => {
                this.$emit('save-config-error', error);

                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: error,
                });
            }).finally(() => {
                this.$emit('after-save-config', configToBeSaved);

                this.isLoading = false;
            });
        },

        /**
         * Called if a config field value changes inside the form.
         * Functions as bind back for the actual config form.
         *
         * @param config
         * @param salesChannelId
         * @param noInheritance
         */
        onConfigChange(config, salesChannelId, noInheritance) {
            this.config = config;
            this.salesChannelId = salesChannelId;
            this.noInheritance = noInheritance;
        },

        onSalesChannelChange(salesChannelId) {
            this.salesChannelId = salesChannelId;
        },

        onExportConfig() {
            this.isExporting = true;

            this.SwagAmazonPayConfigService.exportConfig().then(response => {
                const url = window.URL.createObjectURL(new Blob([JSON.stringify(response)]));
                const link = document.createElement('a');

                link.href = url;
                link.setAttribute('download', 'swag-amazon-pay.config.json');

                document.body.appendChild(link);
                link.click();

                document.body.removeChild(link);

                this.isExportSuccessful = true;
            }).catch(() => {
                this.createNotificationError({
                    title: this.$tc('swag-amazon-pay-configuration.exception.exportConfig.title'),
                    message: this.$tc('swag-amazon-pay-configuration.exception.exportConfig.message'),
                });
            }).finally(() => {
                this.isExporting = false;
                this.isExportSuccessful = false;
            });
        },

        onCloseModal() {
            this.showConfigImportModal = false;
            this.isImporting = false;
        },

        onImportConfig() {
            this.showConfigImportModal = true;
            this.isImporting = true;
        },
    },
});
