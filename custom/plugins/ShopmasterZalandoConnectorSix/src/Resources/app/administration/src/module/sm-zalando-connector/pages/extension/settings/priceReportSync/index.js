import template from './sm-zalando-connector-extension-settings-priceReportSync.html.twig';

const {Component, Mixin} = Shopware;

Component.register('sm-zalando-connector-extension-settings-priceReportSync', {
    template,
    inject: [
        'ShopmasterZalandoConnectorSettingsFromService',
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    data() {
        return {
            form: {
                salesChannelId: null,
                data: null
            }
        };
    },
    created() {
        console.log('priceReportSync');
    },
    methods: {
        getFormBySalesChannelId(salesChannelId) {
            this.ShopmasterZalandoConnectorSettingsFromService.getFormBySalesChannelAndType(salesChannelId, 'priceReportSync').then((response) => {
                this.form.data = response;
            });
        },
        saveData() {
            this.ShopmasterZalandoConnectorSettingsFromService.saveForm(this.form.salesChannelId, 'priceReportSync', this.form.data).then((response) => {
                return this.createNotificationSuccess({
                    title: this.$tc('title.success'),
                    message: this.$tc('message.success')
                });
            });
        }
    },
    watch: {
        'form.salesChannelId'(newVal) {
            if (newVal && newVal.length) {
                this.getFormBySalesChannelId(newVal)
            }
        },

    }
});