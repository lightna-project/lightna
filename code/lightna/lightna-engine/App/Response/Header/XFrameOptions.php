<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 */
class XFrameOptions extends AbstractHeader
{
    protected string $name = 'X-Frame-Options';
    protected string $value = 'SAMEORIGIN';
}
