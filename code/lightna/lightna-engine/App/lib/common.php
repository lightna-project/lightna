<?php

declare(strict_types=1);

use Lightna\Engine\App\I18n;
use Lightna\Engine\App\ObjectManager;

/**
 * @psalm-template InstanceType
 * @psalm-param class-string<InstanceType> $type
 * @psalm-return InstanceType
 */
function getobj(string $type, array $data = []): mixed
{
    return ObjectManager::get($type, $data);
}

/**
 * @psalm-template InstanceType
 * @psalm-param class-string<InstanceType> $type
 * @psalm-return InstanceType
 */
function newobj(object|string $type, array $data = []): mixed
{
    return ObjectManager::new(
        is_string($type) ? $type : $type::class,
        $data
    );
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
    $parts = explode('/', trim($path, '/'));
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
    $parts = explode('/', trim($path, '/'));
    $lastKey = array_pop($parts);
    $parent = array_path_get($array, implode('/', $parts));

    return is_array($parent) && array_key_exists($lastKey, $parent);
}

function array_path_set(array &$array, string $path, mixed $value): void
{
    $parts = explode('/', trim($path, '/'));
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

function is_instanceof(object $object, string $class): bool
{
    // instanceof must fail if $class not undefined
    if (!class_exists($class)) {
        throw new Exception('Class "' . $class . '" not found');
    }

    return $object instanceof $class;
}

function json(mixed $var): string
{
    return json_encode($var, JSON_UNESCAPED_UNICODE);
}

function phrase(string $phrase): string
{
    return escape(_phrase($phrase));
}

function _phrase(string $phrase): string
{
    /** @var I18n $i18n */
    static $i18n;
    $i18n ??= getobj(I18n::class);

    return $i18n->phrase($phrase);
}
