<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Opcache\Compiled;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CompilerA extends ObjectA
{
    protected Compiled $compiled;
    /** @AppConfig(modules) */
    protected ?array $modules;

    protected function walkFilesInModules(string $subDir, array $fileExtensions, Closure $callback): void
    {
        $folders = merge(['Lightna\Engine' => LIGHTNA_SRC], $this->modules ?? []);
        $subDir = $subDir . '/';
        $root = realpath(LIGHTNA_ENTRY) . '/';

        foreach ($folders as $ns => $folder) {
            $folder = rtrim(($folder[0] !== '/' ? LIGHTNA_ENTRY . $folder : $folder), '/') . '/';

            if (!is_dir($folder . $subDir)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder . $subDir)
            );

            $relPath = preg_replace('~^' . preg_quote($root) . '~', '', $folder);

            foreach ($iterator as $file) {
                if ($file->isFile() && (empty($fileExtensions) || in_array($file->getExtension(), $fileExtensions))) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $subPath = $iterator->getSubPathname();
                    $callback(
                        $subPath,
                        $relPath . $subDir . $subPath,
                        $this->getModulePath($folder),
                    );
                }
            }
        }
    }

    protected function getModulePath(string $moduleBaseDir): string
    {
        $parts = explode('/', trim($moduleBaseDir, '/'));

        return end($parts);
    }
}
