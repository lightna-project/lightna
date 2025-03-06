<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Response\Header\CacheControl;
use Lightna\Engine\App\Response\Header\Csp;
use Lightna\Engine\App\Response\Header\HeaderInterface;
use Lightna\Engine\App\Response\Header\XContentTypeOptions;
use Lightna\Engine\App\Response\Header\XFrameOptions;
use Lightna\Engine\App\Response\Header\XssProtection;

class Response extends ObjectA
{
    public function sendHeaders(): void
    {
        foreach ($this->getHeaders() as $class => $enabled) {
            if ($enabled) {
                $this->sendHeader($class);
            }
        }
    }

    protected function sendHeader(string $class): void
    {
        $header = getobj($class);

        header($header->getName() . ': ' . $header->getValue());
    }

    /**
     * @return HeaderInterface[]
     */
    protected function getHeaders(): array
    {
        return [
            CacheControl::class => true,
            XFrameOptions::class => true,
            XContentTypeOptions::class => true,
            XssProtection::class => true,
            Csp::class => true,
        ];
    }
}
