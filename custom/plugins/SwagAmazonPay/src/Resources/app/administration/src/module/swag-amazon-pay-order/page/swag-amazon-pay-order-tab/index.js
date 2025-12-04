import template from './swag-amazon-pay-order-tab.html.twig';
import './swag-amazon-pay-order-tab.scss';

const {Component, Mixin, Context} = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('swag-amazon-pay-order-tab', {
    template,

    inject: [
        'SwagAmazonPayOrderService',
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    data() {
        return {
            paymentDetails: {
                chargePermission: {
                    limits: {
                        amountLimit: null,
                    },
                    presentmentCurrency: 'EUR',
                    statusDetails: null,
                },
                charge: {
                    captureAmount: null,
                    refundedAmount: null,
                    statusDetails: null,
                },
            },
            refundStatus: null,
            refundPending: false,
            order: null,
            isLoading: true,
            isSuccess: true,
            transaction: null,
            refreshHandler: null,
        };
    },

    computed: {
        orderRepository() {
            return this.repositoryFactory.create('order');
        },

        transactionRepository() {
            return this.repositoryFactory.create('order_transaction');
        },

        isInvalidChargePermission() {
            if (this.isSuccess === true) {
                return false;
            }

            if (this.isLoading === false && !this.paymentDetails.chargePermission.reference) {
                return true;
            }

            return false;
        },

        isPartiallyCharged() {
            if (this.isSuccess !== true) {
                return false;
            }
            return this.paymentDetails.isPartiallyCaptured;
        },
    },

    watch: {
        '$route'() {
            this.resetDataAttributes();
            this.createdComponent();
        },
    },

    beforeDestroy() {
        window.clearInterval(this.refreshHandler);
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadData();
        },

        resetDataAttributes() {
            this.paymentDetails = null;
            this.isLoading = true;
            this.isSuccess = false;
            this.transaction = null;
        },

        reloadOrderDetails(refreshData = false) {
            this.isSuccess = true;
            return this.loadData(refreshData);
        },

        loadTransactionDetails(refreshData = false) {
            const transactionId = this.$route.params.transactionId;
            if(!transactionId) {
                return Promise.resolve();
            }
            const me = this;
            // It's required to reload the whole transaction to get the new custom field values (e.g. after a refund)
            return this.loadTransaction(transactionId).then((transaction) => {
                const chargePermissionId = transaction.customFields.swag_amazon_pay_charge_permission_id;

                if(this.refreshHandler){
                    window.clearInterval(this.refreshHandler);
                }

                //this causes the modals to rerender and overwrite custom input in the amount fields (as long as they are not saved by blur/input event)
                // this.refreshHandler = setInterval(function(){
                //     me.backgroundLoadPaymentHistory(chargePermissionId);
                // }, 10000);

                if (!transaction.customFields || !transaction.customFields.swag_amazon_pay_charge_permission_id) {
                    this.displayErrorMessage();
                    return Promise.reject();
                }


                this.transaction = transaction;

                return this.loadPaymentDetails(chargePermissionId, refreshData).then((response) => {
                    this.isLoading = false;
                    if (!response.chargePermission) {
                        this.displayErrorMessage();
                        return Promise.reject();
                    }
                    this.paymentDetails = response;
                    this.isSuccess = true;
                    return Promise.resolve();
                });
            });
        },

        backgroundLoadPaymentHistory(chargePermissionId){
            this.loadPaymentDetails(chargePermissionId, false).then((response) => {
                if (!response.chargePermission) {
                    return;
                }
                this.paymentDetails = response;
            });
        },

        loadData(refreshData = false) {
            if(refreshData) {
                this.isLoading = true;
            }
            const orderId = this.$route.params.id;

            return this.loadOrder(orderId).then((order) => {
                this.order = order;
                return this.loadTransactionDetails(refreshData);
            });
        },

        loadOrder(orderId) {
            const orderCriteria = new Criteria(1, 1);
            orderCriteria.addAssociation('addresses');
            orderCriteria.addAssociation('currency');
            orderCriteria.addAssociation('deliveries');
            orderCriteria.addAssociation('orderCustomer.salutation');

            return this.orderRepository.get(orderId, Context.api, orderCriteria);
        },

        loadTransaction(transactionId) {
            return this.transactionRepository.get(transactionId, Context.api);
        },

        loadPaymentDetails(chargePermissionId, refreshData = false) {
            return this.SwagAmazonPayOrderService.fetchPaymentDetails(chargePermissionId, refreshData);
        },

        displayErrorMessage(apiError) {
            let errorMessage = this.$tc('swag-amazon-pay-order.general.errors.incompletePaymentProcess');
            if (apiError && apiError.response && apiError.response.data && apiError.response.data.errors) {
                errorMessage = apiError.response.data.errors[0].detail;
            }

            this.createNotificationError({
                title: this.$tc('global.default.error'),
                message: errorMessage,
            });

            this.isSuccess = false;
            this.isLoading = false;
        }
    },
});
