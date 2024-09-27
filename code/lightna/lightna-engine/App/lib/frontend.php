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

/**
 * Return is always empty string but declared return type "string" allows to use <?=
 * To get html see function blockhtml
 */
function block(string $blockName = '', array $vars = []): string
{
    /** @var Layout $layout */
    static $layout;
    $layout ??= getobj(Layout::class);

    return $layout->block($blockName, $vars);
}

function blockhtml(string $blockName = '', array $vars = []): string
{
    ob_start();
    block($blockName, $vars);

    return ob_get_clean();
}
