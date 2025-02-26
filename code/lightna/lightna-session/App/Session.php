<?php

declare(strict_types=1);

namespace Lightna\Session\App;

use Exception;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Session\App\Handler\HandlerInterface;
use Lightna\Session\App\Session\Cookie;
use Lightna\Session\App\Session\DataBuilder;
use Lightna\Session\App\Session\Serializer;
use Throwable;

class Session extends ObjectA
{
    /** @AppConfig(session) */
    protected array $config;
    /** @AppConfig(session_handler) */
    protected array $handlers;
    /** @AppConfig(fpc_compatible) */
    protected bool $fpcCompatible;
    protected HandlerInterface $handler;
    protected Cookie $cookie;
    protected Context $context;
    protected DataBuilder $dataBuilder;
    protected Serializer $serializer;

    protected array $data;
    protected bool $isReindexRequired = false;

    /** @noinspection PhpUnused */
    protected function defineHandler(): void
    {
        if (!$handler = $this->handlers[$this->config['handler']] ?? null) {
            throw new Exception('Unknown session handler "' . $this->config['handler'] . '"');
        }

        $this->handler = getobj($handler);
    }

    public function getData(): array
    {
        return $this->data;
    }

    /** @noinspection PhpUnused */
    protected function defineData(): void
    {
        $this->data = $this->readData();
    }

    protected function readData(): array
    {
        if (!$this->canRead()) {
            throw new Exception('Reading the session on public pages is not allowed in FPC-compatible mode.');
        }

        $srz = $this->readContent();
        $this->dataBuilder->setSessionData($this->getSessionData($this->unserialize($srz)));
        $scopeData = $this->dataBuilder->getScopeData();
        $this->isReindexRequired = $this->dataBuilder->getIsReindexRequired();

        return $scopeData;
    }

    public function canRead(): bool
    {
        return !$this->fpcCompatible || $this->context->visibility === 'private';
    }

    protected function readContent(): string
    {
        return $this->handler->read();
    }

    public function prolong(): void
    {
        $this->cookie->prolong();
        $this->handler->prolong();
    }

    protected function unserialize(string $srz): array
    {
        if ($srz === '') return [];

        try {
            $data = $this->serializer->unserialize($srz);
        } catch (Throwable $e) {
            throw new Exception('Failed to unserialized session.');
        }

        return $data;
    }

    protected function getSessionData(array $data): array
    {
        return $data[$this->config['namespace']] ?? [];
    }

    public function getIsReindexRequired(): bool
    {
        return $this->isReindexRequired;
    }
}
