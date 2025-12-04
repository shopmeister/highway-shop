import template from './sw-order-detail.html.twig';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.override('sw-order-detail', {
    template,

    inject: ['MagnalisterOrderService'],

    data() {
        return {
            ismagnalisterOrder: false,

        };
    },
    created() { 
            this.loadMagnaOrderData(this.$route.params.id); 
    },

    methods: {
        loadMagnaOrderData(orderId) {
            const me = this;
            me.MagnalisterOrderService.fetchOrderData(orderId, Shopware.State.get('session').currentUser.id).then((response) => {
                if (response.logo !== '') {
                    me.ismagnalisterOrder = true;
                }
            });

        }
    }

});
