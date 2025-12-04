const { join, resolve } = require('path');
const webpack = require('webpack');
const fs = require('fs');

module.exports = () => {
    // Defensive Suche nach node_modules: Shopware, Plugin, oder verfügbare Polyfills
    const shopwareNodeModules = resolve(__dirname, '../../../../../vendor/shopware/administration/Resources/app/administration/node_modules');
    const pluginNodeModules = resolve(__dirname, '../node_modules');

    // Hilfsfunktion um verfügbare Polyfills zu finden
    const findPolyfill = (name) => {
        const shopwarePath = resolve(join(shopwareNodeModules, name));
        const pluginPath = resolve(join(pluginNodeModules, name));

        if (fs.existsSync(shopwarePath)) return shopwarePath;
        if (fs.existsSync(pluginPath)) return pluginPath;

        // Als letzter Ausweg: versuche require.resolve
        try {
            return require.resolve(name);
        } catch {
            return false;
        }
    };

    const processPolyfill = findPolyfill('process/browser');
    const bufferPolyfill = findPolyfill('buffer');

    return {
        resolve: {
            alias: {
                // Versuche lokale node_modules zuerst, dann Shopware's
                'pdf-merger-js': findPolyfill('pdf-merger-js') || 'pdf-merger-js',
                'notiflix': findPolyfill('notiflix') || 'notiflix',
            },
            fallback: {
                // Nur Polyfills hinzufügen wenn sie verfügbar sind
                ...(processPolyfill ? { "process": processPolyfill } : {}),
                ...(bufferPolyfill ? { "buffer": bufferPolyfill } : {}),
                "stream": false,
                "crypto": false,
                "fs": false,
                "path": false,
                "os": false,
                "http": false,
                "https": false,
                "url": false,
                "util": false
            }
        },
        plugins: [
            // Nur ProvidePlugin wenn Polyfills verfügbar sind
            ...(processPolyfill || bufferPolyfill ? [
                new webpack.ProvidePlugin({
                    ...(processPolyfill ? { process: ['process/browser'] } : {}),
                    ...(bufferPolyfill ? { Buffer: ['buffer', 'Buffer'] } : {}),
                })
            ] : []),
        ],
        module: {
            rules: [
                {
                    test: /\.js$/,
                    loader: 'babel-loader',
                    options: {
                        compact: true,
                        cacheDirectory: true,
                        presets: [
                            '@babel/preset-env'
                        ]
                    }
                }
            ]
        }
    };
};