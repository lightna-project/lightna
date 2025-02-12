<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds an X-Content-Type-Options header to HTTP responses to safeguard against MIME-sniffing.
 */
class XContentTypeOptions extends AbstractHeaderProvider
{

    /**
     * x-content-type-options Header name
     *
     * @var string
     */
    protected string $headerName = 'X-Content-Type-Options';

    /**
     * x-content-type-options header value
     *
     * @var string
     */
    protected string $headerValue = 'nosniff';
}
