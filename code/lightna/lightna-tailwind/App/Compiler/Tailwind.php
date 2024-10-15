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
        $this->makeMainConfig();
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
        $config['tailwind']['content'] = [
            $moduleFolder . '/template/**/*.phtml',
            $moduleFolder . '/layout/*.yaml',
            $moduleFolder . '/js/**/*.js',
        ];
    }

    protected function saveModulesConfig(): void
    {
        $this->compiled->putFile(
            'tailwind/config.json',
            json_pretty($this->twConfig['tailwind']),
        );
    }


    protected function indexImports(): void
    {
        $this->importsIndex = [];
        foreach ($this->twConfig['entry'] as $entry) {
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
            foreach ($entry['import'] as $import) {
                $entryCss .= "\n" . '@import ' . json_pretty($this->getImport($import)) . ';';
            }
            $entryCss .= "\n" . '@config "tailwind.config.js";';

            $this->saveEntryCss($name, $entryCss);
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
        $this->compiled->putFile('tailwind/' . $name . '.css', $css);
    }

    protected function makeMainConfig(): void
    {
        file_copy(__DIR__ . '/../../tailwind.config.js', $this->compiled->getDir() . 'tailwind/tailwind.config.js');
    }
}
