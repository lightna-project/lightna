<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Expression;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Context;

class Config extends ObjectA
{
    protected Database $db;
    protected Context $context;
    protected array $modules;
    protected array $stores;
    /** @AppConfig(project/src_dir) */
    protected string $src;
    protected array $defaultConfig;
    protected array $configResult;

    protected function init(): void
    {
        $this->src = LIGHTNA_ENTRY . $this->src . '/';
        $this->initModules();
        $this->initStores();
        $this->initDefaultConfig();
    }

    protected function initModules(): void
    {
        $allModules = $this->getAllModules();
        $enabledModules = $this->getEnabledModules();
        foreach ($enabledModules as $name) {
            $this->modules[$name] = $allModules[$name];
        }
    }

    protected function getAllModules(): array
    {
        $modules = [];

        $cLock = json_decode(file_get_contents($this->src . 'composer.lock'));
        foreach ($cLock->packages as $package) {
            if ($package->type === 'magento2-module') {
                $modules = merge($modules, $this->getPackageModule($package));
            }
        }

        foreach (glob($this->src . 'app/code/*/*') as $folder) {
            $modules = merge($modules, $this->getAppCodeModule($folder));
        }

        return $modules;
    }

    protected function getPackageModule(\stdClass $package): array
    {
        $autoloadPsr4 = (array)$package->autoload->{'psr-4'};
        $namespace = array_key_first($autoloadPsr4);
        $name = str_replace('\\', '_', trim($namespace, '\\'));
        $path = array_shift($autoloadPsr4);
        $fullPath = 'vendor/' . $package->name . (empty($path) ? '' : '/' . trim($path, '/'));

        return [$name => $fullPath];
    }

    protected function getAppCodeModule(string $folder): array
    {
        $parts = array_slice(explode('/', $folder), -2);
        $name = implode('_', $parts);

        return [$name => 'app/code/' . implode('/', $parts)];
    }

    protected function getEnabledModules(): array
    {
        $modules = array_filter((require $this->src . 'app/etc/config.php')['modules']);

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
            $file = $this->src . $folder . '/etc/config.xml';
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
        if (!isset($this->configResult)) {
            $this->load();
        }

        return $this->configResult;
    }

    protected function load(): void
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
            require $this->src . 'app/etc/config.php',
            require $this->src . 'app/etc/env.php',
        );
    }
}
