<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
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
            throw new Exception("Storage \"$code\" not found.");
        }

        $this->adapters[$code] = newobj(
            $this->storages[$code]['adapter'],
            $this->storages[$code]['options'],
        );

        return $this->adapters[$code];
    }
}
