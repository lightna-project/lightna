<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\App\Security\Csp as SecurityCsp;

/**
 * @method string nonce(string $escapeMethod = null)
 */
class Csp extends DataA
{
    public string $nonce;
    protected SecurityCsp $securityCsp;

    /** @noinspection PhpUnused */
    protected function defineNonce(): void
    {
        $this->nonce = $this->securityCsp->getNonce();
    }
}
