module.exports = {
    splitChunks: {
        chunks: 'all',
            minSize: 0,
            cacheGroups: {
            commons: {
                name: 'shared',
                chunks: 'all',
            },
        },
    },
}
