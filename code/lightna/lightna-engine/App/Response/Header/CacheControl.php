<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

/**
 * Adds a Cache-Control header to HTTP responses consisting of a set of directives that allow you to specify when/how
 * to cache a response and for how long.
 */
class CacheControl extends AbstractHeader
{
    protected string $name = 'Cache-Control';
    protected string $value = 'max-age=0, no-cache, no-store, must-revalidate, private';
}
