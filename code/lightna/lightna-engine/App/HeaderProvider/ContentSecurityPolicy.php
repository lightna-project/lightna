<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds a Content-Security-Policy header to HTTP responses to safeguard against cross-site scripting.
 */
class ContentSecurityPolicy extends AbstractHeaderProvider
{

    protected ContentSecurityPolicy\Csp $csp;

    public function __construct()
    {
        $this->csp = new ContentSecurityPolicy\Csp();

        $nonceSource = new ContentSecurityPolicy\NonceSource();
        $this->csp->setNonceSource($nonceSource);
    }

    public function addNoncePolicy(): void
    {
        $this->csp->addNoncePolicy();
    }

    public function addPolicy(string $directive, string $value): void
    {
        $this->csp->addPolicy($directive, $value);
    }

    public function setReportOnly($value): void
    {
        $this->csp->setReportOnly($value);
    }

    public function getName(): string
    {
        return $this->csp->getName();
    }

    public function getValue(): string
    {
        return $this->csp->getValue();
    }
}
