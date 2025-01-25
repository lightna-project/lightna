<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;

class Escaper extends ObjectA
{
    public function escape(mixed $var, ?string $method = 'html'): string
    {
        if (($escaped = $this->escapeValue($var, $method)) === null) {
            throw new Exception('Unknown escape method "' . $method . '"');
        }

        return $escaped;
    }

    protected function escapeValue(mixed $var, ?string $method = 'html'): ?string
    {
        return match ($method) {
            null, 'html' => $this->escapeHtml((string)$var),
            'json-js' => $this->escapeJsonJs($var),
            'json-js-pretty' => $this->escapeJsonJsPretty($var),
            'json-html' => $this->escapeJsonHtml($var),
            'url-param' => $this->escapeUrlParam((string)$var),
            default => null,
        };
    }

    protected function escapeHtml(string $var): string
    {
        return htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    protected function escapeJsonJs(mixed $var): string
    {
        return json($var);
    }

    protected function escapeJsonJsPretty(mixed $var): string
    {
        return json_pretty($var);
    }

    protected function escapeJsonHtml(mixed $var): string
    {
        return $this->escapeHtml(json($var));
    }

    protected function escapeUrlParam(string $var): string
    {
        return urlencode($var);
    }
}
