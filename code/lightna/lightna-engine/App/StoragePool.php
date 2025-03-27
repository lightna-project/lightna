<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\Storage\StorageInterface;

class StoragePool extends ObjectA
{
    protected array $adapters;
    /** @AppConfig(storage) */
    protected array $storages;

    public function get(string $code): StorageInterface
    {
        if (isset($this->adapters[$code])) {
            return $this->adapters[$code];
        }

        if (!isset($this->storages[$code])) {
            throw new LightnaException("Storage \"$code\" not found.");
        }

        $this->adapters[$code] = newobj(
            $this->storages[$code]['adapter'],
            $this->storages[$code]['options'],
        );

        return $this->adapters[$code];
    }
}
