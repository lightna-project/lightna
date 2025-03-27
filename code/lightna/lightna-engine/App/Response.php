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
    protected array $runtimeHeaders = [];
    protected int $status;
    protected string $body;

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function send(): void
    {
        $this->sendHeaders();

        echo $this->body;
    }

    public function sendHeaders(): void
    {
        $this->sendStatus();
        $this->sendPermanentHeaders();
        $this->sendRuntimeHeaders();
    }

    protected function sendStatus(): void
    {
        if (isset($this->status)) {
            http_response_code($this->status);
        }
    }

    protected function sendPermanentHeaders(): void
    {
        foreach ($this->getPermanentHeaders() as $class => $enabled) {
            if ($enabled) {
                $this->sendPermanentHeader($class);
            }
        }
    }

    protected function sendPermanentHeader(string $class): void
    {
        $header = getobj($class);

        header($header->getName() . ': ' . $header->getValue());
    }

    /**
     * @return HeaderInterface[]
     */
    protected function getPermanentHeaders(): array
    {
        return [
            CacheControl::class => true,
            XFrameOptions::class => true,
            XContentTypeOptions::class => true,
            XssProtection::class => true,
            Csp::class => true,
        ];
    }

    protected function sendRuntimeHeaders(): void
    {
        foreach ($this->runtimeHeaders as $name => $value) {
            header($name . ': ' . $value);
        }
    }

    public function setHeader(string $name, $value): static
    {
        $this->runtimeHeaders[$name] = $value;
        return $this;
    }

    public function unsetHeader(string $name): static
    {
        unset ($this->runtimeHeaders[$name]);
        return $this;
    }

    public function redirect(string $url, int $code = 302): static
    {
        if (!preg_match('~^https?://~', $url)) {
            // Add slash to relative URL
            $url = $url[0] !== '/' ? '/' . $url : $url;
        }

        $this
            ->setHeader('Location', filter_var($url, FILTER_SANITIZE_URL))
            ->setStatus($code);

        return $this;
    }
}
