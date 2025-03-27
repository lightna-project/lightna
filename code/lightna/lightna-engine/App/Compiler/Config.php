<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;
use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectManagerIgnore;

class Config extends CompilerA implements ObjectManagerIgnore
{
    public function make(): void
    {
        $this->init();

        foreach (LIGHTNA_AREAS as $area) {
            $this->build->save(
                'config/' . $area,
                $this->getAreaConfig($area),
            );
        }
    }

    protected function getAreaConfig(string $area): array
    {
        $config = $this->getYamlConfig($area);
        ArrayDirectives::apply($config);
        $this->defineEnabledModules($config);
        $this->defineAssetBase($config);

        return $config;
    }

    protected function getYamlConfig(string $area): array
    {
        return $this->mergeYamlFiles($this->getYamlFiles($area));
    }

    protected function getYamlFiles(string $area): array
    {
        $folders = [];
        foreach (Bootstrap::getEnabledModules() as $module) {
            $folders[] = $module['path'];
        }
        $folders[] = LIGHTNA_ENTRY;

        $files = [];
        foreach ($folders as $folder) {
            $folder = $this->alignFolder($folder);
            $files = merge($files, $this->getYamlSubFiles($folder, $area));
            $files = merge($files, $this->getYamlRootFiles($folder, $area));
        }

        return $files;
    }

    protected function getYamlSubFiles(string $moduleFolder, string $area): array
    {
        if (!is_dir($configFolder = $moduleFolder . '/config/')) {
            return [];
        }

        $files = [];
        foreach ($this->listSubFolders($configFolder) as $subFolder) {
            if ($area === $subFolder || !in_array($subFolder, LIGHTNA_AREAS)) {
                $files = merge($files, rscan($configFolder . $subFolder, '~.+\.yaml~'));
            }
        }

        return $files;
    }

    protected function getYamlRootFiles(string $moduleFolder, string $area): array
    {
        $files = [];
        foreach (['/config.yaml', '/config/*.yaml'] as $pattern) {
            $files = merge(
                $files,
                array_filter(glob($moduleFolder . $pattern), 'is_file')
            );
        }

        foreach ($files as $i => $file) {
            $name = preg_replace('~\.yaml$~', '', basename($file));
            if (in_array($name, LIGHTNA_AREAS) && $name !== $area) {
                unset($files[$i]);
            }
        }

        return $files;
    }

    protected function alignFolder(string $folder): string
    {
        return rtrim($folder[0] === '/' ? $folder : LIGHTNA_ENTRY . $folder, '/');
    }

    protected function listSubFolders(string $folder): array
    {
        $subFolders = array_filter(glob($folder . '/*'), 'is_dir');
        foreach ($subFolders as &$subFolder) {
            $subFolder = substr($subFolder, strrpos($subFolder, '/') + 1);
        }

        return $subFolders;
    }

    protected function mergeYamlFiles(array $files): array
    {
        $config = [];
        foreach ($files as $file) {
            $data = yaml_parse_file($file);
            $data = array_expand_keys($data, '.', ['/cli/command' => 1]);
            $config = merge($config, $data);
        }

        return $config;
    }

    protected function defineEnabledModules(array &$config): void
    {
        $config['enabled_modules'] = Bootstrap::getEnabledModules();
    }

    protected function defineAssetBase(array &$config): void
    {
        $fullConfig = merge($config, Bootstrap::getConfig());

        if (!$docDir = realpath($docDirConfig = LIGHTNA_ENTRY . $fullConfig['doc_dir'])) {
            throw new LightnaException('Invalid doc_dir [' . $docDirConfig . ']');
        }

        $assetDir = Bootstrap::getAssetDir();
        if (!is_dir($assetDir)) {
            try {
                // Not recursive
                mkdir($assetDir);
            } catch (Exception $e) {
            }
            if (!is_dir($assetDir)) {
                throw new LightnaException('Invalid asset_dir "' . $assetDir . '"');
            }
        }

        $config['asset_base'] = '/' . getRelativePath(
                $docDir,
                Bootstrap::getEditionAssetDir(),
                false,
            ) . '/';
    }
}
