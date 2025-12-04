import './acl';
import './page/swag-amazon-pay-settings';
import './component/swag-amazon-pay-configuration-input-key-modal';
import './component/swag-amazon-pay-configuration';
import './component/swag-amazon-pay-configuration-upload-modal';
import './component/swag-amazon-pay-configuration-log-download';
import './component/swag-amazon-pay-insecure-domain-notification';
import './component/swag-amazon-pay-settings-icon';

const {Module} = Shopware;

Module.register('swag-amazon-pay-configuration', {
    type: 'plugin',
    name: 'SwagAmazonPayConfiguration',
    title: 'swag-amazon-pay-configuration.module.title',
    description: 'swag-amazon-pay-configuration.module.description',
    version: '1.0.0',
    targetVersion: '1.0.0',
    routes: {
        config: {
            component: 'swag-amazon-pay-settings',
            path: 'config',
            meta: {
                parentPath: 'sw.extension.my-extensions',
                privilege: 'swag_amazonpay.viewer',
            },
        },
    },
    settingsItem: {
        group: 'plugins',
        to: 'swag.amazon.pay.configuration.config',
        iconComponent: 'swag-amazon-pay-settings-icon',
        backgroundEnabled: true,
        privilege: 'swag_amazonpay.viewer',
    },
});
