/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

// import ApiService from 'src/core/service/api.service';

const {Application} = Shopware;

class MagnalisterOrderService extends Shopware.Classes.ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'magnalister') {
        super(httpClient, loginService, apiEndpoint);
    }

    fetchOrderData(magnalisterOrderId, userID) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/fetch_order`,
                {
                    magnalister_order_id: magnalisterOrderId,
                    user_id: userID,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

    fetchOrderLogo(magnalisterOrderId, userID) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/fetch_logo`,
                {
                    magnalister_order_id: JSON.stringify(magnalisterOrderId),
                    user_id: userID,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

    storeReturnCarierAndReturnTrakingCode(returnCarrier, returnTrackingNumber, magnalisterOrderId, userID) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/additional_order`,
                {
                    magnalister_return_carrier: returnCarrier,
                    magnalister_return_tracking_code: returnTrackingNumber,
                    magnalister_order_id: magnalisterOrderId,
                    user_id: userID,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

    fetchReturnCarierAndReturnTrakingCode(magnalisterOrderId, userID) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/fetch_additional_order`,
                {
                    magnalister_order_id: magnalisterOrderId,
                    user_id: userID,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

    fetchCarierCodeAndShippingMethod(magnalisterOrderId, userID) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/fetch_amazon_additional_order`,
                {
                    magnalister_order_id: magnalisterOrderId,
                    user_id: userID,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

    storeCarrierCodeAndShippingMethod(CarrierCode, shipMethod, magnalisterOrderId, userID) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/store_amazon_additional_order`,
                {
                    magnalister_carrier_code: CarrierCode,
                    magnalister_shipping_method: shipMethod,
                    magnalister_order_id: magnalisterOrderId,
                    user_id: userID,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

    addSession() {
        return this.httpClient
            .post(
                `${this.getApiBasePath()}/add_session`,
                {},
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }
    deleteSession(keyId) {
        return this.httpClient
            .get(
                `${this.getApiBasePath()}/delete_session/` + keyId
            ).then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }
    resetSession(keyId) {
        return this.httpClient
            .post(
                `${this.getApiBasePath()}/reset_session`,
                {
                    keyId: keyId,
                },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return Shopware.Classes.ApiService.handleResponse(response);
            });
    }

}

Application.addServiceProvider('MagnalisterOrderService', (container) => {
    const initContainer = Application.getContainer('init');

    return new MagnalisterOrderService(initContainer.httpClient, container.loginService);
});
