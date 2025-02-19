<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool;

/**
 * Adds an X-XSS-Protection header to HTTP responses to safeguard against cross-site scripting.
 */
class XssProtection extends AbstractHeader
{
    protected string $headerName = 'X-XSS-Protection';

    /** Matches IE 8 browsers */
    public const IE_8_USER_AGENT = 'MSIE 8';

    /** Value for browsers except IE 8 */
    public const HEADER_ENABLED = '1; mode=block';

    /** Value for IE 8 */
    public const HEADER_DISABLED = '0';

    /**
     * Header value. Must be disabled for IE 8.
     */
    protected function init(array $data = []): void
    {
        $this->headerValue = !str_contains($this->getHttpUserAgent(), self::IE_8_USER_AGENT)
            ? self::HEADER_ENABLED
            : self::HEADER_DISABLED;

        parent::init($data);
    }

    protected function getHttpUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
}
