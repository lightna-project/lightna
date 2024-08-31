<?php

declare(strict_types=1);

use Lightna\Engine\App\Escaper;
use Lightna\Engine\App\Layout;

function escape(mixed $var, ?string $method = 'html'): string
{
    static $escaper;
    $escaper ??= getobj(Escaper::class);

    return $escaper->escape($var, $method);
}

function escape_html(string $var): string
{
    return htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}

function block(string $blockName = '', array $vars = []): void
{
    /** @var Layout $layout */
    static $layout;
    $layout ??= getobj(Layout::class);

    $layout->block($blockName, $vars);
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
