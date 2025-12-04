import Plugin from 'src/plugin-system/plugin.class';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import HttpClient from 'src/service/http-client.service';

export default class AmazonPayButtonPlugin extends Plugin {
    static options = {
        libraryUrl: 'https://static-eu.payments-amazon.com/checkout.js',
        amazonPayPluginVersion: '10.2.4',
        errorElementId: 'swag-amazon-pay-button-error',
        loadingElementId: 'swag-amazon-pay-loading-indicator',
        createCheckoutSessionConfig: {
            payloadJSON: '',
            signature: '',
            publicKeyId: '',
        },
        type: 'default',
        buttonConfig: {
            merchantId: '',
            sandbox: false,
            ledgerCurrency: '',
            checkoutLanguage: '',
            productType: '',
            placement: '',
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

        window.amazonPayOnLoadCallbacks = window.amazonPayOnLoadCallbacks || [];
        window.amazonPayOnLoadCallbacks.push(this.renderButton.bind(this));

        const scriptElementId = 'swag-amazon-pay-js-' + this.options.buttonConfig.merchantId;
        const existingScriptElement = document.getElementById(scriptElementId);
        if (existingScriptElement) {
            if (typeof window.amazon !== 'undefined') {
                this.executeAmazonPayOnLoadCallbacks();
            }
            return;
        }

        this.scriptElement = document.createElement('script');
        this.scriptElement.type = 'text/javascript';
        this.scriptElement.src = this.options.libraryUrl;
        this.scriptElement.id = scriptElementId;

        this.scriptElement.addEventListener('load', this.executeAmazonPayOnLoadCallbacks.bind(this), false);

        document.head.appendChild(this.scriptElement);
    }

    executeAmazonPayOnLoadCallbacks() {
        if (window.amazonPayOnLoadCallbacksExecuting) {
            setTimeout(this.executeAmazonPayOnLoadCallbacks, 500);
        }
        window.amazonPayOnLoadCallbacksExecuting = true;

        if (window.amazonPayOnLoadCallbacks) {
            window.amazonPayOnLoadCallbacks.forEach((callback, index) => {
                try {
                    callback();
                    delete window.amazonPayOnLoadCallbacks[index];
                } catch (e) {
                    console.warn(e);
                }
            });
        }

        window.amazonPayOnLoadCallbacksExecuting = false;
        const scriptElementId = 'swag-amazon-pay-js-' + this.options.buttonConfig.merchantId;
        const scriptElement = document.getElementById(scriptElementId);
        if (scriptElement) {
            scriptElement.removeEventListener('load', this.executeAmazonPayOnLoadCallbacks);
        }
        this.$emitter.publish('swagAmazonPay_amazonPayScriptLoaded');
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


    renderButton() {

        this.el.id += '-'+Math.random().toString(36).substring(2);

        const amazonPayButton = this.getAmazonPayObject().renderButton(`#${this.el.id}`, this.options.buttonConfig);
        this._addAmazonPayButtonOnClickHandler(amazonPayButton);

        if (this.options.type === 'product-box') {
            this.addListingAddCartAction();
        }
    }

    /**
     * Override this function to provide a Promise which should be executed before
     * the Amazon Pay checkout will be initiated.
     *
     * @return {Promise}
     */
    beforeInitCheckout() {
        // This promise does nothing and that's fine, because it's just a simple placeholder for other plugin developers.
        return new Promise((resolve) => {
            resolve();
        });
    }

    initCheckout(button) {
        this.$emitter.publish('swagAmazonPay_amazonPayCheckoutInitiated');

        button.initCheckout({createCheckoutSessionConfig: this.options.createCheckoutSessionConfig});
    }

    _addAmazonPayButtonOnClickHandler(button) {
        button.onClick(() => {
            this.$emitter.publish('swagAmazonPay_amazonPayButtonClicked');
            this.setLoading();
            return this.beforeInitCheckout().then(() => {
                this.initCheckout(button);
                setTimeout(() => {
                    this.setLoading(false);
                }, 3000);
            });
        });
    }

    addListingAddCartAction() {
        this.$emitter.publish('swagAmazonPayButton_beforeListingAddProductToCart', {plugin: this});

        const form = this.el.closest('form');
        if (!form) {
            return;
        }
        const formAction = form.action;
        const formData = new FormData(form);


        this.beforeInitCheckout = () => {
            return new Promise(resolve => {
                (new HttpClient()).post(formAction, formData, () => {
                    resolve();
                    this.$emitter.publish('swagAmazonPayButton_afterListingAddProductToCart', {plugin: this});
                });
            });
        };
    }
}
