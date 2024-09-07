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
    public Param $param;

    protected function defineIsPost(): void
    {
        $this->isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function defineIsGet(): void
    {
        $this->isGet = $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function defineIsSecure(): void
    {
        $this->isSecure = ($_SERVER['HTTPS'] ?? '') === 'on';
    }

    protected function defineUri(): void
    {
        $this->uri = $_SERVER['REQUEST_URI'];
    }
}
