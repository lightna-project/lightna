<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 */
class XFrameOptions extends AbstractHeaderProvider
{
    protected string $headerName = 'X-Frame-Options';
    protected string $headerValue;

    /**
     * @param string $xFrameOpt
     */
    public function __construct($xFrameOpt = 'SAMEORIGIN')
    {
        $this->headerValue = $xFrameOpt;
    }
}
