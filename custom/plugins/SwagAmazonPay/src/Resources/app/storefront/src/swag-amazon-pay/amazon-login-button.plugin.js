import Plugin from 'src/plugin-system/plugin.class';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class AmazonLoginButtonPlugin extends Plugin {
    static options = {
        libraryUrl: 'https://static-eu.payments-amazon.com/checkout.js',
        errorElementId: 'swag-amazon-pay-button-error',
        loadingElementId: 'swag-amazon-pay-loading-indicator',
        buttonConfig: {
            merchantId: '',
            sandbox: false,
            ledgerCurrency: '',
            checkoutLanguage: '',
            productType: '',
            placement: '',
            signInConfig: {
                payloadJSON: '',
                signature: '',
                publicKeyId: '',
            },
            buttonColor: 'Gold',
        },
    };

    static scriptElement;

    init() {
        this._initializeAmazonScript();
        this._initializeEventHandler();
    }

    /**
     * Returns the current instance of the Amazon Pay sdk library.
     */
    getAmazonPayObject() {
        return window.amazon.Pay;
    }

    /**
     * Displays the loading indicator.
     *
     * @param {Boolean} loading
     */
    setLoading(loading = true) {
        this.$emitter.publish('swagAmazonPay_setLoading', {
            loading: loading,
        });

        const loadingIndicatorElement = document.getElementById(this.options.loadingElementId);

        // Hide the Amazon Pay Button while loading to avoid further clicks.
        this.el.hidden = true;

        if (loading) {
            loadingIndicatorElement.hidden = false;
            ElementLoadingIndicatorUtil.create(loadingIndicatorElement);
        } else {
            loadingIndicatorElement.hidden = true;
            ElementLoadingIndicatorUtil.remove(loadingIndicatorElement);
        }
    }

    /**
     * Displays an error element and hides the Amazon Pay button element.
     */
    displayError() {
        this.$emitter.publish('swagAmazonPay_displayError');
        document.getElementById(this.options.errorElementId).hidden = false;

        this.el.hidden = true;
    }

    onInsecureConnectionButtonInteraction() {
        document.querySelectorAll('.swag-amazon-pay-button-container').forEach((element) => {
            element.style.visibility = 'hidden';
        });

        document.querySelectorAll('.swag-amazon-pay-button-error').forEach((element) => {
            if (element.classList.contains('connection-is-insecure')) {
                element.hidden = false;
            }
        });
    }

    /**
     * Initializes the  external amazon script.
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

        this.scriptElement.addEventListener('load', this._onLoadAmazonPayScript.bind(this), false);

        document.head.appendChild(this.scriptElement);
    }

    /**
     * Initializes additional event handlers
     *
     * @private
     */
    _initializeEventHandler() {
        const me = this;

        document.querySelectorAll('.swag-amazon-pay-button-tooltip-overlay').forEach((element) => {
            if (element.classList.contains('connection-is-insecure')) {
                element.addEventListener('click', me.onInsecureConnectionButtonInteraction.bind(me));
            }
        });
    }

    /**
     * Called when the external JavaScript Library was loaded.
     *
     * @private
     */
    _onLoadAmazonPayScript() {

        this.el.id += '-'+Math.random().toString(36).substring(2);

        try {
            this.getAmazonPayObject().renderButton(`#${this.el.id}`, this.options.buttonConfig);
            this.scriptElement.removeEventListener('load', this._onLoadAmazonPayScript);
        } catch (e) {
            console.error(e);
        }
        this.$emitter.publish('swagAmazonPay_amazonPayScriptLoaded');
    }
}
