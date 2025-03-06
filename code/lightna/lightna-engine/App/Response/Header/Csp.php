<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

use Lightna\Engine\App\Security\Csp as SecurityCsp;

/**
 * Adds a Content-Security-Policy header to HTTP responses to safeguard against cross-site scripting.
 */
class Csp extends AbstractHeader
{
    protected SecurityCsp $securityCsp;

    protected string $name = 'Content-Security-Policy';
    protected array $policies = [
        "default-src" => [
            "'self'" => true,
        ],
        "script-src" => [
            "'self'" => true,
            "'strict-dynamic'" => true,
        ],
    ];

    public function setPolicy(string $name, string $value, bool $enabled = true): static
    {
        $this->policies[$name][$value] = $enabled;

        return $this;
    }

    protected function init(array $data = []): void
    {
        $this->addNoncePolicy();

        parent::init($data);
    }

    protected function addNoncePolicy(): void
    {
        $nonce = $this->securityCsp->getNonce();
        $this->setPolicy('default-src', "'nonce-$nonce'");
        $this->setPolicy('script-src', "'nonce-$nonce'");
    }

    public function getValue(): string
    {
        $directives = [];
        foreach ($this->policies as $name => $values) {
            $directives[] = $name . ' ' . implode(' ', array_keys(array_filter($values)));
        }

        return implode('; ', $directives);
    }
}
