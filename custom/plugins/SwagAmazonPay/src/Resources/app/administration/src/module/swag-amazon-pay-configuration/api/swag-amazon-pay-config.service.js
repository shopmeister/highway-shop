const { Application } = Shopware;
const ApiService = Shopware.Classes.ApiService;

class SwagAmazonPayConfigService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'swag_amazon_pay_configuration') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * @return {Promise<AxiosResponse<T>>}
     */
    getIpnUrl() {
        const apiRoute = `_action/${this.getApiBasePath()}/get-ipn-url`;

        return this.httpClient
            .post(
                apiRoute,
                null,
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * @return {Promise<AxiosResponse<T>>}
     */
    validateCredentials(credentials) {
        const apiRoute = `_action/${this.getApiBasePath()}/inspect-connection`;

        return this.httpClient
            .post(
                apiRoute,
                credentials,
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * @return {Promise<AxiosResponse<T>>}
     */
    generateRsaKeys() {
        const apiRoute = `_action/${this.getApiBasePath()}/generate-keypair`;

        return this.httpClient
            .post(
                apiRoute,
                null,
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * @param {string} content
     * @param {string} merchantId
     */
    sendMail(content, merchantId) {
        const apiRoute = `_action/${this.getApiBasePath()}/send-activation-mail`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    content: content,
                    merchantId: merchantId,
                },
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * @return {Promise<AxiosResponse<T>>}
     */
    exportConfig() {
        const apiRoute = `_action/${this.getApiBasePath()}/export-config`;

        return this.httpClient
            .post(
                apiRoute,
                null,
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * @param {File} file
     * @return {Promise<AxiosResponse<T>>}
     */
    importConfig(file, ignoreVersionMismatch = false) {
        const apiRoute = `_action/${this.getApiBasePath()}/import-config/${ignoreVersionMismatch}`;

        return this.httpClient
            .post(
                apiRoute,
                file,
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    getLogFiles() {
        const apiRoute = `_action/${this.getApiBasePath()}/log-files`;

        return this.httpClient
            .get(
                apiRoute,
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    generateLogArchive(files) {
        const apiRoute = `_action/${this.getApiBasePath()}/generate-log-archive`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    files: files,
                },
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    downloadLogArchive(downloadPath) {
        const url = `/_action/swag_amazon_pay_configuration/download-log-archive?path=${downloadPath}`;

        return this.httpClient
            .get(
                url,
                {
                    responseType: 'blob',
                    headers: this.getBasicHeaders(),
                },
            ).then((response) => {
                return response.data;
            });
    }

    salesChannelDefault(salesChannelId = null) {
        const apiRoute = `_action/${this.getApiBasePath()}/saleschannel-default`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    salesChannelId,
                },
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

Application.addServiceProvider('SwagAmazonPayConfigService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwagAmazonPayConfigService(initContainer.httpClient, container.loginService);
});
