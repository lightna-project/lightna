const path = require('path');
const deepmerge = require('deepmerge');
const optimizationConfig = require('./webpack.config.optimization');

const editionDir = process.env.LIGHTNA_EDITION_DIR;
const modulesConfig = require('./' + editionDir + '/building/webpack/webpack.config.js');

const config = deepmerge(modulesConfig, {
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, editionDir + '/asset.building/build/js'),
    },
    optimization: optimizationConfig,
    mode: 'production'
});

for (const key in config.entry) {
    if (key !== 'common' && key !== 'lane') {
        config.entry[key] = {
            import: config.entry[key],
            dependOn: 'common'
        };
    }
}
module.exports = config;
