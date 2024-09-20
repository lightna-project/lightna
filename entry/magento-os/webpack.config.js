const path = require('path');

const config = {
    entry: '../../code/lightna/magento-os-frontend/js/index.js',
    output: {
        filename: 'common.js',
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
