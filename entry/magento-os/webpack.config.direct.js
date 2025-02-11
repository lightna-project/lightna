const path = require('path');
const deepmerge = require('deepmerge');

const editionDir = process.env.LIGHTNA_EDITION_DIR;
const editionAssetDir = process.env.LIGHTNA_EDITION_ASSET_DIR;
const modulesConfig = require('./' + editionDir + '/build/webpack/webpack.config.js');

const config = deepmerge(modulesConfig, {
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, editionAssetDir + '/build/js'),
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
