const ApiService = Shopware.Classes.ApiService;
import PDFMerger from 'pdf-merger-js';

export default class PrintDocumentApiService extends ApiService {

  constructor(httpClient, loginService, apiEndpoint = '') {
    super(httpClient, loginService, apiEndpoint);
    this.basicConfig = {
      timeout: 30000
    };

    this.shippingDocumentType = [
      {
        id: "dhl_shipping_label",
        name: "Shipping Label",
        technicalName: "dhl_shipping_label",
      },
      {
        id: "dhl_return_label",
        name: "Return Label",
        technicalName: "dhl_return_label",
      }
    ]
    this.sortedDocumentType = ["invoice", "delivery_note", "shm_return_note", "shm_return_flyer", "shm_empty_page"];
  }

  /**
   * Stelle sicher, dass die HTTP-Client Authentication korrekt gesetzt ist
   */
  async ensureAuthentication() {
    try {
      // Token refresh über Shopware's LoginService
      const token = await this.loginService.refreshToken();

      // Authorization-Header für httpClient setzen
      this.httpClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      return token;
    } catch (error) {
      console.error('Authentication failed:', error);
      throw error;
    }
  }
  async fetchDocSettings() {
    // Authenticate für Settings-API
    await this.ensureAuthentication();

    const { data } = await this.httpClient.get('/shm-kindsgut-documents-setting', {
      params: this.encodeSwagQL({
        includes: {
          shm_kindsgut_documents_setting: ["id", "setting"],
        },
      }),
    });
    const documentSettingData = data.data.find(({ setting }) => setting?.type === "document_setting").setting?.data ?? [];
    const internationalSettingData = data.data.find(({ setting }) => setting?.type === "international_setting").setting?.data ?? [];
    return { documentSettingData, internationalSettingData }
  }
  async fetchCountry(value) {
    await this.ensureAuthentication();

    const { data } = await this.httpClient.post(`/search/country`, {
      page: 1,
      filter: [
        {
          type: "equals",
          field: "id",
          value,
        },
      ]
    })

    return data.data[0];
  }

