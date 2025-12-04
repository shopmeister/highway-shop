import template from './sm-zalando-connector-components-settings-logistic-center.html.twig';

const {Component, Mixin} = Shopware;

Component.register('sm-zalando-connector-components-settings-logistic-center', {
    template,
    inject: [
        'ShopmasterZalandoConnectorSettingsFromService',
    ],
    props: {
        value: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            logisticId: this.value,
            logisticCenters: [],
        };
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getLogisticCenters();
        },
        getLogisticCenters() {
            this.ShopmasterZalandoConnectorSettingsFromService.getLogisticCenters().then((response) => {
                this.logisticCenters = response;
            });
        }
    },
    watch: {
        'logisticId'(newVal) {
            this.$emit('input', newVal ?? '');
        },
    }
});