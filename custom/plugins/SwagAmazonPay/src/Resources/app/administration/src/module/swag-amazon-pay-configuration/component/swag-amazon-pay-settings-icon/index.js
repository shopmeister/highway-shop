import template from './swag-amazon-pay-settings-icon.html.twig';
import './swag-amazon-pay-settings-icon.scss';

const { Component } = Shopware;

Component.register('swag-amazon-pay-settings-icon', {
    template,
    computed:{
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    }
});
