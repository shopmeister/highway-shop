import HttpClient from 'src/service/http-client.service';

export default class NetzpShopManager6 extends window.PluginBaseClass
{
    static options = {
        basePath: ""
    };

    init()
    {
        var client = new HttpClient();

        let basePath = this.options.basePath;
        if(basePath.slice(-1) != '/')
        {
            basePath = basePath + '/';
        }

        client.get(basePath + 'netzp/shopmanager/statistics', response => { });
    }
}
