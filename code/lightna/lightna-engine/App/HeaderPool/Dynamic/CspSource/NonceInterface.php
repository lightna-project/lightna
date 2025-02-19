<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool\Dynamic\CspSource;

interface NonceInterface
{
    /**
     * Generate a unique nonce for use in a Content-Security-Policy header.
     *
     * @return string
     */
    public function getNonce(): string;
}