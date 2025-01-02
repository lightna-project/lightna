<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;

class Bootstrap
{
    /**
     * COMPILER_MODE
     *  "none" - not a compiler run
     *  "default" - compiler creates new build in "building" folder then moves the results into "build" folder on success
     *  "direct" - compiler updates existing build in "build" folder
     */
    protected static string $COMPILER_MODE = 'none';
    protected static bool $autoloadRegistered = false;
    protected static array $config;

    public static function declaration(): void
    {
        require_once __DIR__ . '/lib/common.php';
        static::registerErrorHandler();

        define('LIGHTNA_AREAS', ['frontend', 'backend']);
        define('LIGHTNA_AREA', php_sapi_name() === 'cli' ? 'backend' : 'frontend');
        static::loadConfig();
        static::defineBuild();

        define('LIGHTNA_SRC', static::$config['lightna_dir']);
        define('IS_DEV_MODE', static::$config['mode'] === 'dev');
        define('IS_PROD_MODE', static::$config['mode'] === 'prod');
        !defined('TEST_MODE') && define('TEST_MODE', null);
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
        if (static::getCompilerMode() === 'none') {
            static::loadAreaConfig();
        } else {
            static::loadCompilerConfig();
        }
    }

    protected static function loadCompilerConfig(): void
    {
        static::$config = merge(
            opcache_load_revalidated(LIGHTNA_ENTRY . 'config.php'),
            opcache_load_revalidated(LIGHTNA_ENTRY . 'env.php'),
            ['lightna_dir' => getRelativePath(LIGHTNA_ENTRY, __DIR__ . '/../')],
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

    public static function getCompilerMode(): string
    {
        return static::$COMPILER_MODE;
    }

    public static function setCompilerMode(string $mode): void
    {
        if (!in_array($mode, ['none', 'default', 'direct'])) {
            throw new Exception('Unknown compiler mode');
        }

        static::$COMPILER_MODE = $mode;
    }

    protected static function defineBuild(): void
    {
        $folder = static::$COMPILER_MODE === 'default' ? 'building' : 'build';
        define(
            'BUILD_DIR',
            LIGHTNA_ENTRY . static::$config['compiler_dir'] . '/' . $folder . '/',
        );

        static::validateBuild();
    }

    protected static function validateBuild(): void
    {
        global $argv;

        if (
            LIGHTNA_AREA === 'backend' &&
            str_starts_with($c = $argv[1] ?? '', 'build.') &&
            $c !== 'build.compile' &&
            !is_dir(BUILD_DIR)
        ) {
            echo cli_warning("Build folder not found. Have you run build.compile first?\n");
            exit(1);
        }
    }
}
