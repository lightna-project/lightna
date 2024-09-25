const path = require('path');
const webpack = require('webpack'); //to access built-in plugins

const config = {
    entry: [
        '../../code/lightna/extend-js-here/js/AddToCart-extend.js',
        '../../code/lightna/extend-js-here-and-here/js/AddToCart-extend.js',
        '../../code/lightna/magento-os-frontend/js/index.js',
    ],
    output: {
        filename: 'common.js',
        path: path.resolve(__dirname, '../../project/magento-os/pub/static/lightna/compiled/js'),
    },
    mode: 'production',
    plugins: [
        new webpack.NormalModuleReplacementPlugin(/^\.\/common\/AddToCart/, '../../extend-js-here/js/AddToCartExtend')
    ],
};

module.exports = (env, argv) => {
    if (argv.mode === 'development') {
        config.devtool = 'inline-source-map';
        config.watch = true;
    }

    return config;
};
