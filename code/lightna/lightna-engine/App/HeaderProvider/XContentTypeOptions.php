<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds an X-Content-Type-Options header to HTTP responses to safeguard against MIME-sniffing.
 */
class XContentTypeOptions extends AbstractHeaderProvider
{
    protected string $headerName = 'X-Content-Type-Options';
    protected string $headerValue = 'nosniff';
}
