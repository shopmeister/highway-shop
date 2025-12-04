import './page/document-settings'
const { Module } = Shopware;
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Module.register('document-settings', {
    type: 'plugin',
    name: 'Document Creator',
    color: '#9AA8B5',
    icon: 'regular-cog',
    favicon: 'icon-module-settings.png',

    routes: {
        index: {
            component: 'document-settings',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
            },
        }
    },
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },
    settingsItem: [
        {
            group: 'plugins',
            to: 'document.settings.index',
            favicon: 'icon-module-settings.png',
            label: 'document-settings.index.header',
            icon: 'regular-file-text',
            backgroundEnabled: true,
        }
    ],
});
