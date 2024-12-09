<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Config extends ObjectA
{
    protected Build $build;
    protected array $config;
    protected array $map;

    /** @noinspection PhpUnused */
    protected function defineConfig(): void
    {
        $this->config = $this->build->getAppConfig();
        $this->map();
    }

    /** @noinspection PhpUnused */
    protected function defineMap(): void
    {
        $this->map();
    }

    public function get(string $path = ''): mixed
    {
        return $this->map[$path] ?? null;
    }

    protected function map($config = null, string $location = ''): void
    {
        $config ??= $this->config;
        foreach ($config as $key => &$value) {
            $path = $location . ($location ? '/' : '') . $key;
            $this->map[$path] = &$value;
            if (is_array($value)) {
                $this->map($value, $path);
            }
        }
    }
}
