const ApiService = Shopware.Classes.ApiService;

class ShopmasterZalandoConnectorSettingsFromService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'shopmaster_zalando_connector') {
        super(httpClient, loginService, apiEndpoint);
    }

    getFormById(id) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(`_action/${this.getApiBasePath()}/settings/form/${id}`,
                {headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    getFormBySalesChannelAndType(salesChannel, type) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(`_action/${this.getApiBasePath()}/settings/form/${salesChannel}/${type}`,
                {headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    saveForm(salesChannel, type, data) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .post(`_action/${this.getApiBasePath()}/settings/form/${salesChannel}/${type}`, data,
                {headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    getLogisticCenters() {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(`_action/${this.getApiBasePath()}/settings/general/z_logistic_centers`,
                {headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default ShopmasterZalandoConnectorSettingsFromService;
