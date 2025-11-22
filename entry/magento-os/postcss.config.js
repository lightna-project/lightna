const fs = require('fs');

/**
 * Temporary `resolve` fix
 */
function postCssImportResolve(id) {
    const TAILWIND_IMPORTS = [
        'tailwindcss/base',
        'tailwindcss/components',
        'tailwindcss/utilities',
    ];
    TAILWIND_IMPORTS.includes(id) && (id = id.replace('tailwindcss/', '~/tailwindcss/'));

    if (!id.startsWith('~/')) {
        throw new Error('Can\'t handle import path "' + id + '", it should start with "~/".');
    }

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
        throw new Error('Import "' + id + '" not found.');
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
