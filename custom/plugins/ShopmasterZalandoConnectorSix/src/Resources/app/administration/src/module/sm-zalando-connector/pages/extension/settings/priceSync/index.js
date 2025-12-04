import template from './sm-zalando-connector-extension-settings-priceSync.html.twig';

const {Component, Mixin} = Shopware;

Component.register('sm-zalando-connector-extension-settings-priceSync', {
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
        console.log('priceSync');
    },
    methods: {
        getFormBySalesChannelId(salesChannelId) {
            this.ShopmasterZalandoConnectorSettingsFromService.getFormBySalesChannelAndType(salesChannelId, 'priceSync').then((response) => {
                this.form.data = response;
            });
        },
        saveData() {
            this.ShopmasterZalandoConnectorSettingsFromService.saveForm(this.form.salesChannelId, 'priceSync', this.form.data).then((response) => {
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