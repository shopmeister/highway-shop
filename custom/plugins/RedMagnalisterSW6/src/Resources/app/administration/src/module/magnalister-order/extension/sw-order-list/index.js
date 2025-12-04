import template from './sw-order-list.html.twig';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.override('sw-order-list', {
    template,

    inject: ['MagnalisterOrderService'],

    data() {
        return {
            magnalistrLogo: {},
            orderlistobject: {}

        };
    },

    computed: {

        orderColumns() {

            const columns = this.$super('orderColumns');

            columns.push({
                property: 'magnalister',
                label: 'magnalister-order.order-list.column',
                allowResize: true,
                sortable: false,
                width: '200px',
                align: 'left'
            });
            var orderlistarray = [];

            this.orders.forEach(function (entry) {

                orderlistarray.push(entry.id);
            });

            this.getlog(orderlistarray);
            return columns;
        }


    },

    created() {

    },

    methods: {
        getlog(orderId) {                        
            const me = this;
            me.magnalistrLogo = '';
            this.MagnalisterOrderService.fetchOrderLogo(orderId, Shopware.State.get('session').currentUser.id).then((response) => {

                this.magnalistrLogo = response.logo;
            });
            
        }


    }
});
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


