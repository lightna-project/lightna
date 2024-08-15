<?php

declare(strict_types=1);

use Lightna\Engine\App\Layout;

function escape(mixed $var, ?string $method = 'html'): string
{
    if ($method === null || $method === 'html') {
        return escape_html((string)$var);
    } elseif ($method === 'json-js') {
        return json($var);
    } elseif ($method === 'json-html') {
        return escape_html(json($var));
    } elseif ($method === 'url-param') {
        return urlencode($var);
    } else {
        throw new Exception('Unknown escape method "' . $method . '"');
    }
}

function escape_html(string $var): string
{
    return htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}

function block(string $blockName = ''): void
{
    /** @var Layout $layout */
    static $layout;
    $layout ??= getobj(Layout::class);

    $layout->block($blockName);
}

function template(string $template, array $vars = []): void
{
    /** @var Layout $layout */
    static $layout;
    $layout ??= getobj(Layout::class);

    $layout->template($template, $vars);
}

function templateHtml(string $template, array $vars = []): string
{
    ob_start();
    template($template, $vars);

    return ob_get_clean();
}