  base64ToArrayBuffer(string) {
    const bString = window.atob(string);
    const bLength = bString.length;
    const bytes = new Uint8Array(bLength);
    for (let i = 0; i < bLength; i++) {
      let ascii = bString.charCodeAt(i);
      bytes[i] = ascii;
    }
    return new Blob([bytes], { type: "application/pdf" });
  };
  async fetchDocuments(params) {
    // Stelle sicher, dass Authorization-Header gesetzt sind
    await this.ensureAuthentication();

    const orders = [...Object.values(params)];
    const { documentSettingData, internationalSettingData } = await this.fetchDocSettings();
    const senderInfoForShipment = await this.fetchPickwareShippingBundlePrefix();
    let anyCountryShippingData = []
    let docs = {};
    let docsBlobs = {};
    let returnDocsBlob = {};
    /** genrating docs */
    for await (const order of orders) {
      // Initialisiere Arrays für alle Orders
      docsBlobs[order.id] = [];
      returnDocsBlob[order.id] = [];

      const { iso3 } = await this.fetchCountry(order.deliveries?.[0]?.shippingOrderAddress.countryId);
      const { shippingLabel } = internationalSettingData.find(({ country }) =>
        country === iso3);

      const documentTypes = documentSettingData[order.salesChannelId]?.filter(
        (item) => this.shippingDocumentType.findIndex(typeItem => typeItem.technicalName === item) === -1
      )
        ?.sort((item1, item2) => {
          const index1 = this.sortedDocumentType.findIndex(type => type === item1);
          const index2 = this.sortedDocumentType.findIndex(type => type === item2);
          return (index1 === -1 ? 20 : index1) - (index2 === -1 ? 20 : index2);
        }) ?? [];

      // Debug-Logging für Kaufland-Probleme
      if (!documentSettingData[order.salesChannelId]) {
        console.warn(`No document settings found for sales channel: ${order.salesChannelId}`);
      } else {
        console.log(`Document types for sales channel ${order.salesChannelId}:`, documentTypes);
      }
      if (documentTypes.length !== 0)
        docs[order.id] = await this.createDocument(order.id, documentTypes);

      /**seven sender data */
      if (['SevenSenders Out', 'SevenSenders Return'].includes(shippingLabel)) {
        const { data } = await this.httpClient.get('shm-seven-senders/get-label-url', {
          params: {
            orderNumber: order.orderNumber
          },
        });
        anyCountryShippingData = data.map(({ blob, url }) => {
          let labal = '';

          if (url.includes('return')) labal = 'return';

          if (url.includes('outbound')) labal = 'shipping';

          return {
            blob: this.base64ToArrayBuffer(blob),
            labal,
          }
        });
      }

      /**shiping documents  */
      const shippingDocTypes = documentSettingData[order.salesChannelId] ?? [];
      if (shippingDocTypes.includes(this.shippingDocumentType[0].technicalName)
        || shippingDocTypes.includes(this.shippingDocumentType[1].technicalName)) {
        const { returnDocumentIds, shippingDocumentIds, singleReturnLable } = await this.shipingDocument(internationalSettingData, order, senderInfoForShipment, shippingDocTypes, iso3);
        let blobs = [];
        let returnblobs = [];
        let returnLableData = [], shippingData = [];
        if (returnDocumentIds.length > 0 || shippingDocumentIds.length > 0) {
          const shippingLabelDocument = await this.fetchPickwareDocs(order.id, shippingDocumentIds.slice(-1));
          returnLableData = shippingLabelDocument.filter(doc => doc.documentTypeTechnicalName === 'return_label');
          shippingData.push(shippingLabelDocument[0])

        }
        /** return lable doc check */
        if (singleReturnLable.length > 0 && shippingDocTypes.includes('dhl_return_label')) {
          singleReturnLable.map(async (shipId) => {
            const shippingLabelDocument = await this.fetchPickwareDocs(order.id, shipId);
            let returnData = shippingLabelDocument.filter(doc => doc.documentTypeTechnicalName === 'return_label');
            returnData.map(id => {
              returnLableData.push(id)
            })

          });
        }
        /** document blobs for docs  */
        for (const key in docs[order.id]) {
          const { id, deepLinkCode } = docs[order.id][key];
          blobs.push(await this.getBlob(id, deepLinkCode));
        }
        /** append blob docs if fetched */
        if (returnLableData.length > 0) {
          for (const { id, deepLinkCode } of returnLableData)
            blobs.push(await this.getShipBlob(id, deepLinkCode));
        }
        if (shippingData.length > 0) {
          for (const { id, deepLinkCode } of shippingData)
            returnblobs.push(await this.getShipBlob(id, deepLinkCode))
        }
        /** seven senders docs */
        if (anyCountryShippingData.length > 0) {
          for (const { labal, blob } of anyCountryShippingData) {
            if (labal === 'shipping' && documentTypes.includes('seven_sender_lables_outbond')) returnblobs.push(blob);
            if (labal === 'return' && documentTypes.includes('seven_sender_lables_return'))
              blobs.push(blob);
          }

        }
        docsBlobs[order.id] = blobs || [];
        returnDocsBlob[order.id] = returnblobs || [];
      } else {
        // Wenn keine Shipping-Dokumente, sammle nur normale Dokumente
        let blobs = [];
        for (const key in docs[order.id] || {}) {
          const { id, deepLinkCode } = docs[order.id][key];
          blobs.push(await this.getBlob(id, deepLinkCode));
        }
        docsBlobs[order.id] = blobs || [];
        returnDocsBlob[order.id] = [];
      }
    }

    const shipMerger = new PDFMerger;
    const docMerger = new PDFMerger;
    /** merging docs in single PDF */
    let printShipDocs = false;
    let hasDocBlobs = false;

    for await (const { id } of orders) {
      // Sichere Array-Überprüfung mit Fallback
      const orderDocBlobs = docsBlobs[id] || [];
      const orderReturnBlobs = returnDocsBlob[id] || [];

      if (orderDocBlobs.length > 0) {
        hasDocBlobs = true;
        for await (const blob of orderDocBlobs) {
          if (blob) await docMerger.add(blob);
        }
      }
      if (orderReturnBlobs.length > 0) {
        printShipDocs = true;
        for await (const blob of orderReturnBlobs) {
          if (blob) await shipMerger.add(blob);
        }
      }
    }

    // Prüfe ob überhaupt Dokumente vorhanden sind
    if (!hasDocBlobs && !printShipDocs) {
      console.error('No documents found for the selected orders. Check sales channel configuration.');
      throw new Error('No documents found for the selected orders. Please check the document settings for this sales channel.');
    }

    // Sichere PDF-Blob-Erstellung mit Fehlerbehandlung - nur wenn Dokumente vorhanden
    if (hasDocBlobs) {
      try {
        const docBlob = await docMerger.saveAsBlob();
        if (docBlob && docBlob.size > 0) {
          const docUrl = URL.createObjectURL(docBlob);
          if (docUrl) {
            window.open(docUrl);
          } else {
            console.error('Failed to create document URL - URL.createObjectURL returned null');
          }
        } else {
          console.error('Document blob is empty or invalid');
        }
      } catch (error) {
        console.error('Error creating document blob:', error);
      }
    }

    if (printShipDocs) {
      try {
        const shipBlob = await shipMerger.saveAsBlob();
        if (shipBlob && shipBlob.size > 0) {
          const shipUrl = URL.createObjectURL(shipBlob);
          if (shipUrl) {
            window.open(shipUrl);
          } else {
            console.error('Failed to create shipping URL - URL.createObjectURL returned null');
          }
        } else {
          console.error('Shipping blob is empty or invalid');
        }
      } catch (error) {
        console.error('Error creating shipping blob:', error);
      }
    }

  }

