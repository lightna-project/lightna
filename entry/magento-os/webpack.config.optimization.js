module.exports = {
    splitChunks: {
        chunks: 'all',
        name: 'shared',
        minSize: 0,
    },
}
// yaml? enable/disable? check for number of entries? autoload shared.js?
