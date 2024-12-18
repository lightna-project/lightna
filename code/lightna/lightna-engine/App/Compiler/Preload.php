<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

class Preload extends CompilerA
{
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
        foreach ($this->build->load('object/map') as $fileName) {
            $this->files[$fileName] = $fileName;
        }
    }

    protected function generatePreload(): void
    {
        $preload = "<?php";
        foreach ($this->files as $file) {
            $relFile = getRelativePath($this->build->getDir(), LIGHTNA_ENTRY . $file);
            $fileExpr = var_export('/' . $relFile, true);
            $preload .= "\nopcache_compile_file(__DIR__ . $fileExpr);";
        }
        $this->preload = $preload;
    }

    protected function save(): void
    {
        $this->build->putFile('preload.php', $this->preload);
    }
}
