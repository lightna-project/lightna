<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\Data\Request\Param;

class Request extends DataA
{
    public bool $isPost;
    public bool $isGet;
    public bool $isSecure;
    public string $uri;
    public string $uriPath;
    public Param $param;

    /** @noinspection PhpUnused */
    protected function defineIsPost(): void
    {
        $this->isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /** @noinspection PhpUnused */
    protected function defineIsGet(): void
    {
        $this->isGet = $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /** @noinspection PhpUnused */
    protected function defineIsSecure(): void
    {
        $this->isSecure = ($_SERVER['HTTPS'] ?? '') === 'on';
    }

    /** @noinspection PhpUnused */
    protected function defineUri(): void
    {
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    /** @noinspection PhpUnused */
    protected function defineUriPath(): void
    {
        $qi = strpos($this->uri, '?');
        $this->uriPath = substr($this->uri, 1, $qi !== false ? $qi - 1 : null);
    }
}
