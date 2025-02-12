<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds a Content-Security-Policy header to HTTP responses to safeguard against cross-site scripting.
 */
class ContentSecurityPolicy extends AbstractHeaderProvider
{

    /**
     * content-security-policy header name
     *
     * @var string
     */
    protected string $headerName = 'Content-Security-Policy';

    /**
     * content-security-policy header value
     *
     * @var string
     */
    protected string $headerValue;

    /**
     * @param string $value
     */
    public function __construct($value = 'default-src \'self\'')
    {
        $this->headerValue = $value;
    }
}
