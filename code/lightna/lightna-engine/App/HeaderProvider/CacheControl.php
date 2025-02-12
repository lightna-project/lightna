<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds a Cache-Control header to HTTP responses consisting of a set of directives that allow you to specify when/how
 * to cache a response and for how long.
 */
class CacheControl extends AbstractHeaderProvider
{

    /**
     * cache-control header name
     *
     * @var string
     */
    protected string $headerName = 'Cache-Control';

    /**
     * cache-control header value
     *
     * @var string
     */
    protected string $headerValue = 'max-age=0, no-cache, no-store, must-revalidate, private';
}
