<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\App\ArrayDirectives;

class LayoutDirectives extends ArrayDirectives
{
    protected static function dot2slash(string $path): string
    {
        return ltrim(str_replace('.', '/./', $path), '/');
    }
}
