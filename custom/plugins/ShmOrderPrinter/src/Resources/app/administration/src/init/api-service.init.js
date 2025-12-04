import PrintDocumentApiService from '../core/service/api/print-document.api.service';
import DocumentSettingsApiService from '../core/service/api/document-settings.api.service'

const { Application } = Shopware;

const initContainer = Application.getContainer('init');

Application.addServiceProvider('PrintDocumentApiService', container => (new PrintDocumentApiService(initContainer.httpClient, container.loginService)))

Application.addServiceProvider('DocumentSettingsApiService',container => (new DocumentSettingsApiService(initContainer.httpClient, container.loginService)));

