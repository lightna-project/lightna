<?php

declare(strict_types=1);

namespace Lightna\Webpack\App\Compiler;

use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Compiler\CompilerA;
use stdClass;

class Webpack extends CompilerA
{
    protected array $modulesConfig;
    protected array $importsIndex = [];
    protected array $extendAliases = [];

    public function make(): void
    {
        $this->build->addValidateConfigOverrides('webpack/config');
        $this->collectModulesConfig();
        $this->indexImports();
        $this->makeExtends();
        $this->makeEntriesJs();
        $this->makeConfigJs();
    }

    protected function collectModulesConfig(): void
    {
        $this->modulesConfig = [];
        foreach ($this->getEnabledModules() as $module) {
            if (!$moduleConfig = $this->getModuleConfig($module['path'])) {
                continue;
            }

            $this->modulesConfig = merge(
                $this->modulesConfig,
                $moduleConfig,
            );
        }

        ArrayDirectives::apply($this->modulesConfig);

        // Save result to validate config overrides
        $this->build->save('webpack/config', $this->modulesConfig);
    }

    protected function getModuleConfig(string $folder): array
    {
        $configFile = $folder . '/webpack.yaml';
        if (!is_file($configFile)) {
            return [];
        }

        return yaml_parse_file($configFile);
    }

    protected function indexImports(): void
    {
        $this->walkFilesInModules(
            'js',
            ['js'],
            $this->indexImport(...),
        );
    }

    protected function indexImport(string $subPath, string $file, string $modulePath, string $moduleName): void
    {
        $subPath = preg_replace('~\.js$~', '', $subPath);
        $this->importsIndex[$moduleName . '/' . $subPath] = $moduleName . '/' . $subPath;
    }

    protected function makeConfigJs(): void
    {
        $this->build->putFile('webpack/webpack.config.js', $this->getConfigJs());
    }

    protected function makeExtends(): void
    {
        foreach ($this->modulesConfig['extend'] ?? [] as $import => $extends) {
            if (!count($extends)) {
                continue;
            }
            $this->build->putFile('webpack/extend/' . $import . '.js', $this->getExtendsJs($import));
            $this->extendAliases[$import] = './extend/' . $import;
        }
    }

    protected function getExtendsJs(string $import): string
    {
        $name = preg_replace('~^.+/~', '', $import);
        $nameOrigin = $name . 'Origin';
        $extends = $this->modulesConfig['extend'][$import];

        $js = "import { $name as $nameOrigin } from " . json_pretty($import . '.js') . ';';
        $extend = array_shift($extends);
        $js .= "\nlet $name = require(" . json_pretty($extend) . ").extend($nameOrigin);";

        foreach ($extends as $extend) {
            $extend = $this->getImport($extend);
            $js .= "\n$name = require(" . json_pretty($extend) . ").extend($name);";
        }
        $js .= "\n\nexport { $name };";

        return $js;
    }

    protected function makeEntriesJs(): void
    {
        $entries = $this->modulesConfig['entry'] ?? [];
        $allImports = [];

        foreach ($entries as $name => $types) {
            $components = $types['component'] ?? [];
            unset($types['component']);

            foreach ($types as $imports) {
                $allImports = merge($allImports, $imports);
            }
            $this->createEntryJs($name, $allImports, $components);
        }
    }

    protected function createEntryJs(string $name, array $imports, array $components): void
    {
        $this->saveEntryJs(
            $name,
            $this->getImportsJs($imports) . $this->getComponentsJs($components),
        );
    }

    protected function getImportsJs(array $imports): string
    {
        $js = '';
        foreach ($imports as $import) {
            $js .= "\n" . 'import ' . json_pretty($this->getImport($import)) . ';';
        }

        return $js;
    }

    protected function getComponentsJs(array $components): string
    {
        $js = '';
        $i = 1;
        foreach ($components as $import) {
            $name = preg_replace('~^.+/~', '', $import);
            $as = 'C' . $i;
            $js .= "\n" . "import { $name as $as } from " . json_pretty($this->getImport($import)) . ';';
            $js .= "\n" . 'new ' . $as . '();';
            $js .= "\n";
            $i++;
        }

        return $js;
    }

    protected function getImport(string $import): string
    {
        if (!isset($this->importsIndex[$import])) {
            throw new \Exception('Webpack compiler: import "' . $import . '" not found');
        }

        return $this->importsIndex[$import];
    }

    protected function saveEntryJs(string $name, string $js): void
    {
        $this->build->putFile('webpack/' . $name . '.js', $js);
    }

    protected function getModuleAlias(string $folder): string
    {
        return implode('/', array_slice(explode('/', rtrim($folder, '/')), -2));
    }

    protected function getConfigJs(): string
    {
        return $this->getConfigJsHead() .
            $this->getConfigJsEntries() .
            $this->getConfigJsAliases() .
            $this->getConfigJsExports();
    }

    protected function getConfigJsHead(): string
    {
        $js = "const path = require('path');\n";
        $js .= "let config = " . json_pretty($this->modulesConfig['webpack'] ?? new stdClass()) . ";\n";
        $js .= "config.entry = config.entry || {};\n";
        $js .= "config.resolve = config.resolve || {};\n";
        $js .= "config.resolve.alias = config.resolve.alias || {};\n\n";

        return $js;
    }

    protected function getConfigJsEntries(): string
    {
        $entries = $this->modulesConfig['entry'] ?? [];
        $js = '';
        foreach ($entries as $name => $entry) {
            $js .= "config.entry[" . json_pretty($name) . "]" .
                " = path.resolve(__dirname, " . json_pretty('./' . $name . '.js') . ");\n";
        }

        return $js;
    }

    protected function getConfigJsAliases(): string
    {
        $js = '';
        foreach ($this->getConfigAliases() as $alias => $path) {
            $js .= "config.resolve.alias[" . json_pretty($alias) . "]" .
                " = path.resolve(__dirname, " . json_pretty($path) . ");\n";
        }

        return $js;
    }

    protected function getConfigAliases(): array
    {
        return merge(
            $this->extendAliases,
            $this->getConfigModuleAliases(),
        );
    }

    protected function getConfigModuleAliases(): array
    {
        $aliases = [];
        $wpbDir = $this->build->getDir() . 'webpack';
        foreach ($this->getEnabledModules() as $module) {
            $folder = LIGHTNA_ENTRY . $module['path'] . '/';
            if (is_dir($abs = $folder . 'js')) {
                $aliases[$this->getModuleAlias($folder)] = './' . getRelativePath($wpbDir, $abs);
            }
        }

        return $aliases;
    }

    protected function getConfigJsExports(): string
    {
        return "\nmodule.exports = config;\n";
    }
}
