import { animations, keyframes } from './animations';
import { readFileSync } from 'fs';

const merge = require(process.cwd() + '/node_modules/deepmerge');

const config = {
    theme: {
        extend: {
            animation: animations,
            keyframes: keyframes,
        },
    },
};

const allModulesConfig = JSON.parse(readFileSync(__dirname + "/config.json", "utf8"));

module.exports = merge(config, allModulesConfig);
