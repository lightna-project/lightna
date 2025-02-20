<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool;

/**
 * Adds a Content-Security-Policy header to HTTP responses to safeguard against cross-site scripting.
 */
class Csp extends AbstractHeader implements HeaderDynamicInterface
{

    protected Dynamic\CspSource $cspSource;

    protected function init(array $data = []): void
    {
        $this->getSource()->addPolicy('default-src', "'self'");
        $this->addNoncePolicy();

        parent::init($data);
    }

    public function addNoncePolicy(): static
    {
        $this->getSource()->addNoncePolicy();

        return $this;
    }

    public function getName(): string
    {
        return $this->getSource()->getName();
    }

    public function getValue(): string
    {
        return $this->getSource()->getValue();
    }

    public function getSource(): Dynamic\SourceInterface
    {
        return $this->cspSource;
    }
}
