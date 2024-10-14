<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Bootstrap
{
    protected static bool $autoloadRegistered = false;

    public static function declaration(array $config): void
    {
        set_error_handler(
            function ($errNo, $errMsg, $errFile, $errLine) {
                throw new \ErrorException($errMsg, 0, $errNo, $errFile, $errLine);
            },
            E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED
        );

        $env = require LIGHTNA_ENTRY . 'env.php';

        define('LIGHTNA_AREAS', ['frontend', 'backend']);
        define('LIGHTNA_AREA', php_sapi_name() === 'cli' ? 'backend' : 'frontend');
        define('LIGHTNA_SRC', $config['src_dir'] . '/');
        define('COMPILED_DIR', LIGHTNA_ENTRY . $config['compiler']['dir'] . '/build/');
        define("IS_DEV_MODE", $env['mode'] === 'dev');
        define("IS_PROD_MODE", $env['mode'] === 'prod');
        define(
            'IS_PROGRESSIVE_RENDERING',
            ($env['progressive_rendering'] ?? false)
            && LIGHTNA_AREA === 'frontend'
            && $_SERVER['REQUEST_METHOD'] === 'GET',
        );

        require __DIR__ . '/lib/common.php';
        require __DIR__ . '/lib/' . LIGHTNA_AREA . '.php';
    }

    public static function autoload(): void
    {
        require_once LIGHTNA_ENTRY . LIGHTNA_SRC . 'App/Autoloader.php';

        Autoloader::setClasses(require COMPILED_DIR . 'object/map.php');

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
