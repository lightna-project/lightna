const path = require('path');
const deepmerge = require('deepmerge');

const editionDir = process.env.LIGHTNA_EDITION_DIR;
const modulesConfig = require('./' + editionDir + '/building/webpack/webpack.config.js');

const config = deepmerge(modulesConfig, {
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, editionDir + '/asset.building/build/js'),
    },
    mode: 'production'
});

module.exports = config;
