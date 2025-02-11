const fs = require('fs');

/**
 * Temporary `resolve` fix
 */
function postCssImportResolve(id, defaultResolve) {
    try {
        return defaultResolve.resolve(id);
    } catch (e) {
        if (id.startsWith('~/')) {
            const basedir = process.cwd() + '/';
            const filePath = id.slice(2);
            let checklist = [
                basedir + filePath,
                basedir + filePath + '.css',
                basedir + 'node_modules/' + filePath,
                basedir + 'node_modules/' + filePath + '.css',
            ];

            let resolvedId;
            checklist.forEach((file) => {
                fs.existsSync(file) && (resolvedId = file)
            });

            if (resolvedId) {
                return resolvedId;
            } else {
                throw e;
            }
        }

        throw e;
    }
}

module.exports = {
    plugins: [
        require('postcss-import')({
            resolve: postCssImportResolve,
        }),
        require('tailwindcss'),
        require('autoprefixer'),
    ],
}
