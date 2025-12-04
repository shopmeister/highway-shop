import template from './sm-zalando-connector-components-settings-sales-channel.html.twig';

Shopware.Component.register('sm-zalando-connector-components-settings-sales-channel', {
    template,
    inject: [
        'ShopmasterZalandoConnectorApiSalesChannelService',
    ],
    props: {
        selected: {
            type: String
        }
    },
    data() {
        return {
            salesChannels: [],
            show: false
        };
    },
    computed: {},
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getSalesChannels();

        },
        getSalesChannels() {
            this.ShopmasterZalandoConnectorApiSalesChannelService.getSalesChannels().then((response) => {
                this.salesChannels = response;
                this.show = true;
            });

        },
        updateSelected: function (value) {
            this.$emit('input', value);
        }
    }
});