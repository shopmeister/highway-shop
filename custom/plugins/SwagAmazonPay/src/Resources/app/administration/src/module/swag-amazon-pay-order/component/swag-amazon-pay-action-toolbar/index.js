import template from './swag-amazon-pay-action-toolbar.html.twig';
import './swag-amazon-pay-action-toolbar.scss';

const {Component} = Shopware;

Component.register('swag-amazon-pay-action-toolbar', {
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
            cancelModalVisible: false,
            createChargeModalVisible: false,
        };
    },

    computed: {
        disableCancellation() {
            const chargePermissionStatusDetails = this.paymentDetails.chargePermission.status;

            if (chargePermissionStatusDetails === null) {
                return true;
            }

            return chargePermissionStatusDetails === 'Closed';
        },
    },

    methods: {
        showCancelModal() {
            this.cancelModalVisible = true;
        },
        showCreateChargeModal() {
            this.createChargeModalVisible = true;
        },

        closeModals() {
            this.cancelModalVisible = false;
            this.createChargeModalVisible = false;
        },
        updatePaymentDetails() {
            this.$emit('update-payment-details');
        },
        reloadPaymentDetails() {
            this.closeModals();

            // Wait for the next tick to trigger the reload. Otherwise, the Modal won't be hidden correctly.
            this.$nextTick().then(() => {
                this.$emit('reload-payment');
            });
        },
    },
});
