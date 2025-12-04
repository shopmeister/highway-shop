import ShopmasterZalandoConnectorApiSalesChannelService
    from '../core/service/api/sm-zalando-connection-api-sales-channel.service';

import ShopmasterZalandoConnectorSettingsFromService
    from '../core/service/api/sm-zalando-connection-settings-form.service';

import ShopmasterZalandoConnectorApiOrderService
    from '../core/service/api/sm-zalando-connection-api-order.service'

const {Application} = Shopware;

const initContainer = Application.getContainer('init');

Application.addServiceProvider(
    'ShopmasterZalandoConnectorApiSalesChannelService',
    (container) => new ShopmasterZalandoConnectorApiSalesChannelService(initContainer.httpClient, container.loginService),
);

Application.addServiceProvider(
    'ShopmasterZalandoConnectorSettingsFromService',
    (container) => new ShopmasterZalandoConnectorSettingsFromService(initContainer.httpClient, container.loginService),
);

Application.addServiceProvider(
    'ShopmasterZalandoConnectorApiOrderService',
    (container) => new ShopmasterZalandoConnectorApiOrderService(initContainer.httpClient, container.loginService),
);