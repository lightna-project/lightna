<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\App\Config as AppConfig;

class ObjectManager
{
    protected static AppConfig $config;
    protected static array $schema;
    protected static array $extended = [];
    protected static array $instances;
    protected static array $producers = [];
    protected static bool $allowConfig = true;

    public static function init(): void
    {
        if (!class_exists(ObjectA::class)) {
            throw new Exception(ObjectA::class . ' needs to be defined');
        }
        static::$schema = require Bootstrap::getBuildDir() . 'object/schema.php';
        static::$extended = require Bootstrap::getBuildDir() . 'object/extended.php';
        static::$producers['Lightna'] = [static::class, 'producer'];
        static::$config = getobj(AppConfig::class);

        if (TEST_MODE) {
            static::initTestMode();
        }
    }

    protected static function produce(string $className, $data = []): ?object
    {
        foreach (static::$producers as $producer) {
            if ($object = call_user_func($producer, $className, $data)) {
                return $object;
            }
        }

        throw new Exception('Class ' . $className . ' not found by ObjectManager');
    }

    protected static function producer(string $className, $data = []): ?object
    {
        if (!isset(static::$schema[$className])) {
            return null;
        }

        $name = static::$extended[$className] ?? $className;
        $schema = static::$schema[$className];

        /** @var ObjectA $instance */
        $instance = new $name($schema['p']);

        if (count($data) === 0 && isset($schema['data'])) {
            $data = $schema['data'];
        }
        $instance->initialize($data);

        return $instance;
    }

    public static function new(string $className, array $data = []): object
    {
        return static::produce($className, $data);
    }

    public static function get(string $className, array $data = []): object
    {
        if (isset(static::$instances[$className])) {
            return static::$instances[$className];
        }

        return static::$instances[$className] = static::new($className, $data);
    }

    public static function getConfigValue(string $path): mixed
    {
        return static::$allowConfig ? static::$config->get($path) : null;
    }

    public static function setProducer(string $name, callable $producer): void
    {
        static::$producers[$name] = $producer;
    }

    protected static function initTestMode(): void
    {
        if (TEST_MODE === 'unit') {
            static::$allowConfig = false;
            static::$extended = [];
        }
    }

    public static function getClassSchema(string $className): ?array
    {
        if (!TEST_MODE) {
            throw new Exception('ObjectManager::getClassSchema is allowed only in TEST_MODE');
        }

        return isset(static::$schema[$className]) ? static::$schema[$className]['p'] : null;
    }
}
