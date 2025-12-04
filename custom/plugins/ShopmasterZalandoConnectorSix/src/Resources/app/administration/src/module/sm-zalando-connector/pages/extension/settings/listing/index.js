import template from './sm-zalando-connector-extension-settings-listing.html.twig';

const {Component, Mixin} = Shopware;

Component.register('sm-zalando-connector-extension-settings-listing', {
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
        console.log('listing');
    },
    methods: {
        getFormBySalesChannelId(salesChannelId) {
            this.ShopmasterZalandoConnectorSettingsFromService.getFormBySalesChannelAndType(salesChannelId, 'listing').then((response) => {
                // Initialize with default values if response is null/empty
                this.form.data = {
                    activeListing: false,
                    listingInterval: 60,
                    ...response
                };
            });
        },
        saveData() {
            console.log('Saving data:', this.form.data);
            this.ShopmasterZalandoConnectorSettingsFromService.saveForm(this.form.salesChannelId, 'listing', this.form.data).then((response) => {
                console.log('Save response:', response);
                return this.createNotificationSuccess({
                    title: this.$tc('title.success'),
                    message: this.$tc('message.success')
                });
            }).catch((error) => {
                console.error('Save error:', error);
                return this.createNotificationError({
                    title: 'Error',
                    message: 'Failed to save settings'
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