<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider\ContentSecurityPolicy;

use Lightna\Engine\App\ObjectA;

class Csp extends ObjectA implements CspInterface
{
    private NonceSourceInterface $nonceSource;
    private array $policies = [];
    private bool $reportOnly = false;

    public function setNonceSource(NonceSourceInterface $nonce): void
    {
        $this->nonceSource = $nonce;
    }

    public function getNonce(): string
    {
        return $this->nonceSource->getNonce();
    }

    public function addNoncePolicy(): void
    {
        $this->addPolicy('script-src', "'nonce-" . $this->nonceSource->getNonce() . "'");
    }

    public function addPolicy(string $directive, string $value): void
    {
        $this->policies[$directive][] = $value;
        $tmp = array_unique($this->policies[$directive]);
        $this->policies[$directive] = $tmp;
    }

    public function setReportOnly($value): void
    {
        $this->reportOnly = $value;
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
