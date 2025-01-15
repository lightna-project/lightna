<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\ObjectManagerIgnore;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ClassMap extends CompilerA implements ObjectManagerIgnore
{
    protected array $classes = [];

    public function make(): void
    {
        $this->init();
        $this->collectClasses();
        $this->saveMap();
    }

    protected function collectClasses(): void
    {
        $root = realpath(LIGHTNA_ENTRY);

        foreach ($this->getAllPackages() as $ns => $folders) {
            $folders = (array)$folders;

            foreach ($folders as $folder) {
                if ($folder[0] !== '/') {
                    $folder = LIGHTNA_ENTRY . $folder;
                }

                $path = preg_replace('~^' . preg_quote($root) . '~', '', $folder);
                $path = trim($path, '/') . '/';

                $iterator = $this->getDirectoryIterator($folder);
                foreach ($iterator as $file) {
                    $this->mapFile($file, $ns, $path, $iterator);
                }
            }
        }
    }

    protected function getAllPackages(): array
    {
        $modules = [];
        foreach (Bootstrap::getEnabledModules() as $module) {
            $modules[$module['namespace']] = [$module['path']];
        }

        return merge(
            $this->getComposerPackages(),
            $modules,
        );
    }

    protected function getComposerPackages(): array
    {
        $psr4Config = require LIGHTNA_ENTRY . 'vendor/composer/autoload_psr4.php';
        $packages = [];
        foreach ($psr4Config as $ns => $paths) {
            $packages[trim($ns, '\\')] = $paths;
        }

        return $packages;
    }

    protected function getDirectoryIterator(string $folder): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($folder),
                [$this, 'recursiveDirectoryIteratorCallback']
            )
        );
    }

    protected function recursiveDirectoryIteratorCallback(SplFileInfo $current): bool
    {
        return true;
    }

    protected function mapFile(SplFileInfo $file, string $ns, string $path, RecursiveIteratorIterator $iterator): void
    {
        if ($file->isFile() && $file->getExtension() === 'php' && ctype_upper($file->getFilename()[0])) {
            /** @noinspection PhpUndefinedMethodInspection */
            $subName = $iterator->getSubPathname();
            $class = $ns . '\\' . str_replace(['/', '.php'], ['\\', ''], $subName);
            $this->classes[$class] = ['e', $path . $subName];
        }
    }

    protected function saveMap(): void
    {
        $this->build->save('object/map', $this->classes);
    }
}
