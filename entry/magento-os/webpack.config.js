const path = require('path');
const deepmerge = require('deepmerge');
const modulesConfig = require('../../generated/magento-os/compiled/build/webpack/webpack.config.js');

let config = deepmerge(modulesConfig, {
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, '../../project/magento-os/pub/static/lightna/compiled/js'),
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
