<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

class Maintenance extends ObjectA
{
    protected array $config;

    public function process(): void
    {
        $this->config = Bootstrap::getConfig()['maintenance'] ?? [];

        if (!$this->config['enabled'] || $this->canBypass()) {
            return;
        }

        try {
            $this->render();
        } catch (Throwable $e) {
            error500('Initialization error', $e);
        }
    }

    protected function canBypass(): bool
    {
        $cookieConfig = $this->config['bypass']['cookie'];
        if (!($name = $cookieConfig['name']) || !($value = $cookieConfig['value'])) {
            return false;
        }

        return ($_COOKIE[$name] ?? '') === $value;
    }

    #[NoReturn]
    protected function render(): void
    {
        http_response_code(503);
        require $this->getTemplate();
        exit;
    }

    protected function getTemplate(): string
    {
        $fallback = [];
        if ($dir = ($this->config['dir'] ?? '')) {
            if (!empty($this->config['vary_name'])) {
                $name = $this->getVaryValue($this->config['vary_name']);
                $fallback[] = LIGHTNA_ENTRY . $dir . "/$name.phtml";
            }
            $fallback[] = LIGHTNA_ENTRY . $dir . '/default.phtml';
        }

        foreach ($fallback as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return __DIR__ . '/../template/error/503.phtml';
    }

    protected function getVaryValue(string $name): string
    {
        $name = getenv($name);

        return $name ?: $_SERVER[$name];
    }
}
