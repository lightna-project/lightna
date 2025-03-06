<?php declare(strict_types=1);

namespace Lightna\Engine\App\Security;

use Lightna\Engine\App\ObjectA;

class Csp extends ObjectA
{
    protected string $nonce;

    /** @noinspection PhpUnused */
    protected function defineNonce(): void
    {
        $this->nonce = bin2hex(random_bytes(16));
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }
}
