import template from './sw-extension-card-base.html.twig';

const { Component } = Shopware;

Component.override('sw-extension-card-base', {
    template,
    methods: {
        onSwagAmazonPaySettings() {
            this.$router.push({ name: 'swag.amazon.pay.configuration.config' });
        },
    },
});
