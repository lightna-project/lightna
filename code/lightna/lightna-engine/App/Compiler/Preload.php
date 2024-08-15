<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\App\Compiled;
use Lightna\Engine\App\ObjectA;

class Preload extends ObjectA
{
    protected Compiled $compiled;
    protected array $files = [];
    protected string $preload;

    public function make(): void
    {
        $this->collectFiles();
        $this->generatePreload();
        $this->save();
    }

    protected function collectFiles(): void
    {
        foreach ($this->compiled->load('object/map') as $fileName) {
            $this->files[$fileName] = $fileName;
        }
    }

    protected function generatePreload(): void
    {
        $preload = "<?php";
        foreach ($this->files as $file) {
            $fileExpr = var_export(realpath(LIGHTNA_ENTRY . $file), true);
            $preload .= "\nopcache_compile_file($fileExpr);";
        }
        $this->preload = $preload;
    }

    protected function save(): void
    {
        $this->compiled->putFile('opcache/preload.php', $this->preload);
    }
}
