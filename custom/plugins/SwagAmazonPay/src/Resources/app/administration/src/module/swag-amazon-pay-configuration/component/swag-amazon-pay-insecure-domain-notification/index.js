import template from './swag-amazon-pay-insecure-domain-notification.html.twig';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('swag-amazon-pay-insecure-domain-notification', {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    props: {
        salesChannelId: {
            required: false,
            type: String,
            default: '',
        },
    },

    data() {
        return {
            insecureDomains: [],
        };
    },

    computed: {
        salesChannelDomainRepository() {
            return this.repositoryFactory.create('sales_channel_domain');
        },

        hasInsecureDomains() {
            if (!this.salesChannelId) {
                return false;
            }

            if (!(this.salesChannelId in this.insecureDomains)) {
                return false;
            }

            return true;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadInsecureDomains();
        },

        loadInsecureDomains() {
            const criteria = new Criteria(1, 500);

            criteria.addFilter(Criteria.multi(
                'AND',
                [
                    Criteria.contains('url', 'http://'),
                ],
            ));

            return this.salesChannelDomainRepository
                .search(criteria, Shopware.Context.api)
                .then((result) => {
                    result.forEach((domain) => {
                        const key = domain.salesChannelId;
                        const url = domain.url;

                        if (!(key in this.insecureDomains)) {
                            this.insecureDomains[key] = [];
                        }

                        this.insecureDomains[key].push(url);
                    });
                });
        },
    },
});
