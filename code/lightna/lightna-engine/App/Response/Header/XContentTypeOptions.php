<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

/**
 * Adds an X-Content-Type-Options header to HTTP responses to safeguard against MIME-sniffing.
 */
class XContentTypeOptions extends AbstractHeader
{
    protected string $name = 'X-Content-Type-Options';
    protected string $value = 'nosniff';
}
