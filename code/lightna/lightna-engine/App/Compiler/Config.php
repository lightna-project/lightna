<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;
use Lightna\Engine\App\ArrayDirectives;
use Lightna\Engine\App\ObjectManagerIgnore;
use Lightna\Engine\App\Opcache\Compiled;

class Config extends CompilerA implements ObjectManagerIgnore
{
    public function make(): void
    {
        $this->compiled = new Compiled();
        $envConfig = merge($this->getMainConfig(), $this->getEnvConfig());

        foreach (LIGHTNA_AREAS as $scope) {
            $config = merge(
                $this->getYamlConfig($scope),
                $envConfig
            );

            $this->applyDefaults($config);
            ArrayDirectives::apply($config);
            $this->defineAssetBase($config);
            $this->compiled->save('config/' . $scope, $config);
        }
    }

    protected function getYamlConfig(string $scope): array
    {
        return $this->mergeYamlFiles($this->getYamlFiles($scope));
    }

    protected function getYamlFiles(string $scope): array
    {
        $folders = merge([LIGHTNA_SRC], array_values($this->getMainConfig()['modules']), [LIGHTNA_ENTRY]);
        $files = [];
        foreach ($folders as $folder) {
            $folder = $this->alignFolder($folder);
            $files = merge($files, $this->getYamlSubFiles($folder, $scope));
            $files = merge($files, $this->getYamlRootFiles($folder, $scope));
        }

        return $files;
    }

    protected function getYamlSubFiles(string $moduleFolder, string $scope): array
    {
        if (!is_dir($configFolder = $moduleFolder . '/config/')) {
            return [];
        }

        $files = [];
        foreach ($this->listSubFolders($configFolder) as $subFolder) {
            if ($scope === $subFolder || !isset($this->scopes[$subFolder])) {
                $files = merge($files, rscan($configFolder . $subFolder, '~.+\.yaml~'));
            }
        }

        return $files;
    }

    protected function getYamlRootFiles(string $moduleFolder, string $scope): array
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
            if (in_array($name, LIGHTNA_AREAS) && $name !== $scope) {
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

    protected function getMainConfig(): array
    {
        return require LIGHTNA_ENTRY . 'config.php';
    }

    protected function getEnvConfig(): array
    {
        return require LIGHTNA_ENTRY . 'env.php';
    }

    protected function mergeYamlFiles(array $files): array
    {
        $config = [];
        foreach ($files as $file) {
            $data = yaml_parse_file($file);
            $data = array_expand_keys($data, '.');
            $config = merge($config, $data);
        }

        return $config;
    }

    protected function applyDefaults(array &$config): void
    {
        if ($defaultStorage = $config['default']['storage'] ?? '') {
            foreach ($config['entity'] as &$entity) {
                $entity['storage'] ??= $defaultStorage;
            }
        }
    }

    protected function defineAssetBase(array &$config): void
    {
        if (!$docDir = realpath($docDirConfig = LIGHTNA_ENTRY . $config['doc_dir'])) {
            throw new Exception('Invalid doc_dir [' . $docDirConfig . ']');
        }
        if (!$assetDir = realpath(LIGHTNA_ENTRY . $config['asset_dir'])) {
            try {
                // Not recursive
                mkdir(LIGHTNA_ENTRY . $config['asset_dir']);
            } catch (Exception $e) {
            }
            if (!$assetDir = realpath(LIGHTNA_ENTRY . $config['asset_dir'])) {
                throw new Exception('Invalid asset_dir');
            }
        }
        $config['asset_base'] = preg_replace('~^' . preg_quote($docDir, '~') . '~', '', $assetDir) . '/';
    }
}
