<?php

declare(strict_types=1);

namespace Lightna\Webpack\App\Compiler;

use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Compiler\CompilerA;
use Lightna\Engine\App\Exception\LightnaException;
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

        $this->modulesConfig['entry'] ??= [];
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

    protected function indexImport(string $subPath, string $file, string $moduleName): void
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
        $allImports = [];

        foreach ($this->modulesConfig['entry'] as $name => $types) {
            $components = $types['component'] ?? [];
            unset($types['component']);

            foreach ($types as $key => $imports) {
                if (!in_array($key, ['component', 'import'])) {
                    continue;
                }

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
            throw new LightnaException('Webpack compiler: import "' . $import . '" not found');
        }

        return $this->importsIndex[$import];
    }

    protected function saveEntryJs(string $name, string $js): void
    {
        $this->build->putFile('webpack/' . $name . '.js', $js);
    }

    protected function getConfigJs(): string
    {
        return $this->getConfigJsHead() .
            $this->getConfigJsEntries() .
            $this->getConfigJsAliases() .
            $this->getConfigJsOptimizationSplitChunks() .
            $this->getConfigJsExports();
    }

    protected function getConfigJsHead(): string
    {
        $js = "const path = require('path');\n";
        $js .= "let config = " . json_pretty($this->modulesConfig['webpack'] ?? new stdClass()) . ";\n";
        $js .= "config.entry ||= {};\n";
        $js .= "config.resolve ||= {};\n";
        $js .= "config.resolve.alias ||= {};\n\n";

        return $js;
    }

    protected function getConfigJsEntries(): string
    {
        $js = '';
        foreach ($this->modulesConfig['entry'] as $name => $entry) {
            $js .= "var entry = config.entry[" . json_pretty($name) . "] ||= {};\n";
            $js .= "entry['import'] = path.resolve(__dirname, " . json_pretty('./' . $name . '.js') . ");\n";
            $js .= $this->getConfigJsEntryFields($name);
            $js .= "\n";
        }

        return $js;
    }

    protected function getConfigJsEntryFields(string $name): string
    {
        $js = '';
        foreach ($this->modulesConfig['entry'][$name] as $field => $value) {
            if (in_array($field, ['component', 'import'])) {
                continue;
            }
            $js .= $this->getConfigJsEntryField($field, $value);
        }

        return $js;
    }

    protected function getConfigJsEntryField(string $field, mixed $value): string
    {
        $field = match ($field) {
            'depends_on' => 'dependOn',
            default => $field,
        };

        return "entry[" . json_pretty($field) . "] = " . json_pretty($value) . ";\n";
    }

    protected function getConfigJsAliases(): string
    {
        $js = '';
        foreach ($this->getConfigAliases() as $alias => $path) {
            $js .= "config.resolve.alias[" . json_pretty($alias) . "]" .
                " = path.resolve(__dirname, " . json_pretty($path) . ");\n";
        }
        $js .= "\n";

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
                $aliases[$module['name']] = './' . getRelativePath($wpbDir, $abs);
            }
        }

        return $aliases;
    }

    protected function getConfigJsOptimizationSplitChunks(): string
    {
        $chunks = $this->getChunks();
        $js = "config.optimization ||= {};\n";
        $js .= "var splitChunks = config.optimization.splitChunks ||= {};\n";
        $js .= "splitChunks.chunks = (chunk) => " . json_pretty($chunks) . ".includes(chunk.name);\n";
        $js .= "splitChunks.minSize = 0;\n";

        return $js;
    }

    protected function getChunks(): array
    {
        $chunks = [];
        foreach ($this->modulesConfig['entry'] as $name => $entry) {
            if (!$dependsOn = ($entry['depends_on'] ?? null) ?? ($entry['dependOn'] ?? null)) {
                continue;
            }

            $chunks[$name] = 1;
            $chunks[$dependsOn] = 1;
        }

        return array_keys($chunks);
    }

    protected function getConfigJsExports(): string
    {
        return "\nmodule.exports = config;\n";
    }
}
