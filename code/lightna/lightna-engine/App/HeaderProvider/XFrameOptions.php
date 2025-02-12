<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 */
class XFrameOptions extends AbstractHeaderProvider
{

    /**
     * x-frame-options Header name
     *
     * @var string
     */
    protected string $headerName = 'X-Frame-Options';

    /**
     * x-frame-options header value
     *
     * @var string
     */
    protected string $headerValue;

    /**
     * @param string $xFrameOpt
     */
    public function __construct($xFrameOpt = 'SAMEORIGIN')
    {
        $this->headerValue = $xFrameOpt;
    }
}
