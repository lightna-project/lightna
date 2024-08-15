<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class ArrayPath implements ObjectManagerIgnore
{
    public readonly string $path;
    public readonly string $parent;
    public readonly string $key;

    public function __construct(string $path)
    {
        $this->path = $path;
        $parts = explode('/', $path);
        $this->key = array_pop($parts);
        $this->parent = implode('/', $parts);
    }
}
