<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Config extends ObjectA
{
    protected Compiled $compiled;
    protected array $config;
    protected array $map;

    protected function init(): void
    {
        $this->config = $this->compiled->loadAppConfig();
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
