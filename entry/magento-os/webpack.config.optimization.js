module.exports = {
    splitChunks: {
        chunks: (chunk) => chunk.name !== 'lane',
        minSize: 0,
    },
}
