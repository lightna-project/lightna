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

        foreach ($this->getAllLibs() as $ns => $folder) {
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

        $this->build->save('object/map', $classes);
    }

    protected function getAllLibs(): array
    {
        $config = require LIGHTNA_ENTRY . 'config.php';

        return merge(
            ['Lightna\Engine' => LIGHTNA_SRC],
            $config['modules'] ?? [],
            $config['libs'] ?? []
        );
    }
}
