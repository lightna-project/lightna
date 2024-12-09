<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Build;

use Exception;
use Lightna\Engine\App\Build;
use Lightna\Engine\App\Compiler;

class Config extends Build
{
    protected string $dir;
    protected Compiler $compiler;
    protected Build $build;

    /** @noinspection PhpUnused */
    protected function defineDir(): void
    {
        $this->dir = LIGHTNA_ENTRY . 'config/';
    }

    public function apply(): void
    {
        $version = time();

        foreach (LIGHTNA_AREAS as $area) {
            $this->save($area, [
                'version' => $version,
                'value' => $this->load($area),
            ]);
        }

        $this->save('version', $version);
    }

    public function load(string $name): array
    {
        return merge(
            opcache_load_revalidated($this->compiler->getBuildOrigDir() . 'config/' . $name . '.php'),
            opcache_load_revalidated(LIGHTNA_ENTRY . 'config.php'),
            opcache_load_revalidated(LIGHTNA_ENTRY . 'env.php'),
            ['src_dir' => $this->findSrcDir()]
        );
    }

    protected function findSrcDir(): string
    {
        $dir = __DIR__;
        while (!is_file($dir . '/index.php') && $dir !== '/') {
            $dir = dirname($dir);
        }
        if ($dir === '/') throw new Exception('src_dir not found');

        return getRelativePath(LIGHTNA_ENTRY, $dir);
    }
}
