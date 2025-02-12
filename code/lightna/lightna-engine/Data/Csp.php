<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\App\HeaderPool\Dynamic\CspSource;

/**
 * @method string nonce(string $escapeMethod = null)
 */
class Csp extends DataA
{
    protected CspSource $cspSource;

    public string $nonce;

    /** @noinspection PhpUnused */
    protected function defineNonce(): void
    {
        $this->nonce = $this->cspSource->getNonce();
    }
}
