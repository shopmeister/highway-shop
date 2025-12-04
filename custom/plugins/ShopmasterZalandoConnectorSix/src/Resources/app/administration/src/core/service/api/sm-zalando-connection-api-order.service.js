const ApiService = Shopware.Classes.ApiService;

class ShopmasterZalandoConnectorApiOrderService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'shopmaster_zalando_connector') {
        super(httpClient, loginService, apiEndpoint);
    }

    getList(data = []) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .post(`_action/${this.getApiBasePath()}/order/list/backend`, data,
                {headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default ShopmasterZalandoConnectorApiOrderService;
