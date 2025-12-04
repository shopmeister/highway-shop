import template from './sm-zalando-connector-extension-settings-orderImport.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('sm-zalando-connector-extension-settings-orderImport', {
    template,
    inject: [
        'ShopmasterZalandoConnectorSettingsFromService',
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    data() {
        return {
            form: {
                salesChannelId: null,
                data: null
            },
            deliveryStateCollection: []
        };
    },
    created() {
        this.createdComponent();
    },
    computed: {
        paymentStateCriteria() {
            return (new Criteria())
                .addAssociation('stateMachine')
                .addFilter(Criteria.equalsAny('stateMachine.technicalName', [
                    'order_transaction.state'
                ]));
        },
        deliveryStateCriteria() {
            return (new Criteria())
                .addAssociation('stateMachine')
                .addFilter(Criteria.equalsAny('stateMachine.technicalName', [
                    'order_delivery.state'
                ]));
        },
        orderStateCriteria() {
            return (new Criteria())
                .addAssociation('stateMachine')
                .addFilter(Criteria.equalsAny('stateMachine.technicalName', [
                    'order.state'
                ]));
        },
        returnTrackingCustomFieldCriteria() {
            return (new Criteria())
                // .addAssociation('stateMachine')
                .addFilter(Criteria.equals('customFieldSet.relations.entityName', 'order'))
                .addFilter(Criteria.equals('type', 'text'));
        },
        salesChannelCriteria() {
            return (new Criteria())
                .addFilter(Criteria.equals('active', true));
        },

        deliveryStatesRepository() {
            return this.repositoryFactory.create('state_machine_state');
        },

    },
    methods: {
        createdComponent() {

        },
        getFormBySalesChannelId(salesChannelId) {
            this.ShopmasterZalandoConnectorSettingsFromService.getFormBySalesChannelAndType(salesChannelId, 'orderImport').then((response) => {
                this.form.data = response;
            });
        },
        saveData() {
            this.ShopmasterZalandoConnectorSettingsFromService.saveForm(this.form.salesChannelId, 'orderImport', this.form.data).then((response) => {
                return this.createNotificationSuccess({
                    title: this.$tc('title.success'),
                    message: this.$tc('message.success')
                });
            });
        },
    },
    watch: {
        // 'form.salesChannelId'(newVal) {
        //     debugger;
        //     if (newVal && newVal.length) {
        //         this.getFormBySalesChannelId(newVal)
        //     }
        // },
        'form.salesChannelId': {
            handler(newVal) {
                if (newVal && newVal.length) {
                    this.getFormBySalesChannelId(newVal)
                }
            },
        },
    },

});