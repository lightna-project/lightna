<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Build\Config as BuildConfig;

class Compiler extends ObjectA
{
    protected array $config;
    protected Build $build;

    public function init(array $data = []): void
    {
        $this->build = new Build();
    }

    public function defineConfig(): void
    {
        $this->config = Bootstrap::getConfig();
    }

    public function clean(): void
    {
        if (is_dir($this->getBuildBakDir())) {
            rcleandir($this->getBuildBakDir());
            rmdir($this->getBuildBakDir());
        }

        rcleandir($this->build->getDir());
        rcleandir($this->getAssetDir());
    }

    public function version(): void
    {
        $this->build->save('version', time());
    }

    public function apply(): void
    {
        $this->applyBuild();
        $this->applyAssets();
        $this->applyConfig();
    }

    protected function applyBuild(): void
    {
        if (is_dir($this->getBuildOrigDir())) {
            rename($this->getBuildOrigDir(), $this->getBuildBakDir());
        }

        rename($this->build->getDir(), $this->getBuildOrigDir());

        if (is_dir($this->getBuildBakDir())) {
            rcleandir($this->getBuildBakDir());
            rmdir($this->getBuildBakDir());
        }
    }

    protected function applyAssets(): void
    {
        if (!is_dir($this->getAssetBuildingDir())) {
            echo cli_warning('No assets to apply') . "\n";
            return;
        }

        if (is_dir($this->getAssetOrigBakDir())) {
            rcleandir($this->getAssetOrigBakDir());
            rmdir($this->getAssetOrigBakDir());
        }

        rename($this->getAssetOrigDir(), $this->getAssetOrigBakDir());
        rename($this->getAssetBuildingDir(), $this->getAssetOrigDir());

        rcleandir($this->getAssetOrigBakDir());
        rmdir($this->getAssetOrigBakDir());
    }

    protected function applyConfig(): void
    {
        getobj(BuildConfig::class)->apply();
    }

    public function getAssetDir(): string
    {
        $dir = Bootstrap::getCompilerMode() === 'default'
            ? $this->getAssetBuildingDir()
            : LIGHTNA_ENTRY . $this->config['asset_dir'];

        return rtrim($dir, '/') . '/';
    }

    protected function getBuildDir(string $name): string
    {
        return preg_replace('~[^/]+/?$~', $name, $this->build->getDir()) . '/';
    }

    protected function getBuildBakDir(): string
    {
        return $this->getBuildDir('build.bak');
    }

    public function getBuildOrigDir(): string
    {
        return $this->getBuildDir('build');
    }

    protected function getAssetBuildingDir(): string
    {
        return $this->getBuildDir('asset.building');
    }

    protected function getAssetOrigDir(): string
    {
        return LIGHTNA_ENTRY . rtrim($this->config['asset_dir'], '/') . '/';
    }

    protected function getAssetOrigBakDir(): string
    {
        return $this->getBuildDir('asset.bak');
    }
}
