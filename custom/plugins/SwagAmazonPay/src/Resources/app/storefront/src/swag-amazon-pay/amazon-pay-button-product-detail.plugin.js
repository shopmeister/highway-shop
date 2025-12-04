import Plugin from 'src/plugin-system/plugin.class';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import HttpClient from 'src/service/http-client.service';
import AmazonPayFormUtil from '../utils/amazon-pay-form-util';

export default class AmazonPayButtonProductDetailPlugin extends Plugin {
    static options = {
        addLineItemUrl: '',
        addLineItemToken: '',
    };

    static buttonPlugin;

    static httpClient;

    init() {
        this.httpClient = new HttpClient();

        this.buttonPlugin = window.PluginManager.getPluginInstances('AmazonPayButton')[0];
        this.buttonPlugin.beforeInitCheckout = this._getAddProductPromise.bind(this);
    }

    /**
     * @return {Promise}
     * @private
     */
    _getAddProductPromise() {
        this.$emitter.publish('swagAmazonPayProductDetail_beforeAddProductToCart');

        const formattedLineItems = this._formatLineItems();
        formattedLineItems._csrf_token = this.options.addLineItemToken;

        return new Promise(resolve => {
            this.httpClient.post(this.options.addLineItemUrl, JSON.stringify(formattedLineItems), () => {
                resolve();
                this.$emitter.publish('swagAmazonPayProductDetail_afterAddProductToCart');
            });
        });
    }

    /**
     * Takes the current product detail form and prepares the payload for an "add-line-item"-request.
     *
     * @return {Object}
     * @private
     */
    _formatLineItems() {
        const formData = AmazonPayFormUtil.formToJson(this.el.closest('form'));

        const formattedLineItems = {};
        Object.keys(formData).forEach(key => {
            const matches = key.match(/lineItems\[(.+)]\[(.+)]/);

            if (key !== 'redirectTo' && matches && matches.length === 3) {
                if (!formattedLineItems[matches[1]]) {
                    formattedLineItems[matches[1]] = {
                        [matches[2]]: formData[matches[0]],
                    };
                } else {
                    const lineItem = formattedLineItems[matches[1]];

                    lineItem[matches[2]] = formData[matches[0]];
                }
            }
        });

        return {
            lineItems: formattedLineItems,
        };
    }
}
