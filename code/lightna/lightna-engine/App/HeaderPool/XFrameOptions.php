<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool;

/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 */
class XFrameOptions extends AbstractHeader
{
    protected string $headerName = 'X-Frame-Options';

    protected string $headerValue;

    /**
     * @param string $xFrameOpt
     */
    protected function init(array $data = []): void
    {
        $this->headerValue = $data['xFrameOpt'] ?? 'SAMEORIGIN';

        parent::init($data);
    }
}
