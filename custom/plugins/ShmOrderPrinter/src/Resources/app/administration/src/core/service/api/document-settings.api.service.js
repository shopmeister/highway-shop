const ApiService = Shopware.Classes.ApiService;
export default class DocumentSettingsApiService extends ApiService {

  constructor(httpClient, loginService, apiEndpoint = '') {
    super(httpClient, loginService, apiEndpoint);
    this.baseUrl = this.httpClient.defaults.baseURL;
  }

  async getSttings() {

    const settings = await fetch(`${this.baseUrl}/shm-kindsgut-documents-setting?${new URLSearchParams({
      ...this.encodeSwagQL({
        filter: [{
          type: "equals",
          field: "setting.type",
          value: "document_setting"
        }]
      })
    }).toString()}`, {
      headers: { Authorization: this.getBasicHeaders().Authorization },
    });
    const documenttypes = await fetch(`${this.baseUrl}/document-type`, { headers: { Authorization: this.getBasicHeaders().Authorization } });
    const channels = await fetch(`${this.baseUrl}/sales-channel`, { headers: { Authorization: this.getBasicHeaders().Authorization } });


    return await Promise.all([settings.json(), documenttypes.json(), channels.json()]);
  }


  async saveSettings(data, settingsId) {
    let url = settingsId == "" ? `shm-kindsgut-documents-setting` : `shm-kindsgut-documents-setting/${settingsId}`
    let params = {
      setting: {
        type: 'document_setting',
        data,
      }
    }
    const res = await fetch(`${this.baseUrl}/${url}`, {
      method: settingsId == "" ? "POST" : "PATCH",
      body: JSON.stringify(params),
      headers: {
        Authorization: this.getBasicHeaders().Authorization,
        "Content-Type": "application/json; charset=utf-8"
      }
    });

    return res.status;
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

}