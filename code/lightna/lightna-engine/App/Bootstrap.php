<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;

class Bootstrap
{
    protected static bool $autoloadRegistered = false;
    protected static array $config;

    public static function declaration(): void
    {
        require_once __DIR__ . '/lib/common.php';
        static::registerErrorHandler();

        define('LIGHTNA_AREAS', ['frontend', 'backend']);
        define('LIGHTNA_AREA', php_sapi_name() === 'cli' ? 'backend' : 'frontend');
        static::loadConfig();

        define('LIGHTNA_SRC', static::$config['src_dir']);
        define(
            'BUILD_DIR',
            LIGHTNA_ENTRY . static::$config['compiler']['dir']
            . (defined('IS_COMPILER')
                ? '/building/'
                : '/build/'),
        );
        define("IS_DEV_MODE", static::$config['mode'] === 'dev');
        define("IS_PROD_MODE", static::$config['mode'] === 'prod');
        define(
            'IS_PROGRESSIVE_RENDERING',
            (static::$config['progressive_rendering'] ?? false)
            && LIGHTNA_AREA === 'frontend'
            && $_SERVER['REQUEST_METHOD'] === 'GET',
        );

        require_once __DIR__ . '/lib/' . LIGHTNA_AREA . '.php';
    }

    protected static function registerErrorHandler(): void
    {
        set_error_handler(
            function ($errNo, $errMsg, $errFile, $errLine) {
                throw new \ErrorException($errMsg, 0, $errNo, $errFile, $errLine);
            },
            E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED
        );
    }

    protected static function loadConfig(): void
    {
        if (defined('IS_COMPILER')) {
            static::loadCompilerConfig();
        } else {
            static::loadAreaConfig();
        }
    }

    protected static function loadCompilerConfig(): void
    {
        static::$config = merge(
            opcache_load_revalidated(LIGHTNA_ENTRY . 'config.php'),
            opcache_load_revalidated(LIGHTNA_ENTRY . 'env.php'),
            ['src_dir' => getRelativePath(LIGHTNA_ENTRY, __DIR__ . '/../')],
        );
    }

    protected static function loadAreaConfig(): void
    {
        $configFile = LIGHTNA_ENTRY . 'config/' . LIGHTNA_AREA . '.php';
        $config = require $configFile;
        $version = opcache_load_revalidated_soft(LIGHTNA_ENTRY . 'config/version.php');

        if ($version !== $config['version']) {
            $config = opcache_load_revalidated($configFile);

            if ($version !== $config['version']) {
                throw new Exception('Config version mismatch');
            }
        }

        static::$config = $config['value'];
    }

    public static function getConfig(): array
    {
        return static::$config;
    }

    public static function autoload(): void
    {
        require_once LIGHTNA_ENTRY . LIGHTNA_SRC . 'App/Autoloader.php';

        Autoloader::setClasses(require BUILD_DIR . 'object/map.php');

        if (!static::$autoloadRegistered) {
            spl_autoload_register([Autoloader::class, 'loadClass'], true, true);
            static::$autoloadRegistered = true;
        }
    }

    public static function objectManager(): void
    {
        ObjectManager::init();
    }

    public static function unregister(): void
    {
        set_error_handler(function () {
            return false;
        });
    }
}
