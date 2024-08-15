<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Storage\StorageInterface;

class Storage extends ObjectA
{
    protected array $adapters;
    /** @AppConfig(storage) */
    protected array $storages;

    public function get(string $code): StorageInterface
    {
        if (isset($this->adapters[$code])) {
            return $this->adapters[$code];
        }

        $this->adapters[$code] = getobj(
            $this->storages[$code]['adapter'],
            $this->storages[$code]['options'],
        );

        return $this->adapters[$code];
    }
}
