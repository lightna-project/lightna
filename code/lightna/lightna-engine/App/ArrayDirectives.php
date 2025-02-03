<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;

class ArrayDirectives implements ObjectManagerIgnore
{
    public static function apply(array &$data): void
    {
        if (empty($data['directive'])) {
            return;
        }

        foreach ($data['directive'] as $directiveText) {
            if (empty($directiveText)) {
                continue;
            }

            $directiveText = filter_extra_spaces($directiveText);
            $words = explode(' ', $directiveText);
            if (!count($words)) {
                continue;
            }

            $directive = array_shift($words);
            if ($directive === 'position') {
                static::applyPosition($data, $words);
            } elseif ($directive === 'delete') {
                static::applyDelete($data, $words);
            } elseif ($directive === 'replace') {
                static::applyReplace($data, $words);
            } elseif ($directive === 'move') {
                static::applyMove($data, $words);
            } else {
                throw new Exception('Unsupported directive "' . $directiveText . '"');
            }
        }
    }

    protected static function applyPosition(array &$data, array $params = []): void
    {
        $topic = 'Array position directive';
        $context = ' in "position ' . implode(' ', $params) . '"';

        $path = array_shift($params);
        if (!is_string($path)) {
            throw new Exception($topic . ': Invalid path parameter' . $context);
        }

        $pathS = static::dot2slash($path);
        if (!array_path_exists($data, $pathS)) {
            throw new Exception($topic . ': path "' . $pathS . '" doesn\'t exist' . $context);
        }

        $position = array_shift($params);
        if (!in_array($position, ['last', 'first', 'after', 'before'])) {
            throw new Exception($topic . ': unexpected value for position "' . $position . '"' . $context);
        }

        $path = new ArrayPath($pathS);
        $parent = array_path_get($data, $path->parent);

        $dest = null;
        if (in_array($position, ['after', 'before'])) {
            $dest = array_shift($params);
            if ($dest === null) {
                throw new Exception($topic . ': undefined destination' . $context);
            }
            if ($dest === $path->key) {
                throw new Exception($topic . ': specified destination must differ from operand' . $context);
            }
        }

        if (in_array($position, ['after', 'before']) && !array_key_exists($dest, $parent)) {
            throw new Exception($topic . ': undefined destination "' . $dest . '"' . $context);
        }

        if (!in_array($position, ['last', 'first', 'before', 'after'])) {
            throw new Exception($topic . ': unsupported position "' . $position . '"' . $context);
        }

        static::makePosition($data, $path, $position, $dest);
    }

    protected static function makePosition(array &$data, ArrayPath $path, string $position, ?string $dest = null): void
    {
        $parent = array_path_get($data, $path->parent);
        $copy = $parent[$path->key];
        unset($parent[$path->key]);

        if ($position === 'last') {
            $parent[$path->key] = $copy;
        } elseif ($position === 'first') {
            $parent = [$path->key => $copy] + $parent;
        } elseif (in_array($position, ['before', 'after'])) {
            $pos = array_search($dest, array_keys($parent));
            $pos += (int)($position === 'after');
            $before = array_slice($parent, 0, $pos, true);
            $after = array_slice($parent, $pos, null, true);
            $parent = $before + [$path->key => $copy] + $after;
        }

        array_path_set($data, $path->parent, $parent);
    }

    protected static function applyDelete(array &$data, array $params = []): void
    {
        $topic = 'Array delete directive';
        $context = ' in "delete ' . implode(' ', $params) . '"';

        $mode = array_shift($params);
        if (!in_array($mode, ['key', 'value'])) {
            throw new Exception($topic . ': unexpected mode "' . $mode . '"' . $context);
        }

        $path = array_shift($params);
        if (empty($path)) {
            throw new Exception($topic . ': Invalid path parameter "' . $path . '"' . $context);
        }

        $pathS = static::dot2slash($path);
        if (!array_path_exists($data, $pathS)) {
            throw new Exception($topic . ': path "' . $pathS . '" doesn\'t exist' . $context);
        }

        $path = new ArrayPath($pathS);
        $value = null;
        if ($mode === 'value') {
            if (!is_array(array_path_get($data, $path->path))) {
                throw new Exception($topic . ': specified path "' . $path->path . '" isn\'t array' . $context);
            }
            $value = array_shift($params);
            if ($value === null) {
                throw new Exception($topic . ': undefined value' . $context);
            }
        }

        static::makeDelete($data, $mode, $path, $value);
    }

