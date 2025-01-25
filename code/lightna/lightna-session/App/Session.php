<?php

declare(strict_types=1);

namespace Lightna\Session\App;

use Exception;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Session\App\Handler\HandlerInterface;
use Lightna\Session\App\Session\Cookie;
use Lightna\Session\App\Session\DataBuilder;
use Throwable;

class Session extends ObjectA
{
    /** @AppConfig(session) */
    protected array $config;
    /** @AppConfig(fpc_compatible) */
    protected bool $fpcCompatible;
    protected HandlerInterface $handler;
    protected Cookie $cookie;
    protected Context $context;
    protected DataBuilder $dataBuilder;
    protected array $data;
    protected bool $isReindexRequired = false;

    /** @noinspection PhpUnused */
    protected function defineHandler(): void
    {
        $this->handler = getobj($this->config['handler']);
    }

    /** @noinspection PhpUnused */
    protected function defineData(): void
    {
        $this->data = $this->read();
    }

    public function getData(): array
    {
        return $this->data;
    }

    protected function read(): array
    {
        if (!$this->canRead()) {
            throw new Exception('Reading the session on public pages is not allowed in FPC-compatible mode.');
        }

        $srz = $this->handler->read();
        $this->dataBuilder->setSessionData($this->getSessionData($this->unserialize($srz)));
        $scopeData = $this->dataBuilder->getScopeData();
        $this->isReindexRequired = $this->dataBuilder->getIsReindexRequired();

        return $scopeData;
    }

    public function canRead(): bool
    {
        return !$this->fpcCompatible || $this->context->visibility === 'private';
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
            $data = unserialize($srz, ['allowed_classes' => false]);
        } catch (Throwable $e) {
            // By default, only session.serialize_handler = php_serialize is supported
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
