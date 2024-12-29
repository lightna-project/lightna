<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\App\Build;
use Lightna\Engine\App\ObjectManagerIgnore;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClassMap extends CompilerA implements ObjectManagerIgnore
{
    public function make(): void
    {
        $this->build = new Build();
        $root = realpath(LIGHTNA_ENTRY);
        $classes = [];

        foreach ($this->getAllPackages() as $ns => $folders) {
            $folders = (array)$folders;

            foreach ($folders as $folder) {
                if ($folder[0] !== '/') {
                    $folder = LIGHTNA_ENTRY . $folder;
                }

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($folder)
                );

                $path = preg_replace('~^' . preg_quote($root) . '~', '', $folder);
                $path = trim($path, '/') . '/';

                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php' && ctype_upper($file->getFilename()[0])) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $subName = $iterator->getSubPathname();
                        $class = $ns . '\\' . str_replace(['/', '.php'], ['\\', ''], $subName);
                        $classes[$class] = ['e', $path . $subName];
                    }
                }
            }
        }

        $this->build->save('object/map', $classes);
    }

    protected function getAllPackages(): array
    {
        $config = require LIGHTNA_ENTRY . 'config.php';

        return merge(
            $this->getComposerPackages(),
            $config['modules'] ?? [],
            ['Lightna\Engine' => LIGHTNA_SRC],
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
}
