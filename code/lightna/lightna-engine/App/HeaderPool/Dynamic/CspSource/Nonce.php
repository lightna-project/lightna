<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool\Dynamic\CspSource;

use Lightna\Engine\App\ObjectA;

class Nonce extends ObjectA implements NonceInterface
{
    private string $nonce;

    public function getNonce(): string
    {
        if (!isset($this->nonce)) {
            $this->nonce = $this->createNonce();
        }

        return $this->nonce;
    }

    protected function createNonce(): string
    {
        return bin2hex(random_bytes(16));
    }
}
