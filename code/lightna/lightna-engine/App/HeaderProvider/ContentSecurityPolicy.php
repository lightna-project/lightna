<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds a Content-Security-Policy header to HTTP responses to safeguard against cross-site scripting.
 */
class ContentSecurityPolicy extends AbstractHeaderProvider implements HeaderProviderDynamicInterface
{

    protected Dynamic\ContentSecurityPolicySource $csp;

    public function __construct()
    {
        $csp = new Dynamic\ContentSecurityPolicySource();
        $this->setSource($csp);
    }

    public function setSource(Dynamic\SourceInterface $source): void
    {
        $this->csp = $source;
    }

    public function getSource(): Dynamic\SourceInterface
    {
        return $this->csp;
    }

    public function addNoncePolicy(): void
    {
        $this->csp->addNoncePolicy();
    }

    public function addPolicy(string $directive, string $value): void
    {
        $this->csp->addPolicy($directive, $value);
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
