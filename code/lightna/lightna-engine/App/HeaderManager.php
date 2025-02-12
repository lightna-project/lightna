<?php declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\HeaderPool\HeaderInterface;

class HeaderManager extends ObjectA
{
    protected HeaderPool $headerPool;

    public function getHeaders(): array
    {
        return $this->headerPool->getHeaders();
    }

    public function isValidHeader($header): bool
    {
        return $header instanceof HeaderInterface;
    }

    public function sendHeader(HeaderInterface $header): void
    {
        if ($header->canApply()) {
            header($header->getName() . ': ' . $header->getValue());
        }
    }
}
