<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\App\ObjectManagerIgnore;
use Lightna\Engine\App\Opcache\Compiled;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClassMap extends CompilerA implements ObjectManagerIgnore
{
    public function make(): void
    {
        $this->compiled = new Compiled();
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
                    $classes[$class] = $path . $subName;
                }
            }
        }

        $this->compiled->save('object/map', $classes);
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
