const ApiService = Shopware.Classes.ApiService;

class ShopmasterZalandoConnectorApiSalesChannelService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'shopmaster_zalando_connector') {
        super(httpClient, loginService, apiEndpoint);
    }

    getSalesChannels() {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(`_action/${this.getApiBasePath()}/settings/general/z_active_sales_channels`,
                {headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default ShopmasterZalandoConnectorApiSalesChannelService;
