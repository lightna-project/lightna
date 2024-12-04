const path = require('path');
const deepmerge = require('deepmerge');
const modulesConfig = require('../../generated/magento-os/compiled/building/webpack/webpack.config.js');

let config = deepmerge(modulesConfig, {
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, '../../generated/magento-os/compiled/asset.building/compiled/js'),
    },
    mode: 'production'
});

module.exports = (env, argv) => {
    if (argv.mode === 'development') {
        config.devtool = 'inline-source-map';
        config.watch = true;
    }

    return config;
};
