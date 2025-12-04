const { Application } = Shopware;
const ApiService = Shopware.Classes.ApiService;

class SwagAmazonPayOrderService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'swag-amazon-pay-order') {
        super(httpClient, loginService, apiEndpoint);
    }

    fetchPaymentDetails(chargePermissionId, refreshData = false) {
        const apiRoute = `_action/${this.getApiBasePath()}/payment-details/${chargePermissionId}`;
        return this.httpClient.get(
            apiRoute,
            {
                params: {refreshData:refreshData?1:0},
                headers: this.getBasicHeaders(),
            },
        )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    chargePayment(chargeId, amount, currencyCode, softDescriptor = '') {
        const apiRoute = `_action/${this.getApiBasePath()}/charge-payment/${chargeId}`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    currencyCode: currencyCode,
                    softDescriptor: softDescriptor,
                    amount: amount,
                },
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    createCharge(chargePermissionId, amount, softDescriptor = '') {
        const apiRoute = `_action/${this.getApiBasePath()}/create-charge/${chargePermissionId}`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    softDescriptor: softDescriptor,
                    amount: amount,
                },
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    cancelPayment(chargePermissionId, closureReason) {
        const apiRoute = `_action/${this.getApiBasePath()}/cancel-payment/${chargePermissionId}`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    closureReason: closureReason,
                },
                {
                    headers: this.getBasicHeaders(),
                },
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    refundPayment(chargeId, amount, currencyCode, softDescriptor = '') {
        const apiRoute = `_action/${this.getApiBasePath()}/refund-payment/${chargeId}`;

        return this.httpClient
            .post(
                apiRoute,
                {
                    currencyCode: currencyCode,
                    softDescriptor: softDescriptor,
                    amount: amount,
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

Application.addServiceProvider('SwagAmazonPayOrderService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwagAmazonPayOrderService(initContainer.httpClient, container.loginService);
});

