<?php declare(strict_types=1);

namespace Lightna\Engine\App\Security;

use Lightna\Engine\App\ObjectA;

class Csp extends ObjectA
{
    /** @AppConfig(security/csp/policy) */
    protected array $policies;

    protected string $nonce;

    protected function init(array $data = []): void
    {
        $this->applyNonceToPolicies();

        parent::init($data);
    }

    /** @noinspection PhpUnused */
    protected function defineNonce(): void
    {
        $this->nonce = bin2hex(random_bytes(16));
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function getPolicies(): array
    {
        return $this->policies;
    }

    protected function applyNonceToPolicies(): void
    {
        foreach ($this->policies as $name => $policies) {
            foreach ($policies as $value => $enabled) {
                if ($enabled && $value === "'nonce'") {
                    $this->policies[$name][$value] = false;
                    $this->policies[$name]["'nonce-{$this->nonce}'"] = true;
                }
            }
        }
    }
}
