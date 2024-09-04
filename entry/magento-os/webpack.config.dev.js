const path = require('path');

module.exports = {
    entry: '../../code/lightna/magento-os-frontend/js/index.js',
    output: {
        filename: 'common.js',
        path: path.resolve(__dirname, '../../project/magento-os/pub/static/lightna/compiled/js'),
    },
    mode: 'development',
    watch: true,
    devtool: 'inline-source-map',
};
