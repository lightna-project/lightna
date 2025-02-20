<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool\Dynamic;

use Lightna\Engine\App\ObjectA;

class CspSource extends ObjectA implements SourceInterface
{
    protected CspSource\Nonce $nonce;

    private array $policies = [];

    private bool $reportOnly = false;

    public function getNonce(): string
    {
        return $this->nonce->getNonce();
    }

    public function addNoncePolicy(): static
    {
        $this->addPolicy('default-src', "'nonce-" . $this->nonce->getNonce() . "'");

        return $this;
    }

    public function addPolicy(string $directive, string $value): static
    {
        $this->policies[$directive][] = $value;
        $tmp = array_unique($this->policies[$directive]);
        $this->policies[$directive] = $tmp;

        return $this;
    }

    public function setReportOnly($value): static
    {
        $this->reportOnly = $value;

        return $this;
    }

    public function getName(): string
    {
        return $this->reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
    }

    public function getValue(): string
    {
        $string = '';
        foreach ($this->policies as $directive => $values) {
            $string .= $directive . ' ' . implode(' ', $values) . '; ';
        }

        return rtrim($string, '; ');
    }

}
