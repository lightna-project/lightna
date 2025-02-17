<?php

declare(strict_types=1);

namespace Lightna\Tailwind\App\Compiler;

use Exception;
use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Compiler\CompilerA;
use Lightna\Engine\App\Config as AppConfig;

class Tailwind extends CompilerA
{
    protected AppConfig $appConfig;
    protected array $modulesConfig;
    protected array $modulesIndex;
    protected array $importsIndex;

    public function make(): void
    {
        $this->build->addValidateConfigOverrides('tailwind/config');
        $this->collectModulesConfig();
        $this->makeEntries();
        $this->makeShellScript('build', false);
        $this->makeShellScript('update', true);
    }

    /** @noinspection PhpUnused */
    protected function defineModulesIndex(): void
    {
        $this->modulesIndex = [];
        foreach ($this->getEnabledModules() as $module) {
            $this->modulesIndex[$module['name']] = $module['path'];
        }
    }

    /** @noinspection PhpUnused */
    protected function defineImportsIndex(): void
    {
        $this->importsIndex = [];
        foreach ($this->modulesConfig['entry'] as $entry) {
            foreach ($entry['import'] as $import) {
                $this->importsIndex[$import] = $this->getImportFile($import);
            }
        }
    }

    protected function collectModulesConfig(): void
    {
        $this->modulesConfig = [];
        foreach ($this->getEnabledModules() as $module) {
            $this->modulesConfig = merge(
                $this->modulesConfig,
                $this->getModuleConfig($module['path']),
            );
        }

        $this->modulesConfig['entry'] ??= [];
        ArrayDirectives::apply($this->modulesConfig);

        // Save result to validate config overrides
        $this->build->save('tailwind/config', $this->modulesConfig);
    }

    protected function getModuleConfig(string $folder): array
    {
        if (!is_file($configFile = $folder . '/tailwind.yaml')) {
            return [];
        }

        return yaml_parse_file($configFile);
    }

    protected function getImportFile(string $import): string
    {
        if ($this->isCoreImport($import)) {
            return $import;
        }

        $path = $this->getModularPath($import, 'css');
        $name = $path['module'] . '/' . $path['subPath'];
        if (isset($this->overrides['css'][$name])) {
            $path = $this->getModularPath($this->overrides['css'][$name]['sub'], '');
        }

        if (!file_exists($absFile = LIGHTNA_ENTRY . $path['fullPath'])) {
            throw new Exception("Import \"$import\" not found. Expected file \"$absFile\"");
        }

        return $this->modulesIndex[$path['module']] . '/' . $path['path'];
    }

    protected function getModularPath(string $path, string $subfolder = null): array
    {
        $parts = explode('/', $path);
        if (count($parts) < 3) {
            throw new Exception("Invalid Tailwind path \"$path\". Have you specified the module namespace?");
        }

        $module = implode('/', array_slice($parts, 0, 2));
        if (!isset($this->modulesIndex[$module])) {
            throw new Exception("Invalid Tailwind path \"$path\". The module \"$module\" was not found.");
        }

        $subPath = implode('/', array_slice($parts, 2));
        $path = ($subfolder ? "$subfolder/" : '') . $subPath;

        return [
            'module' => $module,
            'path' => $path,
            'subPath' => $subPath,
            'fullPath' => $this->modulesIndex[$module] . '/' . $path,
        ];
    }

    protected function isCoreImport(string $import): bool
    {
        return !preg_match('~\.css$~', $import);
    }

    protected function getImport(string $import): string
    {
        return '~/' . (
            $this->isCoreImport($import) ?
                $import :
                $this->importsIndex[$import]
            );
    }

    protected function makeEntries(): void
    {
        foreach ($this->modulesConfig['entry'] as $name => $entry) {
            $this->saveEntryCss($name, $this->getEntryCss($entry));
            $this->saveEntryConfigJson($name, $this->getEntryConfigJson($entry));
            $this->saveEntryConfigJs($name, $this->getEntryConfigJs());
        }
    }

    protected function getEntryCss(array $entry): string
    {
        $css = '';
        foreach ($entry['import'] as $import) {
            $css .= "\n" . '@import ' . json_pretty($this->getImport($import)) . ';';
        }
        $css .= "\n@config \"config.js\";";

        return $css;
    }

    protected function saveEntryCss(string $name, string $css): void
    {
        $this->build->putFile("tailwind/$name/entry.css", $css);
    }

    protected function getEntryConfigJson(array $entry): string
    {
        return json_pretty(merge(
            $this->modulesConfig['tailwind'],
            [
                'content' => $this->getEntryContent($entry),
            ]
        ));
    }

    protected function getEntryContent(array $entry): array
    {
        $raw = $entry['content'] ?? [];
        $content = [];
        foreach ($raw as $item) {
            $content[] = $this->getModularPath($item)['fullPath'];
        }

        return $content;
    }

    protected function saveEntryConfigJson(string $name, string $config): void
    {
        $this->build->putFile("tailwind/$name/config.json", $config);
    }

    protected function getEntryConfigJs(): string
    {
        return file_get_contents(__DIR__ . '/../../tailwind.config.js');
    }

    protected function saveEntryConfigJs(string $name, string $config): void
    {
        $this->build->putFile("tailwind/$name/config.js", $config);
    }

    protected function makeShellScript(string $scriptName, bool $isDirect): void
    {
        $sh = $sep = '';
        foreach ($this->modulesConfig['entry'] as $name => $entry) {
            $sh .= $sep . $this->getBuildCommand($name, $isDirect);
            $sep = " && \\\n";
        }

        $this->saveShellScript($scriptName, $sh);
    }

    protected function getBuildCommand(string $entryName, bool $isDirect): string
    {
        return "npx tailwindcss"
            . $this->getBuildCommandArgs($entryName, $isDirect)
            . " -i " . escapeshellarg($this->getBuildDir($isDirect) . "tailwind/$entryName/entry.css")
            . " -o " . escapeshellarg($this->getAssetBuildDir($isDirect) . 'style/' . $entryName . '.css')
            . " --postcss " . escapeshellarg('./postcss.config.js');
    }

    protected function getBuildCommandArgs(string $entryName, bool $isDirect): string
    {
        return $isDirect ? '' : ' --minify';
    }

    protected function getBuildDir(bool $isDirect): string
    {
        return $this->getCompilerDir() . ($isDirect ? 'build' : 'building') . '/';
    }

    protected function getCompilerDir(): string
    {
        return getRelativePath(LIGHTNA_ENTRY, dirname(Bootstrap::getBuildDir()));
    }

    protected function getAssetBuildDir(bool $isDirect): string
    {
        return $isDirect ?
            $this->getAssetDir() . 'build/' :
            $this->getCompilerDir() . 'asset.building/build/';
    }

    protected function getAssetDir(): string
    {
        if (!is_dir($dir = Bootstrap::getEditionAssetDir())) {
            mkdir($dir, 0775, true);
        }

        return getRelativePath(LIGHTNA_ENTRY, $dir);
    }

    protected function saveShellScript(string $scriptName, string $script): void
    {
        $file = "tailwind/$scriptName.sh";
        $this->build->putFile($file, $script);
        chmod($this->build->getDir() . $file, 0774);
    }
}
