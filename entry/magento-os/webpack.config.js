const path = require('path');

const config = {
    entry: {
        common: '../../code/lightna/magento-os-frontend/js/index.js',
        semi: '../../code/lightna/magento-os-frontend/js/index-semi.js',
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, '../../project/magento-os/pub/static/lightna/compiled/js'),
    },
    mode: 'production'
};

module.exports = (env, argv) => {
    if (argv.mode === 'development') {
        config.devtool = 'inline-source-map';
        config.watch = true;
    }

    return config;
};
