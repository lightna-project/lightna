<?php

declare(strict_types=1);

namespace Lightna\Webpack\App\Compiler;

use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Compiler\CompilerA;

class Webpack extends CompilerA
{
    protected array $modulesConfig;
    protected array $relevantModules;
    protected array $importsIndex = [];
    protected array $filesIndex = [];
    protected array $extendAliases = [];
    protected array $wpConfig;

    public function make(): void
    {
        $this->collectModulesConfig();
        $this->indexImports();
        $this->makeWpConfig();
    }

    protected function collectModulesConfig(): void
    {
        $this->modulesConfig = [];
        $this->relevantModules = [];
        foreach ($this->getModules() as $folder) {
            if (!$moduleConfig = $this->getModuleConfig($folder)) {
                continue;
            }
            $this->relevantModules[] = $folder;
            $this->modulesConfig = merge(
                $this->modulesConfig,
                $moduleConfig,
            );
        }

        ArrayDirectives::apply($this->modulesConfig);
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
            function ($subPath, $file, $modulePath, $moduleName, $moduleDir) {
                $subPath = preg_replace('~\.js$~', '', $subPath);
                $this->importsIndex[$moduleName . '/' . $subPath] = $moduleName . '/' . $subPath;
                $this->filesIndex[$moduleName . '/' . $subPath] = $moduleDir . 'js/' . $subPath;
            }
        );
    }

    protected function makeWpConfig(): void
    {
        $this->makeEntriesConfig();
        $this->makeExtends();
        $this->makeEntriesJs();
        $this->makeAliasesConfig();
        $this->saveConfig();
    }

    protected function makeEntriesConfig(): void
    {
        $entries = $this->modulesConfig['entry'] ?? [];
        $this->wpConfig['entry'] = [];
        foreach ($entries as $name => $types) {
            $this->wpConfig['entry'][$name] = $this->compiled->getDir() . 'webpack/' . $name . '.js';
        }
    }

    protected function makeExtends(): void
    {
        foreach ($this->modulesConfig['extend'] ?? [] as $import => $extends) {
            if (!count($extends)) {
                continue;
            }
            $this->compiled->putFile('webpack/extend/' . $import . '.js', $this->getExtendsJs($import));
            $this->extendAliases[$import] = $this->compiled->getDir() . './webpack/extend/' . $import . '.js';
        }
    }

    protected function getExtendsJs(string $import): string
    {
        $name = preg_replace('~^.+/~', '', $import);
        $nameOrigin = $name . 'Origin';
        $extends = $this->modulesConfig['extend'][$import];

        $js = "import { $name as $nameOrigin } from " . json_pretty(LIGHTNA_ENTRY . $this->filesIndex[$import]) . ';';
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
        $this->compiled->putFile('webpack/' . $name . '.js', $js);
    }

    protected function makeAliasesConfig(): void
    {
        $aliases = &$this->wpConfig['resolve']['alias'];
        $aliases = merge($this->extendAliases, $this->getModuleAliases());
    }

    protected function getModuleAliases(): array
    {
        $aliases = [];
        foreach ($this->relevantModules as $folder) {
            $aliases[$this->getModuleAlias($folder)] = LIGHTNA_ENTRY . $folder . '/js';
        }

        return $aliases;
    }

    protected function getModuleAlias(string $folder): string
    {
        return implode('/', array_slice(explode('/', rtrim($folder, '/')), -2));
    }

    protected function saveConfig(): void
    {
        $this->compiled->putFile('webpack/webpack.config.js', $this->getWpConfigJs());
    }

    protected function getWpConfigJs(): string
    {
        return 'module.exports = ' . json_pretty($this->getWpConfig());
    }

    protected function getWpConfig(): array
    {
        return merge(
            $this->modulesConfig['webpack'] ?? [],
            $this->wpConfig,
        );
    }
}
