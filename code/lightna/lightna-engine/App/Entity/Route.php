<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

class Route extends EntityA
{
    const STORAGE_PREFIX = 'URL_';
    const MULTIPLE_VALUES_PER_SCOPE = true;
    const ACTION_301 = 1;
    const ACTION_302 = 2;
    const ACTION_CUSTOM = 3;

    /** @AppConfig(entity/route/storage) */
    protected string $storageName;

    public function get(int|string $id): array
    {
        if (!$data = $this->storage->get(static::STORAGE_PREFIX . $id)) {
            return [];
        }

        return [
            'action' => $data[0],
            'params' => $data[1],
        ];
    }

    public function add301(string $from, string $to): void
    {
        $this->addUrl($from, [static::ACTION_301, [$to]]);
    }

    public function add302(string $from, string $to): void
    {
        $this->addUrl($from, [static::ACTION_302, [$to]]);
    }

    public function addAction(string $url, string $code, $params = []): void
    {
        $this->addUrl($url, [static::ACTION_CUSTOM, [$code, $params]]);
    }

    protected function addUrl(string $url, array $rule): void
    {
        $this->storage->set('URL_' . $url, $rule);
    }
}