  async getShipBlob(id, deepLinkCode) {
    const { data } = await this.httpClient.get(`_action/pickware-document/${id}/contents`, {
      params: {
        deepLinkCode
      },
      responseType: 'blob'
    });
    return data;
  }

  async shipingDocument(internationalSettingData, order, senderInfoForShipment, documentType, iso3) {

    const { shippingLabel, returnLabel, rules } = internationalSettingData.find(settingItem =>
      settingItem.country === iso3
    );



    if (!!shippingLabel || !!returnLabel) {
      const pickwareShippingShipments = await this.pickwareShippingShipments(order.id);
      const shipmentConfigData = await this.fetchShipmentConfigData();
      const shippingDocumentIds = [], returnDocumentIds = [], failedShippingLabel = [], singleReturnLable = [];
      const shipmentBlueprint = this.getShipmentBlueprint(order, 'PickwareShippingBundle.common', order.deliveries?.[0]?.shippingOrderAddress, senderInfoForShipment);

      const weightCompare = order?.lineItems?.[0]?.product?.weight < rules?.[0]?.compareValue;

      if (
        !!shippingLabel &&
        shippingLabel !== "SevenSenders Out" &&
        !!documentType.includes(this.shippingDocumentType[0].technicalName)
      ) {
        if (weightCompare) {
          try {
            const documents = await this.createDocument(order.id, {
              isReturnShipment: false,
              shipmentData: {
                shipmentBlueprint: {
                  ...shipmentBlueprint,
                  shipmentConfig: {
                    ...shipmentConfigData,
                    product: "V62WP",
                  }
                },
                "orderId": selectedOrder?.id
              },
            });
          } catch (error) {
            failedShippingLabel.push({
              type: shippingDocumentType[0].name,
              orderId: selectedOrder.orderNumber,
              customer: selectedOrder?.salesChannel?.name,
              message: error?.response?.data?.errors?.[0]?.detail ?? error?.message ?? error
            });
          }

        } else
          shippingDocumentIds.push(
            ...((pickwareShippingShipments ?? [])
              .filter(shippingItem => !shippingItem.isReturnShipment)
              .map(shippingItem => shippingItem.id))
          );
        if (documentType.includes('single_dhl_return_label')) {
          let includedIds = (pickwareShippingShipments ?? []).map(shippingItem => shippingItem.id)
          includedIds.map(id => {
            if (!(shippingDocumentIds.includes(id)))
              singleReturnLable.push(id)
          })
        }
      }

      if (!!returnLabel && returnLabel !== "SevenSenders Return" && !!documentType.includes(this.shippingDocumentType[1].technicalName)) {
        if (returnLabel === "V62WP" || weightCompare) {
          try {
            const returnShipmentForOrderIds = await this.createDocument(order.id, {
              isReturnShipment: true,
              shipmentData: {
                shipmentBlueprint: {
                  ...shipmentBlueprint,
                  shipmentConfig: {
                    ...shipmentConfigData,
                    product: returnLabelName,
                  }
                },
                "orderId": order.id
              },
            })
            returnDocumentIds.push(returnShipmentForOrderIds[0]);
          }
          catch (error) {
            failedShippingLabel.push({
              type: shippingDocumentType[1].name,
              orderId: selectedOrder.orderNumber,
              customer: selectedOrder?.salesChannel?.name,
              message: error?.response?.data?.errors?.[0]?.detail ?? error?.message ?? error
            });
          }
        } else {
          returnDocumentIds.push(
            ...((pickwareShippingShipments ?? [])
              .filter(shippingItem => !shippingItem.isReturnShipment)
              .map(shippingItem => shippingItem.id))
          );

        }
      }
      return { returnDocumentIds, shippingDocumentIds, singleReturnLable };
    }

  }
  async fetchPickwareDocs(orderId, shippingId) {
    const { data } = await this.httpClient.get(`/order/${orderId}/pickwareShippingShipments/${shippingId}/documents`,
      {
        params: {
          ...this.encodeSwagQL({
            includes: {
              "pickware_document": ["id", "deepLinkCode", "documentTypeTechnicalName"]
            }
          }),
        }
      }
    )
    return data.data;
  }

