<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Autoloader
{
    protected static array $classes = [];

    public static function setClasses(array $classes): void
    {
        static::$classes = $classes;
    }

    public static function loadClass(string $class): void
    {
        if (!isset(static::$classes[$class])) {
            return;
        }

        if (static::$classes[$class][0] === 'b') {
            require Bootstrap::getBuildDir() . static::$classes[$class][1];
        } else {
            require LIGHTNA_ENTRY . static::$classes[$class][1];
        }
    }
}
