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
            null, 'html' => escape_html((string)$var),
            'json-js' => json($var),
            'json-html' => escape_html(json($var)),
            'url-param' => urlencode($var),
            default => null,
        };
    }
}
