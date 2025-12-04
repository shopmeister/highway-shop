import template from './swag-amazon-pay-payment-history.html.twig';
import './swag-amazon-pay-payment-history.scss';
const {Component, Context} = Shopware;
const { currency } = Shopware.Utils.format;

Component.register('swag-amazon-pay-payment-history', {
    template,
    inject: [
        'acl',
    ],
    props: {
        paymentDetails: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            paymentNotifications: [],
            refundModalVisible: false,
            captureModalVisible: false,
            refundChargeId: null,
            captureChargeId: null,
            repository: null,
            isLoading: false,
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: this.$tc('swag-amazon-pay-order.payment-history.columns.subject'),
                    primary: true,
                },
                {
                    property: 'reference',
                    dataIndex: 'reference',
                    label: this.$tc('swag-amazon-pay-order.payment-history.columns.objectId'),
                },
                {
                    property: 'amount',
                    dataIndex: 'amount',
                    label: this.$tc('swag-amazon-pay-order.payment-history.columns.amount'),
                },
                {
                    property: 'status',
                    dataIndex: 'status',
                    align: 'center',
                    label: this.$tc('swag-amazon-pay-order.payment-history.columns.status'),
                },
                {
                    property: 'time',
                    dataIndex: 'time',
                    label: this.$tc('swag-amazon-pay-order.payment-history.columns.date'),
                },
                {
                    property: 'actions',
                    dataIndex: 'actions',
                    label: this.$tc('swag-amazon-pay-order.payment-history.columns.actions'),
                }
            ];
        },
        dateFilter() {
            return Shopware.Filter.getByName('date');
        },
    },

    created() {
    },

    methods: {
        showRefundModal(transaction) {
            this.refundChargeId = transaction.reference;
            this.refundModalVisible = true;
        },
        showCaptureModal(transaction) {
            this.captureChargeId = transaction.reference;
            this.captureModalVisible = true;
        },
        closeModals() {
            this.captureModalVisible = false;
            this.refundModalVisible = false;
        },
        reloadPaymentDetails() {
            this.closeModals();

            // Wait for the next tick to trigger the reload. Otherwise, the Modal won't be hidden correctly.
            this.$nextTick().then(() => {
                this.$emit('reload-payment');
            });
        },
        formatCurrency(value) {
            return currency(
                value || 0.0,
                this.paymentDetails.chargePermission.currency
            );
        }
    },
});
