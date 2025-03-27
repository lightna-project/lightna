<?php

declare(strict_types=1);

use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\I18n;
use Lightna\Engine\App\ObjectManager;

/**
 * @template T
 * @param class-string<T> $type
 * @return T
 */
function getobj(string $type, array $data = []): object
{
    return ObjectManager::get($type, $data);
}

/**
 * @template T
 * @param class-string<T> $type
 * @return T
 */
function newobj(string $type, array $data = []): object
{
    return ObjectManager::new($type, $data);
}

function getconf(string $path): mixed
{
    return ObjectManager::getConfigValue($path);
}

function a2o(array $arr, string $class): object
{
    $object = new $class();
    foreach ($arr as $key => $value) {
        $object->$key = $value;
    }

    return $object;
}

function o2a(object $obj): array
{
    return json_decode(json($obj), true);
}

/**
 * array_merge_recursive doesn't work as array_merge
 * merge does the logic which is expected from array_merge_recursive
 */
function merge(...$arrays): array
{
    $merge = array_shift($arrays);
    foreach ($arrays as $array) {
        $isNumeric = array_key_first($array) === 0 || array_key_first($merge) === 0;
        foreach ($array as $key => $value) {
            $isDestArray = array_key_exists($key, $merge) && is_array($merge[$key]);
            if (is_array($value) && !$isDestArray) {
                $merge[$key] = [];
            }

            if (is_array($value)) {
                $merge[$key] = merge($merge[$key], $value);
            } else {
                $isNumeric ? $merge[] = $value : $merge[$key] = $value;
            }
        }
    }

    return $merge;
}

function array_path_get(array $array, string $path): mixed
{
    $parts = $path === '' ? [] : explode('/', trim($path, '/'));
    $value = &$array;
    foreach ($parts as $part) {
        if (isset($value[$part])) {
            $value = &$value[$part];
        } else {
            return null;
        }
    }

    return $value;
}

function array_path_exists(array $array, string $path): mixed
{
    if ($path === '') {
        return true;
    }

    $parts = explode('/', trim($path, '/'));
    $lastKey = array_pop($parts);
    $parent = array_path_get($array, implode('/', $parts));

    return is_array($parent) && array_key_exists($lastKey, $parent);
}

function array_path_set(array &$array, string $path, mixed $value): void
{
    $parts = $path === '' ? [] : explode('/', trim($path, '/'));
    $dest = &$array;
    foreach ($parts as $part) {
        $dest = &$dest[$part];
    }

    $dest = $value;
}

function camel(string $name): string
{
    return lcfirst(str_replace('_', '', ucwords($name, '_')));
}

function array_camel(array $array): array
{
    $result = [];
    foreach ($array as $k => $v) {
        $k = camel((string)$k);
        $v = is_array($v) ? array_camel($v) : $v;
        $result[$k] = $v;
    }

    return $result;
}

function instance_of(object $object, string $class): bool
{
    // instanceof must fail if $class not undefined
    if (!class_exists($class)) {
        throw new LightnaException('Class "' . $class . '" not found');
    }

    return $object instanceof $class;
}

function json(mixed $var): string
{
    return json_encode($var, JSON_UNESCAPED_UNICODE);
}

function json_pretty(mixed $var): string
{
    return json_encode($var, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function phrase(string $phrase, array $args = []): string
{
    return escape(_phrase($phrase, $args));
}

function _phrase(string $phrase, array $args = []): string
{
    /** @var I18n $i18n */
    static $i18n;
    $i18n ??= getobj(I18n::class);

    return $i18n->phrase($phrase, $args);
}

function opcache_load_revalidated(string $file): mixed
{
    opcache_invalidate($file);

    return require $file;
}

function opcache_load_revalidated_soft(string $file): mixed
{
    static $validate, $freq;

    if (is_null($validate)) {
        $validate = (int)ini_get('opcache.validate_timestamps');
        $freq = (int)ini_get('opcache.revalidate_freq');
    }

    ini_set('opcache.validate_timestamps', 1);
    ini_set('opcache.revalidate_freq', 1);

    try {
        $result = require $file;
    } finally {
        // Restore
        ini_set('opcache.validate_timestamps', $validate);
        ini_set('opcache.revalidate_freq', $freq);
    }

    return $result;
}

function getRelativePath(string $from, string $to, bool $useReal = true): string
{
    $f = $useReal ? realpath($from) : normalpath($from);
    if ($f === false) {
        throw new LightnaException('Path "' . $from . '" doesn\'t exist');
    }
    $t = $useReal ? realpath($to) : normalpath($to);
    if ($t === false) {
        throw new LightnaException('Path "' . $to . '" doesn\'t exist');
    }

    $f = rtrim($f, '/');
    $t = rtrim($t, '/');
    $fromParts = explode('/', $f);
    $toParts = explode('/', $t);
    $length = min(count($fromParts), count($toParts));
    $commonBaseLength = 0;

    for ($i = 0; $i < $length; $i++) {
        if ($fromParts[$i] === $toParts[$i]) {
            $commonBaseLength++;
        } else {
            break;
        }
    }

    $upLevels = count($fromParts) - $commonBaseLength;
    $relativePath = str_repeat('../', $upLevels) . implode('/', array_slice($toParts, $commonBaseLength));

    return rtrim($relativePath === '' ? './' : $relativePath, '/')
        . ($useReal && is_dir($to) ? '/' : '');
}

function normalpath(string $path): string
{
    if ($path[0] !== '/') {
        throw new LightnaException('Path "' . $path . '" is not a valid absolute path');
    }
    do {
        $path = preg_replace(['~/{2,}~', '~^\./~', '~/\./~'], ['/', '', '/'], $path, -1, $count);
    } while ($count);
    do {
        $path = preg_replace('~/(?!\.\.)[^/]+/\.\./~', '/', $path, -1, $count);
    } while ($count);

    return $path;
}

function array_filter_recursive(array $array, ?callable $cb): array
{
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = array_filter_recursive($v, $cb);
        } else {
            if (!$cb($k, $v)) {
                unset($array[$k]);
            }
        }
    }

    return $array;
}

function array_is_fields_changed(array $fields, array $original, array $updated): bool
{
    foreach ($fields as $field) {
        if (($original[$field] ?? null) !== ($updated[$field] ?? null)) {
            return true;
        }
    }

    return false;
}
