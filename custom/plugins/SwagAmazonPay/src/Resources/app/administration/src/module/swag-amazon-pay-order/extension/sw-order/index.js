import template from './sw-order.html.twig';
import './sw-order.scss';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.override('sw-order-detail', {
    template,

    data() {
        return {
            isLoadingAmazonTransactions: true,
            amazonPayTransactions: [],
            hasIncompleteTransaction: false,
        };
    },

    computed: {
        showTabs() {
            return true;
        },
    },

    watch: {
        orderId: {
            deep: true,
            handler() {
                this.loadAmazonPayTransactions(this.orderId);
            },
            immediate: true,
        },
    },

    methods: {
        loadAmazonPayTransactions(orderId) {
            if (!orderId) {
                this.isLoadingAmazonPayTransactions = false;

                return;
            }
            this.amazonPayTransactions = [];
            this.isLoadingAmazonPayTransactions = true;

            const orderRepository = this.repositoryFactory.create('order');
            const orderCriteria = new Criteria(1, 1);
            orderCriteria.addAssociation('transactions');

            orderCriteria.addFilter(Criteria.equals('transactions.paymentMethodId', 'f7b88fc9c0104702a96f664dabfe2656'));
            orderCriteria.addFilter(Criteria.equals('id', orderId));

            orderRepository.search(orderCriteria, Context.api).then((searchResult) => {
                const order = searchResult.first();

                if (!order) {
                    return;
                }

                if (!this.identifier) {
                    this.identifier = order.orderNumber;
                }

                order.transactions.forEach((orderTransaction) => {
                    if (orderTransaction.customFields && orderTransaction.customFields.swag_amazon_pay_charge_permission_id) {
                        this.amazonPayTransactions.push(orderTransaction);
                        return;
                    }

                    this.hasIncompleteTransaction = true;
                });
            }).finally(() => {
                this.isLoadingAmazonTransactions = false;
            });
        },

        getAmazonPayDetailsRoute(transactionId) {
            return {
                name: 'swag-amazon-pay-order.payment.detail',
                params: {
                    id: this.$route.params.id,
                    transactionId: transactionId,
                },
            };
        },
    },
});
