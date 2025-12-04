import template from './magnalister-tab.html.twig';
import './magnalister-tab.scss';


const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('magnalister-tab', {
    template,

    inject: ['MagnalisterOrderService'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            globalOrderId: '',
            returnCarrierValue: '',
            returnTrackingNumberValue: '',
            carrierCodeValue: '',
            shipMethodValue: '',
            repository: null,
            hasError: false,
            isLoading: true,
            isEditing: false,
            initialized: false,
            entity: null,
            magnalisterOrderData: {},
            magnalistrLogo: {},
            loaded: false,
            isOtto: false,
            isAmazon: false,
        };
    },
    watch: {

    },
    created() {
        this.globalOrderId = this.$route.params.id;
        this.isMyOrderValid(this.$route.params.id);
        this.fetchAdditional(this.$route.params.id);
        this.fetchAmazonAdditional(this.$route.params.id);
    },

    methods: {
        submitValue() {
            const me = this;
            me.MagnalisterOrderService.storeReturnCarierAndReturnTrakingCode(this.$refs.returnCarrier.value, this.$refs.returnTrackingNumber.value, this.globalOrderId, Shopware.State.get('session').currentUser.id).then((response) => {
                this.createNotificationSuccess({
                    title: this.$tc('magnalister-order.success.title'),
                    message: this.$tc('magnalister-order.success.content')
                });
                
                me.hasError = false;
                me.initialized = true;


            }).catch(() => {
                me.createNotificationError({
                    title: this.$tc('magnalister-order.error.title'),
                    message: this.$tc('magnalister-order.error.content')
                });

                me.hasError = true;
            }).finally(() => {
                me.isLoading = false;
            });

        },
        submitAmazonValue() {
             const me = this;
            me.MagnalisterOrderService.storeCarrierCodeAndShippingMethod(this.$refs.carrierCode.value, this.$refs.shipMethod.value, this.globalOrderId, Shopware.State.get('session').currentUser.id).then((response) => {
                me.createNotificationSuccess({
                    title: me.$tc('magnalister-order.success.title'),
                    message: me.$tc('magnalister-order.success.content')
                });
                
                me.hasError = false;
                me.initialized = true;


            }).catch(() => {
                me.createNotificationError({
                    title: me.$tc('magnalister-order.error.title'),
                    message: me.$tc('magnalister-order.error.content')
                });

                me.hasError = true;
            }).finally(() => {
                me.isLoading = false;
            });
        },
        onCarierInput(event) {
            this.$refs.returnCarrier.value = event.target.value
        },
        onReturnTrackingCodInput(event) {
            this.$refs.returnTrackingNumber.value = event.target.value
        },
        onCarrierCodeInput(event) {
            this.$refs.carrierCode.value = event.target.value
        },
        onShipMethodInput(event) {
            this.$refs.shipMethod.value = event.target.value
        },
        isMyOrderValid(orderId) {
            const me = this;
            me.MagnalisterOrderService.fetchOrderData(orderId, Shopware.State.get('session').currentUser.id).then((response) => {
                me.hasError = false;
                me.initialized = true;

                me.magnalisterOrderData = response.orderContent;
                me.magnalistrLogo = response.logo;
            }).catch(() => {
                me.createNotificationError({
                    title: this.$tc('magnalister-order.error.title'),
                    message: this.$tc('magnalister-order.error.content')
                });

                me.hasError = true;
            }).finally(() => {
                me.isLoading = false;
            });
        },
        fetchAdditional(orderId) {
            const me = this;
            me.MagnalisterOrderService.fetchReturnCarierAndReturnTrakingCode(orderId, Shopware.State.get('session').currentUser.id).then((response) => {
                if (response.Additional != null && response.Additional != '') {
                    this.returnCarrierValue = JSON.parse(response.Additional).returnCarrier;
                    this.returnTrackingNumberValue = JSON.parse(response.Additional).returnTrackingNumber;
                }
                if (response.Marketplace == 'otto') {
                    this.isOtto = true;
                }
                me.hasError = false;
                me.initialized = true;


            }).catch(() => {
                me.createNotificationError({
                    title: this.$tc('magnalister-order.error.title'),
                    message: this.$tc('magnalister-order.error.content')
                });
                me.hasError = true;
            }).finally(() => {
                me.isLoading = false;
            });
        },
        fetchAmazonAdditional(orderId) {
            const me = this;
            me.MagnalisterOrderService.fetchCarierCodeAndShippingMethod(orderId, Shopware.State.get('session').currentUser.id).then((response) => {
                if (response.Additional != null && response.Additional != '') {
                    this.carrierCodeValue = JSON.parse(response.Additional).carrierCode;
                    this.shipMethodValue = JSON.parse(response.Additional).shipMethod;
                }
                if (response.Marketplace == 'amazon') {
                    this.isAmazon = true;
                }

                me.hasError = false;
                me.initialized = true;


            }).catch(() => {
                me.createNotificationError({
                    title: this.$tc('magnalister-order.error.title'),
                    message: this.$tc('magnalister-order.error.content')
                });
                me.hasError = true;
            }).finally(() => {
                me.isLoading = false;
            });
        }
    }
});
