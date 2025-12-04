import template from './netzp-shopmanager6-qrcode.html.twig';
import './netzp-shopmanager6-qrcode.scss';

import deDE from './snippet/de-DE';
import enGB from './snippet/en-GB';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('netzp-shopmanager6-qrcode', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        name: {
            type: String,
            required: true
        }
    },

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    data() {
        return {
            repository: null,
            integrationData: null
        }
    },

    computed: {
        getQrCodeUrl() {
            if(this.integrationData) {
                var params = window.location.origin + "@" + this.getAccessKey;
                return 'https://sm.netzperfekt.de/api/qrcode/' + window.btoa(params);
            }
        },

        getAccessKey() {
            var s = "";

            if(this.integrationData) {
                s += "SW6_";
                s += this.integrationData.accessKey + "_";
                s += this.integrationData.customFields.netzp_shopmanager_key;
            }

            return s;
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('integration');
        const criteria = new Criteria();
        criteria.addFilter(Criteria.equals('customFields.netzp_shopmanager_type', 'sm'));

        this.repository.search(criteria, Shopware.Context.api)
            .then((response) => {
                this.integrationData = response[0]
            });
    }
});
