<?php declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\HeaderPool\CacheControl;
use Lightna\Engine\App\HeaderPool\XFrameOptions;
use Lightna\Engine\App\HeaderPool\XContentTypeOptions;
use Lightna\Engine\App\HeaderPool\XssProtection;
use Lightna\Engine\App\HeaderPool\Csp;

class HeaderPool extends ObjectA
{
    protected CacheControl $cacheControl;

    protected XFrameOptions $xFrameOptions;

    protected XContentTypeOptions $xContentTypeOptions;

    protected XssProtection $xssProtection;

    protected Csp $csp;

    public function getHeaders(): array
    {
        return [
            $this->cacheControl,
            $this->xFrameOptions,
            $this->xContentTypeOptions,
            $this->xssProtection,
            $this->csp,
        ];
    }
}
