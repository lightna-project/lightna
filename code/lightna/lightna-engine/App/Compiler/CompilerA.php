<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Closure;
use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Build;
use Lightna\Engine\App\Compiler;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CompilerA extends ObjectA
{
    protected Compiler $compiler;
    protected Build $build;
    protected array $overrides;

    protected function init(array $data = []): void
    {
        $this->build = new Build();
        $this->build->init($data);
        parent::init($data);
    }

    /** @noinspection PhpUnused */
    protected function defineOverrides(): void
    {
        $this->overrides = [];
        $this->walkFilesInModules('override', [], $this->processOverride(...));
    }

    protected function processOverride(string $subPath, string $file, string $moduleName): void
    {
        if (count($parts = explode('/', $subPath)) < 4) {
            throw new LightnaException("Invalid override \"$subPath\" in module\"$moduleName\"");
        }

        $module = $parts[0] . '/' . $parts[1];
        $type = $parts[2];
        $name = $module . '/' . implode('/', array_slice($parts, 3));
        $this->overrides[$type][$name] = [
            'rel' => $file,
            'sub' => $moduleName . '/override/' . $subPath,
        ];
    }

    protected function getEnabledModules(): array
    {
        return Bootstrap::getEnabledModules();
    }

    protected function walkFilesInModules(string $subDir, array $fileExtensions, Closure $callback): void
    {
        $subDir = $subDir . '/';
        $root = realpath(LIGHTNA_ENTRY) . '/';

        foreach ($this->getEnabledModules() as $module) {
            $folder = $module['path'];
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
                        $module['name'],
                        $relPath,
                    );
                }
            }
        }
    }
}
