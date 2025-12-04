import template from './sm-zalando-connector-components-settings-api-test-button.html.twig';

const {Component, Mixin} = Shopware;

Component.register('sm-zalando-connector-components-settings-api-test-button', {
    template,
    inject: [
        'ShopmasterZalandoConnectorApiSalesChannelService',
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false
        };
    },
    computed: {},
    methods: {
        check() {
            this.isLoading = true;
            this.getSalesChannels();
        },
        getSalesChannels() {
            let prom = this.ShopmasterZalandoConnectorApiSalesChannelService.getSalesChannels();
            if (!(prom instanceof Promise)) {
                return;
            }
            prom.then((response) => {
                this.isLoading = false;
                this.createNotificationSuccess({
                    title: this.$tc('sm-zalando-connector-components-settings-api-test-button.title'),
                    message: this.$tc('sm-zalando-connector-components-settings-api-test-button.success')
                });
            }).catch((e) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('sm-zalando-connector-components-settings-api-test-button.title'),
                    message: this.$tc('sm-zalando-connector-components-settings-api-test-button.error')
                });
            });
        }
    }
});