import Plugin from 'src/plugin-system/plugin.class';

export default class AmazonPayConfirmPagePlugin extends Plugin {
    static options = {
        checkoutSessionId: '',
        libraryUrl: 'https://static-eu.payments-amazon.com/checkout.js',
        changeShippingButtonSelector: '#swag-amazon-pay-change-shipping',
        changePaymentButtonSelector: '#swag-amazon-pay-change-amazon-payment',
    };

    init() {
        this._initializeAmazonScript();
    }

    static scriptElement;

    /**
     * Initializes the  external amazon script.
     *
     * @private
     */
    _initializeAmazonScript() {
        // Script already loaded?
        if (this.scriptElement !== undefined) {
            this._onLoadAmazonPayScript();
        }

        this.$emitter.publish('swagAmazonPay_beforeLoadAmazonPayScript');

        this.scriptElement = document.createElement('script');
        this.scriptElement.type = 'text/javascript';
        this.scriptElement.src = this.options.libraryUrl;

        this.scriptElement.addEventListener('load', this._onLoadAmazonPayScript.bind(this), false);

        document.head.appendChild(this.scriptElement);
    }

    _onLoadAmazonPayScript() {
        window.amazon.Pay.bindChangeAction(this.options.changeShippingButtonSelector, {
            amazonCheckoutSessionId: this.options.checkoutSessionId,
            changeAction: 'changeAddress',
        });

        if (document.querySelector(this.options.changePaymentButtonSelector)) {
            window.amazon.Pay.bindChangeAction(this.options.changePaymentButtonSelector, {
                amazonCheckoutSessionId: this.options.checkoutSessionId,
                changeAction: 'changePayment',
            });
        }
    }
}
