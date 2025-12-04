import template from './sm-zalando-connector-extension-settings-stockSync.html.twig';

const {Component, Mixin} = Shopware;

Component.register('sm-zalando-connector-extension-settings-stockSync', {
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
                salesChannelId: 'global',
                data: null
            }
        };
    },
    created() {
        this.getFormBySalesChannelId('global');
    },
    methods: {
        getFormBySalesChannelId(salesChannelId) {
            this.ShopmasterZalandoConnectorSettingsFromService.getFormBySalesChannelAndType(salesChannelId, 'stockSync').then((response) => {
                this.form.data = response;
            });
        },
        saveData() {
            this.ShopmasterZalandoConnectorSettingsFromService.saveForm(this.form.salesChannelId, 'stockSync', this.form.data).then((response) => {
                return this.createNotificationSuccess({
                    title: this.$tc('title.success'),
                    message: this.$tc('message.success')
                });
            });
        }
    }
});