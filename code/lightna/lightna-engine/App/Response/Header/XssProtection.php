<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

/**
 * Adds an X-XSS-Protection header to HTTP responses to safeguard against cross-site scripting.
 */
class XssProtection extends AbstractHeader
{
    /** Matches IE 8 browsers */
    public const USER_AGENT_IE_8 = 'MSIE 8';
    /** Value for browsers except IE 8 */
    public const VALUE_DEFAULT = '1; mode=block';
    /** Value for IE 8 */
    public const VALUE_IE_8 = '0';

    protected string $name = 'X-XSS-Protection';

    /** @noinspection PhpUnused */
    protected function defineValue(): void
    {
        $this->value = str_contains($this->getHttpUserAgent(), self::USER_AGENT_IE_8)
            ? self::VALUE_IE_8
            : self::VALUE_DEFAULT;
    }

    protected function getHttpUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
}
