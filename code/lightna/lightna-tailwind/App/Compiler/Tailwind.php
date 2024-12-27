<?php

declare(strict_types=1);

namespace Lightna\Tailwind\App\Compiler;

use Exception;
use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Compiler\CompilerA;

class Tailwind extends CompilerA
{
    protected array $twConfig;
    protected array $importsIndex;

    public function make(): void
    {
        $this->collectModulesConfig();
        $this->saveModulesConfig();
        $this->indexImports();
        $this->makeEntries();
        $this->makeJsConfig();
    }

    protected function collectModulesConfig(): void
    {
        $this->twConfig = [];
        foreach ($this->getModules() as $folder) {
            $this->twConfig = merge(
                $this->twConfig,
                $this->getModuleConfig($folder),
            );
        }

        ArrayDirectives::apply($this->twConfig);
    }

    protected function getModuleConfig(string $folder): array
    {
        $configFile = $folder . '/tailwind.yaml';
        if (!is_file($configFile)) {
            return [];
        }

        $config = yaml_parse_file($configFile);
        $this->addModuleContent($folder, $config);

        return $config;
    }

    protected function addModuleContent(string $moduleFolder, array &$config): void
    {
        foreach ($this->getModules() as $folder) {
            $configFile = $folder . '/tailwind.yaml';
            if (!is_file($configFile)) {
                continue;
            }

            foreach ($config['entry'] as $name => $entry) {
                if (!isset($entry['content'])) {
                    continue;
                }

                foreach ($entry['content'] as $content) {
                    $content = $folder . '/' . $content;
                    $config['entry'][$name]['moduleContent'][] = $content;
                }
            }
        }
    }

    protected function saveModulesConfig(): void
    {
        foreach ($this->twConfig['entry'] as $name => $entry) {
            $entryContent['content'] = $entry['moduleContent'] ?? [];
            $this->build->putFile(
                'tailwind/' . $name . '.json',
                json_pretty(merge(
                    $this->twConfig['tailwind'],
                    $entryContent
                ))
            );
        }
    }


    protected function indexImports(): void
    {
        $this->importsIndex = [];
        foreach ($this->twConfig['entry'] as $entry) {
            if (!array_key_exists('import', $entry)) {
                continue;
            }
            foreach ($entry['import'] as $import) {
                $this->importsIndex[$import] = null;
            }
        }

        foreach ($this->getModules() as $folder) {
            foreach ($this->importsIndex as $import => &$dest) {
                $file = $folder . '/css/' . $import;
                if (file_exists($file)) {
                    $dest = $file;
                }
            }
        }
    }

    protected function isModuleImport(string $import): bool
    {
        return (bool)preg_match('~\.css$~', $import);
    }

    protected function makeEntries(): void
    {
        foreach ($this->twConfig['entry'] as $name => $entry) {
            $entryCss = '';
            if (array_key_exists('import', $entry)) {
                foreach ($entry['import'] as $import) {
                    $entryCss .= "\n" . '@import ' . json_pretty($this->getImport($import)) . ';';
                }
            }
            $entryCss .= "\n" . '@config "tailwind.config.' . $name . '.js";';

            $this->saveEntryCss($name, $entryCss);
        }
    }

    protected function makeJsConfig(): void
    {
        foreach ($this->twConfig['entry'] as $name => $entry) {
            $configJs = "import { readFileSync } from 'fs';\n";
            $configJs .= "const allModulesConfig = JSON.parse(readFileSync(__dirname + '/" . $name . ".json', 'utf8'));\n";
            $configJs .= "module.exports = allModulesConfig;\n";

            $this->saveConfigJs($name, $configJs);
        }
    }

    protected function getImport(string $import): string
    {
        if (!$this->isModuleImport($import)) {
            return $import;
        } else {
            $moduleImport = $this->importsIndex[$import] ?? '';
            if (!$moduleImport) {
                throw new Exception("Tailwind compiler: Import [$import] not found.");
            }

            return '~/' . $moduleImport;
        }
    }

    protected function saveEntryCss(string $name, string $css): void
    {
        $this->build->putFile('tailwind/' . $name . '.css', $css);
    }

    protected function saveConfigJs(string $name, string $js): void
    {
        $configName = 'tailwind.config.' . $name . '.js';
        $this->build->putFile('tailwind/' . $configName, $js);
    }
}
