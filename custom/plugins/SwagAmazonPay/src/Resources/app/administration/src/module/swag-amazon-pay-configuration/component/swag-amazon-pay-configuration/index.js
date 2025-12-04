import template from './swag-amazon-pay-configuration.html.twig';
import './swag-amazon-pay-configuration.scss';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('swag-amazon-pay-configuration', {

    template,

    inject: [
        'SwagAmazonPayConfigService',
        'systemConfigApiService',
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        'notification',
    ],

    props: {
        configDomain: {
            type: String,
            required: false,
            default: null,
        },
        parentLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            hasSalesChannelSelect: null,
            salesChannelId: null,
            isTestSuccessful: false,
            isTesting: false,
            showInputKeyModal: false,
            ipnUrl: 'https://example.com/',
            ipnUrlLoaded: false,
            availableLogFiles: [],
            fallbackConfig: null,
            isLoading: false,
            settingDefaultSalesChannelLoading: false,

            // @see src/Components/Config/ConfigServiceInterface.php
            defaultConfig: {
                merchantId: null,
                publicKeyId: null,
                privateKey: null,
                clientId: null,
                sandbox: false,
                hideOneClickCheckoutButtons: false,
                displayButtonOnProductPage: true,
                displayButtonOnListingPage: false,
                displayButtonOnCheckoutRegisterPage: true,
                paymentStateMappingCharge: null,
                paymentStateMappingPartialCharge: null,
                paymentStateMappingRefund: null,
                paymentStateMappingPartialRefund: null,
                paymentStateMappingCancel: null,
                paymentStateMappingAuthorize: null,
                authMode: 'immediately',
                chargeMode: 'direct',
                orderChargeTriggerState: null,
                orderRefundTriggerState: null,
                excludedItems: null,
                sendErrorMail: null,
                loggingMode: 'basic',
                ledgerCurrency: 'EUR',
                softDescriptor: null,
                displayLoginButtonOnRegistrationPage: true,
                inheritFromDefault: false,
                buttonColor: 'Gold',
                excludedProductIds: [],
                excludedProductStreamIds: [],
            },
            config: {},
            configBeforeInheritanceSwitch: null,
        };
    },

    computed: {
        getRelevantConfigSalesChannelId() {
            if (this.config.inheritFromDefault === true) {
                return null;
            }

            return this.salesChannelId;
        },

        isChargeOnShipping() {
            const chargeMode = this.config.chargeMode;

            return chargeMode === 'shipped';
        },

        isInheritanceSwitchVisible() {
            return !!(!this.isLoading && this.salesChannelId);
        },

        getLedgerCurrencyOptions() {
            return [
                {
                    value: 'EUR',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.ledgerCurrency.options.EUR'),
                },
                {
                    value: 'GBP',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.ledgerCurrency.options.GBP'),
                },
                {
                    value: 'USD',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.ledgerCurrency.options.USD'),
                },
            ];
        },

        getAuthModeOptions() {
            return [
                {
                    value: 'immediately',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.authMode.options.immediately'),
                },
                {
                    value: 'canHandlePending',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.authMode.options.canHandlePending'),
                },
            ];
        },

        getChargeModeOptions() {
            return [
                {
                    value: 'direct',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.chargeMode.options.direct'),
                },
                {
                    value: 'shipped',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.chargeMode.options.shipped'),
                },
                {
                    value: 'manually',
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.chargeMode.options.manually'),
                },
            ];
        },

        getLoggingOptions() {
            return [
                {
                    value: 'basic',
                    name: this.$tc('swag-amazon-pay-configuration.configForm.fields.loggingMode.options.basic'),
                },
                {
                    value: 'advanced',
                    name: this.$tc('swag-amazon-pay-configuration.configForm.fields.loggingMode.options.advanced'),
                },
            ];
        },

        isInherited() {
            if (!this.salesChannelId) {
                return false;
            }

            return this.config.inheritFromDefault;
        },

        /**
         *
         * @returns {Object.Criteria}
         */
        getOrderStateCriteria() {
            const criteria = new Criteria(1, 100);
            criteria.addAssociation('stateMachine');
            criteria.addFilter(
                Criteria.equalsAny(
                    'state_machine_state.stateMachine.technicalName',
                    ['order.state'],
                ),
            );

            return criteria;
        },

        /**
         *
         * @returns {Object.Criteria}
         */
        getPaymentStateCriteria() {
            const criteria = new Criteria(1, 100);
            criteria.addAssociation('stateMachine');
            criteria.addFilter(
                Criteria.equalsAny(
                    'state_machine_state.stateMachine.technicalName',
                    ['order_transaction.state'],
                ),
            );

            return criteria;
        },

        showLoader() {
            return this.parentLoading || this.isLoading;
        },

        buttonColorOptions() {
            return [
                {
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.buttonColors.gold'),
                    value: 'Gold',
                },
                {
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.buttonColors.lightGray'),
                    value: 'LightGray',
                },
                {
                    label: this.$tc('swag-amazon-pay-configuration.configForm.fields.buttonColors.darkGray'),
                    value: 'DarkGray',
                },
            ];
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        productStreamRepository() {
            return this.repositoryFactory.create('product_stream');
        },

        excludedProductCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');

            return criteria;
        },
    },

    watch: {
        config() {
            this.emitConfigChange();
        },
        hasSalesChannelSelect(newValue) {
            this.emitConfigChange();
            if(!newValue && this.salesChannelId !== null) {
                this.onSalesChannelChanged(null);
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        emitConfigChange() {
            this.$emit('change-config', this.config, this.salesChannelId, !this.hasSalesChannelSelect);
        },
        createdComponent() {
            this.loadConfig();
            this.getIpnUrl();
            this.getLogFiles();
        },

        /**
         * Loading configuration elements, remove domain and arrange them in a global config array
         */
        loadConfig() {
            const result = {};

            this.isLoading = true;

            if (this.hasSalesChannelSelect === null) {
                this.systemConfigApiService.getValues(this.configDomain, null)
                    .then((config) => {
                        if (config['SwagAmazonPay.settings.noInheritance']) {
                            this.hasSalesChannelSelect = false;
                        } else {
                            this.hasSalesChannelSelect = true;
                        }
                    });
            }

            this.systemConfigApiService.getValues(this.configDomain, this.salesChannelId)
                .then((config) => {
                    // Remove domain and config prefix to get variable names on root level
                    Object.keys(config).forEach((key) => {
                        const configActualKey = key.replace(`${this.configDomain}.`, '');

                        result[configActualKey] = config[key];
                    });
                    if (result['SwagAmazonPay.settings.noInheritance']) {
                        delete result['SwagAmazonPay.settings.noInheritance']
                    }

                    // No config saved? Write default
                    if (Object.keys(result).length < 1) {
                        this.config = {...this.defaultConfig};

                        return;
                    }

                    // If a new configuration key is missing, for example due to a plugin update add defaultConfig value
                    Object.keys(this.defaultConfig).forEach((key) => {
                        if (Shopware.Utils.object.hasOwnProperty(result, key)) {
                            return;
                        }

                        result[key] = this.defaultConfig[key];
                    });

                    if (!this.salesChannelId) {
                        this.config = result;
                        this.fallbackConfig = result;

                        return;
                    }

                    if (result.inheritFromDefault) {
                        this.config = this.fallbackConfig;
                        this.config.inheritFromDefault = true;
                        this.configBeforeInheritanceSwitch = result;

                        return;
                    }

                    this.config = result;
                }).finally(() => {
                this.isLoading = false;
            });
        },

        /**
         * @returns {string}
         */
        getPrivateKeyPlaceholder() {
            if (this.config.privateKey) {
                return 'XXXXXXXXXXX';
            }

            return this.$tc('swag-amazon-pay-configuration.configForm.privateKeyEmpty');
        },

        /**
         * Request and set IPN url
         *
         * @returns void
         */
        getIpnUrl() {
            this.SwagAmazonPayConfigService.getIpnUrl().then(response => {
                this.ipnUrl = response.url;
                this.ipnUrlLoaded = true;
            });
        },

        getLogFiles() {
            this.SwagAmazonPayConfigService.getLogFiles().then(response => {
                Object.keys(response).forEach(item => this.addLogFile(response, item));
            });
        },

        addLogFile(response, item) {
            this.availableLogFiles.push({fileName: response[item]});
        },

        onTest() {
            this.isTestSuccessful = false;
            this.isTesting = true;

            const credentials = {
                merchantId: this.config.merchantId,
                publicKeyId: this.config.publicKeyId,
                privateKey: this.config.privateKey,
                ledgerCurrency: this.config.ledgerCurrency,
                clientId: this.config.clientId,
                sandbox: this.config.sandbox,
                salesChannel: this.salesChannelId,
            };

            this.SwagAmazonPayConfigService.validateCredentials(credentials)
                .then(response => {
                    if (response.success) {
                        this.createNotificationSuccess({
                            title: this.$tc('swag-amazon-pay-configuration.notification.inspectConnectionTitle'),
                            message: this.$tc(response.message),
                        });

                        this.isTestSuccessful = true;

                        setTimeout(this.resetTestStatus, 2000);
                    } else {
                        this.createNotificationError({
                            title: this.$tc('swag-amazon-pay-configuration.notification.inspectConnectionTitle'),
                            message: this.$tc(response.message) + response.exceptionMessage,
                        });
                    }
                }).catch(() => {
                this.createNotificationError({
                    title: this.$tc('swag-amazon-pay-configuration.notification.inspectConnectionTitle'),
                    message: this.$tc('swag-amazon-pay-configuration.exception.genericError'),
                });
            }).finally(() => {
                this.isTesting = false;
            });
        },

        onSwitchInheritance(useInheritance) {
            if (useInheritance) {
                // Create a working copy of the current form to be able to restore it after switching back to no inheritance
                this.configBeforeInheritanceSwitch = {...this.config};
                this.configBeforeInheritanceSwitch.inheritFromDefault = false;

                this.config = this.fallbackConfig;
                this.config.inheritFromDefault = true;
            } else {
                this.config = {...this.configBeforeInheritanceSwitch};
                this.config.inheritFromDefault = false;
            }
            this.emitConfigChange();
        },

        displayInputKeyModal() {
            this.showInputKeyModal = true;
        },

        onUpdatePrivateKey(value) {
            this.config.privateKey = value;
            this.showInputKeyModal = false;
        },

        resetTestStatus() {
            this.isTestSuccessful = false;
        },

        onCloseModal() {
            this.showInputKeyModal = false;
        },

        onSalesChannelChanged(salesChannelId) {
            this.salesChannelId = salesChannelId;
            this.$emit('change-sales-channel', salesChannelId);
            this.loadConfig();
        },

        onSetAmazonPayAsDefaultPaymentMethod() {
            this.settingDefaultSalesChannelLoading = true;

            this.SwagAmazonPayConfigService.salesChannelDefault(this.salesChannelId).then(() => {
                window.setTimeout(() => {
                    this.settingDefaultSalesChannelLoading = false;
                }, 250);
            });
        },
    },
});
