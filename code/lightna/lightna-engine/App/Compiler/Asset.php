<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

class Asset extends CompilerA
{
    protected string $dir;

    protected function init(array $data = []): void
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
            function ($subPath, $file, $modulePath) {
                file_copy($file, $this->dir . $modulePath . '/' . $subPath);
            }
        );
    }
}
