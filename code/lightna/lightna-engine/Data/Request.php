<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

class Request extends DataA
{
    public bool $isPost;
    public bool $isGet;
    public string $uri;

    protected function defineIsPost(): void
    {
        $this->isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function defineIsGet(): void
    {
        $this->isGet = $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function defineUri(): void
    {
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    protected function &__get_fallback(string $name): mixed
    {
        $this->defineRequestVar($name);

        return $this->$name;
    }

    protected function defineRequestVar(string $name): void
    {
        $this->$name = $_REQUEST[$name] ?? null;
    }
}
