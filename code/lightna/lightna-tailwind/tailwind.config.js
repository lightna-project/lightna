import { readFileSync } from 'fs';

const allModulesConfig = JSON.parse(readFileSync(__dirname + "/config.json", "utf8"));

module.exports = allModulesConfig;
