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
    protected static string $EDITION;
    protected static string $BUILD_DIR;
    protected static bool $autoloadRegistered = false;
    protected static array $config;

    public static function declaration(): void
    {
        require_once __DIR__ . '/lib/common.php';
        static::registerErrorHandler();

        define('LIGHTNA_AREAS', ['frontend', 'backend']);
        define('LIGHTNA_AREA', php_sapi_name() === 'cli' ? 'backend' : 'frontend');
        static::defineEdition();
        static::defineConfig();
        static::defineBuild();

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
                if (!(error_reporting() & $errNo)) {
                    return true;
                }

                throw new \ErrorException($errMsg, 0, $errNo, $errFile, $errLine);
            },
            E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED
        );
    }

    protected static function defineConfig(): void
    {
        if (static::getCompilerMode() === 'none') {
            if (static::isConfigApplyCommand()) {
                static::loadUnappliedAreaConfig();
            } else {
                static::loadAreaConfig();
            }
        } else {
            static::loadCompilerConfig();
        }
    }

    protected static function isConfigApplyCommand(): bool
    {
        return PHP_SAPI === 'cli' && ($_SERVER['argv'][1] ?? '') === 'config.apply';
    }

    protected static function loadUnappliedAreaConfig(): void
    {
        static::loadCompilerConfig();
        static::defineBuild();
        static::$config = static::getUnappliedAreaConfig(LIGHTNA_AREA);
    }

    public static function getUnappliedAreaConfig(string $area): array
    {
        $config = merge(
            opcache_load_revalidated(static::$BUILD_DIR . 'config/' . $area . '.php'),
            opcache_load_revalidated(static::getEditionConfigFile('config.php')),
            opcache_load_revalidated(static::getEditionConfigFile('env.php')),
            static::getAdditionalConfig(),
        );

        static::applyConfigDefaults($config);

        return $config;
    }

    protected static function applyConfigDefaults(array &$config): void
    {
        if ($defaultStorage = $config['default']['storage'] ?? '') {
            foreach ($config['entity'] as &$entity) {
                $entity['storage'] ??= $defaultStorage;
            }
        }
    }

    protected static function loadCompilerConfig(): void
    {
        static::$config = merge(
            opcache_load_revalidated(static::getEditionConfigFile('config.php')),
            opcache_load_revalidated(static::getEditionConfigFile('env.php')),
        );

        static::$config = merge(static::$config, static::getAdditionalConfig());
        static::$config['enabled_modules'] = static::getEnabledModules();
    }

    public static function getAdditionalConfig(): array
    {
        return [
            'lightna_dir' => static::getEntryRelatedPath(__DIR__ . '/../') . '/',
            'edition_dir' => static::getEntryRelatedPath(static::$config['compiler_dir'] . '/' . static::$EDITION),
            'edition_asset_dir' => static::getEntryRelatedPath(static::$config['asset_dir'] . '/' . static::$EDITION),
        ];
    }

    protected static function getEntryRelatedPath(string $to): string
    {
        return getRelativePath(
            LIGHTNA_ENTRY,
            $to[0] === '/' ? $to : LIGHTNA_ENTRY . $to,
            false,
        );
    }

    public static function getEditionConfigFile(string $fileName): string
    {
        $paths = [
            'edition/' . static::$EDITION . '/' . $fileName,
            'edition/main/' . $fileName,
        ];

        foreach ($paths as $path) {
            $path = LIGHTNA_ENTRY . $path;
            if (is_file($path)) {
                return $path;
            }
        }

        throw new Exception('Config file "' . $fileName . '" not found.');
    }

    protected static function loadAreaConfig(): void
    {
        static::$config = opcache_load_revalidated_soft(
            static::getAppliedConfigDir() . LIGHTNA_AREA . '.php',
        );
    }

    public static function getAppliedConfigDir(): string
    {
        return LIGHTNA_ENTRY . 'edition/' . static::$EDITION . '/applied/';
    }

    public static function getConfig(): array
    {
        return static::$config;
    }

    public static function autoload(): void
    {
        require_once LIGHTNA_ENTRY . static::$config['lightna_dir'] . 'App/Autoloader.php';

        Autoloader::setClasses(require static::$BUILD_DIR . 'object/map.php');

        if (!static::$autoloadRegistered) {
            spl_autoload_register([Autoloader::class, 'loadClass'], true, true);
            static::$autoloadRegistered = true;
        }
    }

    public static function maintenance(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        require __DIR__ . '/Maintenance.php';
        (new Maintenance())->process();
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
        $edition = static::getEdition();
        $folder = static::$COMPILER_MODE === 'default' ? 'building' : 'build';

        static::$BUILD_DIR = LIGHTNA_ENTRY . static::$config['compiler_dir'] . "/$edition/$folder/";
        static::validateBuild();
    }

    protected static function defineEdition(): void
    {
        static::$EDITION = $_SERVER['LIGHTNA_EDITION'] ?? 'main';
    }

    public static function getEdition(): string
    {
        return static::$EDITION;
    }

    public static function getBuildDir(): string
    {
        return static::$BUILD_DIR;
    }

    protected static function validateBuild(): void
    {
        global $argv;

        if (
            LIGHTNA_AREA === 'backend'
            && str_starts_with($cmd = $argv[1] ?? '', 'build.')
            && $cmd !== 'build.compile'
            && !is_dir(static::$BUILD_DIR)
        ) {
            echo cli_warning("Build folder not found. Have you run build.compile first?\n");
            exit(1);
        }
    }

    public static function getEnabledModules(): array
    {
        if (!isset(static::$config['enabled_modules'])) {
            static::$config['enabled_modules'] = static::loadEnabledModules();
        }

        return static::$config['enabled_modules'];
    }

    protected static function loadEnabledModules(): array
    {
        $enabled = merge(
            ['lightna/' . basename(static::$config['lightna_dir'])],
            static::$config['modules']['enabled'],
        );

        $enabledModules = [];
        foreach ($enabled as $path) {
            $config = static::getModuleConfig($path);
            $name = $config['name'];

            if ($enabledModules[$name] ?? false) {
                throw new Exception("Cannot redeclare module \"$name\"");
            }

            $enabledModules[$name] = $config;
        }

        static::validateModuleRequirements($enabledModules);

        return $enabledModules;
    }

    protected static function getModuleConfig(string $path): array
    {
        foreach (static::$config['modules']['pool'] as $pool) {
            $configFile = LIGHTNA_ENTRY . $pool . "/$path/module.yaml";
            if (!is_file($configFile)) continue;

            $config = yaml_parse_file($configFile);
            if (!isset($config['name'])) {
                throw new Exception("Module name is not defined in \"$configFile\"");
            }
            if (!isset($config['namespace'])) {
                throw new Exception("Module namespace is not defined in \"$configFile\"");
            }

            $config['path'] = "$pool/$path";

            return $config;
        }

        throw new Exception("Module \"$path\" not found");
    }

    protected static function validateModuleRequirements(array $modules): void
    {
        foreach ($modules as $name => $module) {
            if (!isset($module['require'])) continue;

            $modulePosition = array_search($name, array_keys($modules));
            foreach ($module['require'] as $requirement) {
                $requirementPosition = array_search($requirement, array_keys($modules));
                if ($requirementPosition === false) {
                    throw new Exception("The requirement \"$requirement\" for \"$name\" was not found among the modules.");
                }
                if ($requirementPosition > $modulePosition) {
                    throw new Exception("The module \"$name\" should be declared after \"$requirement\" in modules sequence, as it depends on it.");
                }
            }
        }
    }

    public static function getAssetDir(): string
    {
        return LIGHTNA_ENTRY . static::$config['asset_dir'] . '/';
    }

    public static function getEditionAssetDir(): string
    {
        return static::getAssetDir() . static::$EDITION . '/';
    }
}
