<?php

declare(strict_types=1);

namespace Lightna\Webpack\App\Compiler;

use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Compiler\CompilerA;

class Webpack extends CompilerA
{
    protected array $modulesConfig;
    protected array $relevantModules;
    protected array $importsIndex;
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
        $this->importsIndex = [];
        foreach ($this->relevantModules as $folder) {
            $entries = $this->modulesConfig['entry'] ?? [];
            foreach ($entries as $types) {
                foreach ($types as $imports) {
                    foreach ($imports as $import) {
                        if ($this->doesModuleImportExist($folder, $import)) {
                            $this->importsIndex[$import] = $this->getModuleAlias($folder) . '/' . $import;
                        }
                    }
                }
            }
        }
    }

    protected function doesModuleImportExist(string $folder, string $import): bool
    {
        $file = str_ends_with($import, '.js') ? $import : $import . '.js';

        return file_exists($folder . '/js/' . $file);
    }

    protected function makeWpConfig(): void
    {
        $this->makeEntriesConfig();
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
        foreach ($components as $name => $import) {
            $js .= "\n" . 'import { ' . $name . ' } from ' . json_pretty($this->getImport($import)) . ';';
            $js .= "\n" . 'new ' . $name . '();';
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
        $aliases = [];
        foreach ($this->relevantModules as $folder) {
            $aliases[$this->getModuleAlias($folder)] = LIGHTNA_ENTRY . $folder . '/js';
        }
    }

    protected function getModuleAlias(string $folder): string
    {
        return implode('/', array_slice(explode('/', $folder), -2));
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