  async fetchShipmentConfigData() {
    const { data } = await this.httpClient.post(`/search/pickware-shipping-carrier`, {
      page: 1,
      filter: [
        {
          type: "equals",
          field: "technicalName",
          value: "dhl",
        },
      ],
      "total-count-mode": 1,
    })
    return data?.data[0]?.shipmentConfigDefaultValues;
  }

  async pickwareShippingShipments(orderId) {
    const { data } = await this.httpClient.get(`/order/${orderId}/pickwareShippingShipments`, {
      params: {
        ...this.encodeSwagQL({
          includes: {
            "pickware_shipping_shipment": ["id", "isReturnShipment"]
          }
        }),
      }
    });
    return data.data;
  }

  filterCreateDocs(documentTypes) {
    return documentTypes.filter(doc => !(['single_dhl_return_label', 'seven_sender_lables_return', 'seven_sender_lables_outbond', 'seven_sender_lables'].includes(doc)));
  }

  async createDocument(orderId, documentTypes) {
    let docs = this.filterCreateDocs(documentTypes);
    const existingDocument = await this.httpClient.post('search/document', {
      associations: {
        documentType: {}
      },
      filter: [
        {
          type: "multi",
          operator: "and",
          queries: [
            {
              type: "equals",
              field: "orderId",
              value: orderId,
            },
            {
              type: "equalsAny",
              field: "documentType.technicalName",
              value: docs
            }
          ]
        }
      ],
      includes: {
        document: [
          "id",
          "documentType",
          "deepLinkCode"
        ],
        document_type: [
          "name",
          "technicalName"
        ]
      }
    });

    let documentObject = {};

    await Promise.all(docs?.filter(document => !!document).map(
      async (document) => {
        const existItem = existingDocument?.data?.data?.find((item) =>
          item?.documentType?.technicalName === document
        );
        if (!existItem) {
          try {
            const create = this.httpClient.post(`/_action/order/document/${document}/create`, [{ orderId }]);
            documentObject = {
              ...documentObject,
              [document]: {
                create: true,
                id: create.data.data?.[0]?.documentId,
                deepLinkCode: create.data.data?.[0]?.documentDeepLink,
              }
            };
          } catch (error) {
            documentObject = {
              ...documentObject,
              [document]: {
                success: false,
                error: error?.response?.data?.errors?.[0]?.detail ?? error?.message ?? error
              }
            };
          }
        } else
          documentObject = {
            ...documentObject,
            [document]: {
              success: true,
              id: existItem.id,
              deepLinkCode: existItem.deepLinkCode,
            }
          };
      }));

    return documentObject;
  }

  async getBlob(id, deeplink) {
    const { data } = await this.httpClient.get(`_action/document/${id}/${deeplink}`, {
      responseType: "blob"
    });
    return data;
  }

  recursiveEncodeSwagQL(query, keyString) {
    if (typeof query !== "object") {
      return { [keyString]: query };
    }

    return Object.keys(query).reduce(
      (prev, key) => ({
        ...prev,
        ...this.recursiveEncodeSwagQL(query[key], `${keyString}[${key}]`),
      }),
      {}
    );
  }

