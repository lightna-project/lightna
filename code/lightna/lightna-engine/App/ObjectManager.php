<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\App\Config as AppConfig;

class ObjectManager
{
    protected static AppConfig $config;
    protected static array $schema;
    protected static array $extended;
    protected static array $instances;

    public static function init(): void
    {
        if (!class_exists(ObjectA::class)) {
            throw new Exception(ObjectA::class . ' needs to be defined');
        }
        static::$schema = require BUILD_DIR . 'object/schema.php';
        static::$extended = require BUILD_DIR . 'object/extended.php';
        static::$config = getobj(AppConfig::class);
    }

    public static function new(string $className, $data = []): mixed
    {
        static::validateClass($className);
        $name = static::$extended[$className] ?? $className;
        $schema = static::$schema[$className];

        /** @var ObjectA $instance */
        $instance = new $name();
        $instance->construct($schema['p']);

        if (count($data) === 0 && isset($schema['data'])) {
            $data = $schema['data'];
        }
        $instance->initialize($data);

        return $instance;
    }

    public static function get(string $className, $data = []): mixed
    {
        if (isset(static::$instances[$className])) {
            return static::$instances[$className];
        }

        return static::$instances[$className] = static::new($className, $data);
    }

    public static function getConfigValue(string $path): mixed
    {
        return static::$config->get($path);
    }

    public static function validateClass(string $className): void
    {
        if (!isset(static::$schema[$className])) {
            throw new Exception('Class ' . $className . ' not found by ObjectManager');
        }
    }
}
