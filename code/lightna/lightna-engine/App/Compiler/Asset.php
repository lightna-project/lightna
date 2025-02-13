<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

class Asset extends CompilerA
{
    protected string $dir;

    /** @noinspection PhpUnused */
    protected function defineDir(): void
    {
        $this->dir = $this->compiler->getAssetDir();
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    public function make(): void
    {
        $this->walkFilesInModules(
            'asset',
            [],
            $this->processAsset(...)
        );
    }

    protected function processAsset(string $subPath, string $file, string $moduleName): void
    {
        $name = $moduleName . '/' . $subPath;
        if (isset($this->overrides['asset'][$name])) {
            $file = $this->overrides['asset'][$name]['rel'];
        }

        file_copy($file, $this->dir . $moduleName . '/' . $subPath);
    }
}
