import AmazonPayButton from './swag-amazon-pay/amazon-pay-button.plugin';
import AmazonLoginButton from './swag-amazon-pay/amazon-login-button.plugin';
import AmazonPayButtonProductDetail from './swag-amazon-pay/amazon-pay-button-product-detail.plugin';
import AmazonPayConfirmPage from './swag-amazon-pay/amazon-pay-confirm-page.plugin';
import AmazonLoginRegistrationPagePlugin from './swag-amazon-pay/amazon-login-registration-page.plugin';
import AmazonPayPurePaymentMethodPlugin from './swag-amazon-pay/pure-payment-method/amazon-pay-pure-payment-method.plugin';

const PluginManager = window.PluginManager;

PluginManager.register(
    'AmazonPayButton',
    AmazonPayButton,
    '[data-amazon-pay-button]'
);
PluginManager.register(
    'AmazonLoginButton',
    AmazonLoginButton,
    '[data-amazon-login-button]'
);
PluginManager.register(
    'AmazonPayButtonProductDetail',
    AmazonPayButtonProductDetail,
    '[data-amazon-pay-button-product-detail]'
);
PluginManager.register(
    'AmazonPayConfirmPage',
    AmazonPayConfirmPage,
    '[data-amazon-pay-confirm-page]'
);
PluginManager.register(
    'AmazonLoginRegistrationPage',
    AmazonLoginRegistrationPagePlugin,
    '[data-amazon-registration-url]'
);
PluginManager.register(
    'AmazonPayPurePaymentMethodPlugin',
    AmazonPayPurePaymentMethodPlugin,
    '[data-amazon-pay-pure-payment-method="true"]'
);
if (module.hot) {
    module.hot.accept();
}
