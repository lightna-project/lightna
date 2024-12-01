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
        $cName = $this->options['cookie']['name'];
        if (!isset($_COOKIE[$cName])) {
            return [];
        }

        if (!$srz = @file_get_contents($this->getFilename($_COOKIE[$cName]))) {
            return [];
        }

        try {
            $data = unserialize($srz);
        } catch (Throwable $e) {
            throw new Exception(
                'Session can\'t be unserialized, make sure session.serialize_handler = php_serialize'
            );
        }

        $scopeKey = 'scope_' . $this->context->scope;

        return $data['lightna_session']['data'][$scopeKey] ?? [];
    }

    public function prolong(): void
    {
        $cName = $this->options['cookie']['name'];
        if (!isset($_COOKIE[$cName])) {
            return;
        }

        @touch($this->getFilename($_COOKIE[$cName]));
    }

    protected function getFilename(string $id): string
    {
        return $this->options['path'] . '/' . $this->options['prefix'] . $id;
    }
}
