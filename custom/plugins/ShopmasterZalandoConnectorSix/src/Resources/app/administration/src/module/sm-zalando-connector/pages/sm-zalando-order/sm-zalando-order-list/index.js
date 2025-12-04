import template from './sm-zalando-order-list.html.twig';

Shopware.Component.register('sm-zalando-order-list', {
    template,
    inject: [
        'ShopmasterZalandoConnectorApiOrderService',
    ],
    data() {
        return {
            isLoading: true,
            total: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            listData: []
        };
    },

    created() {
        this.getList();
    },

    computed: {
        getColumns() {
            return [{
                property: 'zalando.id',
                dataIndex: 'zalando.id',
                label: 'zalando.id',
                allowResize: false,
                primary: true,
            }, {
                property: 'zalando.orderNumber',
                dataIndex: 'zalando.orderNumber',
                label: 'zalando.orderNumber',
                allowResize: false,
            }, {
                property: 'zalando.status',
                label: 'zalando.status',
                allowResize: false,
            }, {
                property: 'zalando.createdAt',
                label: 'zalando.createdAt',
                allowResize: false
            }, {
                property: 'shopware.orderNumber',
                dataIndex: 'shopware.orderNumber',
                label: 'shopware.orderNumber',
                allowResize: false,
            }];
        },
    },

    methods: {
        getList() {
            this.ShopmasterZalandoConnectorApiOrderService.getList().then((response) => {
                this.listData = response;
                this.isLoading = false;
            }).catch((error) => {
                console.error('Error fetching data:', error);
                this.isLoading = false;
            });
        },

        // Methode zur Datumsformatierung
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'Invalid Date';
            return date.toLocaleString(); // Beispiel: "10.12.2024, 12:00:00"
        }
    }
});
