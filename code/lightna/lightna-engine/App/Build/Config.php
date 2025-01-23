<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Build;

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Build;
use Lightna\Engine\App\Compiler;

class Config extends Build
{
    protected string $dir;
    protected Compiler $compiler;
    protected Build $build;

    public function init(array $data = []): void
    {
        $this->dir = LIGHTNA_ENTRY . 'edition/' . Bootstrap::getEdition() . '/applied/';
    }

    public function apply(): void
    {
        foreach (LIGHTNA_AREAS as $area) {
            $this->save($area, $this->load($area));
        }
    }

    public function load(string $name): array
    {
        $config = merge(
            opcache_load_revalidated($this->compiler->getBuildOrigDir() . 'config/' . $name . '.php'),
            opcache_load_revalidated(Bootstrap::getEditionConfigFile('config.php')),
            opcache_load_revalidated(Bootstrap::getEditionConfigFile('env.php')),
            Bootstrap::getAdditionalConfig(),
        );

        $this::applyDefaults($config);

        return $config;
    }

    public static function applyDefaults(array &$config): void
    {
        if ($defaultStorage = $config['default']['storage'] ?? '') {
            foreach ($config['entity'] as &$entity) {
                $entity['storage'] ??= $defaultStorage;
            }
        }
    }
}
