<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider\Dynamic\Csp;

use Lightna\Engine\App\ObjectA;

class Nonce extends ObjectA implements NonceInterface
{
    private string $nonce;

    /**
     * @throws \Exception
     */
    public function getNonce(): string
    {
        if (!isset($this->nonce)) {
            $this->nonce = $this->createNonce();
        }

        return $this->nonce;
    }

    /**
     * @throws \Exception
     */
    protected function createNonce(): string
    {
        $strongResult = true;
        $nonce = bin2hex(openssl_random_pseudo_bytes(32, $strongResult));

        if (!$strongResult) {
            throw new \RuntimeException('Could not generate a secure nonce');
        }

        return $nonce;
    }
}
