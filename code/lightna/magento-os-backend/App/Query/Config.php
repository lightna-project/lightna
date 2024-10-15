<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Exception;
use Laminas\Db\Sql\Expression;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use stdClass;

class Config extends ObjectA
{
    protected Database $db;
    protected Context $context;
    protected array $modules;
    protected array $stores;
    /** @AppConfig(project/src_dir) */
    protected string $projectSrc;
    protected array $defaultConfig;
    protected array $configResult;

    protected function init(): void
    {
        $this->projectSrc = LIGHTNA_ENTRY . $this->projectSrc . '/';
        $this->initModules();
        $this->initStores();
        $this->initDefaultConfig();
    }

    protected function initModules(): void
    {
        $allModules = $this->getAllModules();
        $enabledModules = $this->getEnabledModules();
        foreach ($enabledModules as $name) {
            if (!isset($allModules[$name])) {
                throw new Exception("Module source for '{$name}' not resolved");
            }
            $this->modules[$name] = $allModules[$name];
        }
    }

    protected function getAllModules(): array
    {
        return merge(
            $this->getVendorModules(),
            $this->getLocalModules(),
        );
    }

    protected function getVendorModules(): array
    {
        $modules = [];
        foreach ($this->getVendorPackages() as $package) {
            $modules = merge($modules, $this->getPackageModules($package));
        }

        return $modules;
    }

    protected function getVendorPackages(): array
    {
        $lock = json_decode(file_get_contents($this->projectSrc . 'composer.lock'));

        return array_merge($lock->packages, $lock->{'packages-dev'});
    }

    protected function getPackageModules(stdClass $package): array
    {
        $modules = [];
        foreach ($package->autoload->files ?? [] as $file) {
            $src = 'vendor/' . $package->name . '/' . dirname($file);
            if ($this->isModuleFolder($src)) {
                $modules[$this->getModuleName($src)] = $src;
            }
        }

        return $modules;
    }

    protected function getLocalModules(): array
    {
        $modules = [];
        foreach (glob($this->projectSrc . 'app/code/*/*') as $folder) {
            if ($this->isModuleFolder($folder)) {
                $modules = merge($modules, $this->getAppCodeModule($folder));
            }
        }

        return $modules;
    }

    protected function isModuleFolder(string $folder): bool
    {
        $folder = str_starts_with($folder, '/') ? $folder : $this->projectSrc . $folder;

        return file_exists($folder . '/etc/module.xml');
    }

    protected function getModuleName(string $src): string
    {
        $src = str_starts_with($src, '/') ? $src : $this->projectSrc . $src;
        $configXml = simplexml_load_file(
            $src . '/etc/module.xml',
        );

        return (string)$configXml->module->attributes()->name;
    }

    protected function getAppCodeModule(string $folder): array
    {
        $name = $this->getModuleName($folder);
        $parts = array_slice(explode('/', $folder), -2);

        return [$name => 'app/code/' . implode('/', $parts)];
    }

    protected function getEnabledModules(): array
    {
        $modules = array_filter((require $this->projectSrc . 'app/etc/config.php')['modules']);

        return array_keys($modules);
    }

    protected function initStores(): void
    {
        $select = $this->db
            ->select(['s' => 'store'])
            ->join(['w' => 'store_website'], 'w.website_id = s.website_id', ['website_code' => 'code']);

        $this->stores = $this->db->fetch(
            $select,
            'store_id'
        );
    }

    protected function initDefaultConfig(): void
    {
        $final = [];
        foreach ($this->modules as $folder) {
            $file = $this->projectSrc . $folder . '/etc/config.xml';
            if (!is_file($file)) {
                continue;
            }
            $config = simplexml_load_file($file);
            if (!$config->default) {
                continue;
            }
            $config = json_decode(json_encode($config->default), true);
            $final = merge($final, $config);
        }

        $this->defaultConfig = $final;
    }

    public function get(): array
    {
        return $this->configResult;
    }

    public function getValue(string $path): mixed
    {
        return array_path_get($this->configResult, $path);
    }

    protected function defineConfigResult(): void
    {
        $this->configResult = merge(
            $this->defaultConfig,
            $this->getDatabaseConfig(),
            $this->getEnvironmentConfig(),
            $this->getEtcConfig(),
        );
    }

    protected function getDatabaseConfig(): array
    {
        $select = $this->db->select(['c' => 'core_config_data']);
        $where = $select->where;
        $where
            ->nest()
            ->equalTo('c.scope', 'stores')->and->equalTo('scope_id', $this->context->scope)
            ->unnest()->or->nest()
            ->equalTo('c.scope', 'websites')->and->equalTo('scope_id', $this->stores[$this->context->scope]['website_id'])
            ->unnest()->or->nest()
            ->equalTo('c.scope_id', 0);

        $select->order(new Expression('field(c.scope, "default", "websites", "stores")'));

        $config = [];
        foreach ($this->db->fetch($select) as $row) {
            $config[$row['path']] = $row['value'];
        }

        return array_expand_keys($config, '/');
    }

    protected function getEnvironmentConfig(): array
    {
        $config = [];
        $rxOrder = [
            '~^CONFIG__DEFAULT__(.+)$~',
            '~^CONFIG__WEBSITES__' . strtoupper($this->stores[$this->context->scope]['website_code']) . '__(.+)$~',
            '~^CONFIG__STORES__' . strtoupper($this->stores[$this->context->scope]['code']) . '__(.+)$~',
        ];
        $convertKey = function ($k) {
            return str_replace('__', '/', strtolower($k));
        };

        foreach ($rxOrder as $rx) {
            foreach (getenv() as $k => $v) {
                $ms = null;
                if (preg_match($rx, $k, $ms)) {
                    $config[$convertKey($ms[1])] = $v;
                }
            }
        }

        return array_expand_keys($config, '/');
    }

    protected function getEtcConfig(): array
    {
        return merge(
            require $this->projectSrc . 'app/etc/config.php',
            require $this->projectSrc . 'app/etc/env.php',
        );
    }
}
