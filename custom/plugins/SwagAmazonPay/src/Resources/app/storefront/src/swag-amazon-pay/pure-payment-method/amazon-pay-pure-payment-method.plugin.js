import Plugin from 'src/plugin-system/plugin.class';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

/**
 * This plugin is registered on the /checkout/amazon-pay-init-checkout/{orderTransactionId} page.
 */
export default class AmazonPayPurePaymentMethodPlugin extends Plugin {
    static options = {
        libraryUrl: 'https://static-eu.payments-amazon.com/checkout.js',
        merchantId: '',
        ledgerCurrency: 'EUR',
        sandbox: true,
        productType: 'PayAndShip',
        createCheckoutSessionConfig: {
            payloadJSON: '',
            signature: '',
            publicKeyId: '',
        },
    };

    init() {
        // Always add loading spinner to show that something is happening in the background
        ElementLoadingIndicatorUtil.create(this.el);

        this._initializeAmazonScript();
    }

    /**
     * Initializes the external amazon script.
     *
     * @private
     */
    _initializeAmazonScript() {
        this.$emitter.publish('swagAmazonPay_beforeLoadAmazonPayScript');

        // Script already loaded?
        if (this.scriptElement !== undefined) {
            this._onLoadAmazonPayScript();
        }

        this.scriptElement = document.createElement('script');
        this.scriptElement.type = 'text/javascript';
        this.scriptElement.src = this.options.libraryUrl;

        // This will trigger the checkout initialization immediately after the script is loaded
        this.scriptElement.addEventListener('load', this._onLoadAmazonPayScript.bind(this), false);

        document.head.appendChild(this.scriptElement);
    }

    /**
     * @private
     */
    _onLoadAmazonPayScript() {
        window.amazon.Pay.initCheckout({
            merchantId: this.options.merchantId,
            ledgerCurrency: this.options.ledgerCurrency,
            sandbox: !!this.options.sandbox,
            productType: this.options.productType,
            placement: 'Checkout',
            createCheckoutSessionConfig: this.options.createCheckoutSessionConfig,
        });
    }
}
