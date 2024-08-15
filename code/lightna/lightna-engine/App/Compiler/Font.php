<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

class Font extends CompilerA
{
    /** @AppConfig(asset_dir) */
    protected string $dir;

    protected function init(): void
    {
        $this->dir = rtrim(LIGHTNA_ENTRY . $this->dir) . '/';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    public function make(): void
    {
        $this->walkFilesInModules(
            'font',
            [],
            function ($subPath, $file, $moduleName) {
                file_copy($file, $this->dir . $moduleName . '/font/' . $subPath);
            }
        );
    }
}