  encodeSwagQL(query) {
    return Object.keys(query).reduce(
      (prev, key) => ({ ...prev, ...this.recursiveEncodeSwagQL(query[key], key) }),
      {}
    );
  }

  async fetchPickwareShippingBundlePrefix() {
    const { data } = await this.httpClient.get('/_action/system-config', {
      params: {
        domain: "PickwareShippingBundle.common",
      },
    });

    return data;
  }

  async fetchLineItems(orderId) {
    const lineItems = await this.httpClient.get(`order/${orderId}/lineItems`, {
      params: this.encodeSwagQL({
        includes: {
          product: []
        },
      })
    });
    const { id, price, quantity } = lineItems.data.data[0];
    const product = await this.getProductLine(id);
    return { id, price, quantity, product };
  }

  async getProductLine(lineItemId) {
    const productLineItems = await this.httpClient.get(`/order-line-item/${lineItemId}/product`);
    const { price, productNumber, weight, width, height, length, name, updatedAt } = productLineItems.data.data[0];
    return { price, productNumber, weight, width, height, length, name, updatedAt };
  }

  async getShipmentBlueprint(order, pickwareShippingBundlePrefix, receiver, senderInfoForShipment) {
    const { product, price, quantity } = await this.fetchLineItems(order.id);
    return {
      "senderAddress": {
        "firstName": "",
        "lastName": "",
        "company": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressCompany`],
        "department": "",
        "addressAddition": "",
        "street": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressStreet`],
        "houseNumber": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressHouseNumber`],
        "city": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressCity`],
        "zipCode": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressZipCode`],
        "countryIso": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressCountryIso`],
        "stateIso": "23",
        "phone": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressPhone`],
        "email": senderInfoForShipment[`${pickwareShippingBundlePrefix}.senderAddressEmail`],
        "customsReference": ""
      },
      "receiverAddress": {
        "firstName": receiver?.firstName ?? "",
        "lastName": receiver?.lastName ?? "",
        "company": receiver?.company ?? "",
        "department": receiver?.department ?? "",
        "addressAddition": receiver?.additionalAddressLine2 ?? "",
        "street": receiver?.street ?? "",
        "houseNumber": "",
        "city": receiver?.city ?? "",
        "zipCode": receiver?.zipcode ?? "",
        "countryIso": "DE",
        "stateIso": "",
        "phone": receiver?.phoneNumber ?? "",
        "email": "asdfas@asfsdafdw.de",
        "customsReference": ""
      },
      "parcels": [
        {
          "items": [
            {
              "name": product.name || "",
              "unitWeight": {
                "value": product.weight || 0,
                "unit": "kg"
              },
              "unitDimensions": {
                "width": {
                  "value": product.width || 0,
                  "unit": "m"
                },
                "height": {
                  "value": product.height || 0,
                  "unit": "m"
                },
                "length": {
                  "value": product.length || 0,
                  "unit": "m"
                }
              },
              "quantity": quantity || 0,
              "customsInformation": {
                "description": product.name || "",
                "customsValue": {
                  "value": product.price[0].net ?? 0,
                  "currency": {
                    "isoCode": order?.currency?.shortName ?? "EUR"
                  }
                },
                "tariffNumber": null,
                "countryIsoOfOrigin": null
              }
            }
          ],
          "dimensions": {
            "width": {
              "value": 0,
              "unit": "m"
            },
            "height": {
              "value": 0,
              "unit": "m"
            },
            "length": {
              "value": 0,
              "unit": "m"
            }
          },
          "fillerWeight": {
            "value": 0,
            "unit": "kg"
          },
          "weightOverwrite": null,
          "customerReference": "10018",
          "customsInformation": {
            "typeOfShipment": "sale-of-goods",
            "officeOfOrigin": "",
            "explanationIfTypeOfShipmentIsOther": null,
            "invoiceNumbers": [
              "1053"
            ],
            "invoiceNumber": product.productNumber || "",
            "invoiceDate": product.updatedAt ?? "",
            "permitNumbers": [],
            "certificateNumbers": [],
            "fees": {
              "shipping-costs": {
                "value": 0,
                "currency": {
                  "isoCode": order?.currency?.shortName ?? "EUR"
                }
              }
            },
            "comment": ""
          }
        }
      ],
      "carrierTechnicalName": "dhl",
      "customerReference": "10018"
    };
  }
}