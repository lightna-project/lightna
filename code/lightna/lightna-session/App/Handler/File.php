<?php

declare(strict_types=1);

namespace Lightna\Session\App\Handler;

use Exception;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Context;
use Throwable;

class File extends ObjectA implements HandlerInterface
{
    protected array $options;
    protected Context $context;

    protected function init(array $data = []): void
    {
        $this->options = $data;
    }

    public function read(): array
    {
        if (!isset($_COOKIE[session_name()])) {
            return [];
        }

        if (!$srz = @file_get_contents($this->getFilename($_COOKIE[session_name()]))) {
            return [];
        }

        try {
            $data = $this->unserialize($srz);
        } catch (Throwable $e) {
            throw new Exception(
                'Session can\'t be unserialized, make sure session.serialize_handler = php_serialize'
            );
        }

        return $data[$this->options['namespace']]['data'][$this->getScopeKey()] ?? [];
    }

    protected function unserialize(string $srz): array
    {
        return unserialize($srz);
    }

    protected function getScopeKey(): string
    {
        return 'scope_' . $this->context->scope;
    }

    public function prolong(): void
    {
        if (!isset($_COOKIE[session_name()])) {
            return;
        }

        @touch($this->getFilename($_COOKIE[session_name()]));
    }

    protected function getFilename(string $id): string
    {
        return session_save_path() . '/sess_' . $id;
    }
}