    protected static function makeDelete(
        array &$data,
        string $mode,
        ArrayPath $path,
        ?string $value = null
    ): void {
        if ($mode === 'key') {
            static::makeDeleteKey($data, $path);
        } else {
            static::makeDeleteValue($data, $path, $value);
        }
    }

    protected static function makeDeleteKey(array &$data, ArrayPath $path): void
    {
        $parent = array_path_get($data, $path->parent);
        unset($parent[$path->key]);
        array_path_set($data, $path->parent, $parent);
    }

    protected static function makeDeleteValue(array &$data, ArrayPath $path, string $value): void
    {
        $node = array_path_get($data, $path->path);
        foreach ($node as $k => $v) {
            if ($v == $value) {
                unset($node[$k]);
            }
        }

        array_path_set($data, $path->path, $node);
    }

    protected static function applyReplace(array &$data, array $params = []): void
    {
        $topic = 'Array replace directive';
        $context = ' in "replace ' . implode(' ', $params) . '"';

        $mode = array_shift($params);
        if ($mode !== 'value') {
            throw new Exception($topic . ': unexpected mode "' . $mode . '"' . $context);
        }

        $value = array_shift($params);
        if (!is_string($value) || $value === '') {
            throw new Exception($topic . ': value is expected' . $context);
        }

        $to = array_shift($params);
        if ($to !== 'to') {
            throw new Exception($topic . ': "to" is expected' . $context);
        }

        $newValue = array_shift($params);
        if (!is_string($newValue) || $newValue === '') {
            throw new Exception($topic . ': replacement is expected' . $context);
        }

        $in = array_shift($params);
        if ($in !== 'in') {
            throw new Exception($topic . ': "in" is expected' . $context);
        }

        $path = array_shift($params);
        if (empty($path)) {
            throw new Exception($topic . ': path is expected' . $context);
        }

        $pathS = static::dot2slash($path);
        if (!array_path_exists($data, $pathS)) {
            throw new Exception($topic . ': path "' . $pathS . '" doesn\'t exist' . $context);
        }

        $path = new ArrayPath($pathS);

        static::makeReplace($data, $value, $newValue, $path);
    }

    protected static function makeReplace(
        array &$data,
        string $value,
        string $newValue,
        ArrayPath $path,
    ): void {
        $node = array_path_get($data, $path->path);
        foreach ($node as &$v) {
            if ($v == $value) {
                $v = $newValue;
            }
        }

        array_path_set($data, $path->path, $node);
    }

    protected static function applyMove(array &$data, array $params = []): void
    {
        $topic = 'Array move directive';
        $context = ' in "move ' . implode(' ', $params) . '"';

        $from = array_shift($params);
        if (!is_string($from)) {
            throw new Exception($topic . ': Invalid path parameter"' . $from . '"' . $context);
        }

        $to = array_shift($params);
        if ($to !== 'to') {
            throw new Exception($topic . ': "to" expected after "' . $from . '"' . $context);
        }

        $to = array_shift($params);
        if (!is_string($to)) {
            throw new Exception($topic . ': Undefined "to" path parameter"' . $to . '"' . $context);
        }

        $from = new ArrayPath(static::dot2slash($from));
        if (!array_path_exists($data, $from->path)) {
            throw new Exception($topic . ': path "' . $from->path . '" doesn\'t exist' . $context);
        }

        $to = new ArrayPath(static::dot2slash($to));
        if (array_path_exists($data, $to->path)) {
            throw new Exception($topic . ': "to" path "' . $to->path . '" exists and can\'t be overwritten' . $context);
        }

        if (!array_path_exists($data, $to->parent)) {
            throw new Exception($topic . ': "to" path "' . $to->path . '" doesn\'t exist' . $context);
        }

        static::makeMove($data, $from, $to);
    }

    protected static function makeMove(array &$data, ArrayPath $from, ArrayPath $to): void
    {
        $fromParent = array_path_get($data, $from->parent);
        $copy = $fromParent[$from->key];
        unset($fromParent[$from->key]);
        array_path_set($data, $from->parent, $fromParent);
        array_path_set($data, $to->path, $copy);
    }

    protected static function dot2slash(string $path): string
    {
        return str_replace('.', '/', $path);
    }
}
