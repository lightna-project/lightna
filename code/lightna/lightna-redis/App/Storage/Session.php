<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage;

use Exception;

class Session extends Redis
{
    /** @AppConfig(storage/redis_session/options) */
    protected array $options;

    #[\Override]
    protected function init(array $data = []): void
    {
        $this->options = merge($this->optionDefaults, $this->options);
    }

    #[\Override]
    protected function setSerializerOptions(): void
    {
        // No serializer options
    }

    #[\Override]
    public function get(string $key): string|array
    {
        return match ($this->options['data_type']) {
            'string' => parent::get($key),
            'hash' => $this->getHashField($key, $this->options['data_hash_field']),
            default => throw new Exception('Unknown data_type for redis session storage'),
        };
    }

    public function expire(string $key, int $ttl): void
    {
        $this->client->expire($key, $ttl);
    }
}
