import './orderImport';
import './stockSync';
import './priceSync';
import './priceReportSync';
import './listing';

import template from './sm-zalando-connector-extension-settings.html.twig';

Shopware.Component.register('sm-zalando-connector-extension-settings', {
    template,
    data() {
        return {};
    },
    created() {

    },
});