const path = require('path');
const deepmerge = require('deepmerge');
const modulesConfig = require('../../generated/magento-os/compiled/building/webpack/webpack.config.js');

const config = deepmerge(modulesConfig, {
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, '../../generated/magento-os/compiled/asset.building/build/js'),
    },
    mode: 'production'
});

module.exports = config;
