<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

class Route extends EntityA
{
    public const NAME = 'route';
    public const STORAGE_PREFIX = 'URL_';
    public const ACTION_301 = 1;
    public const ACTION_302 = 2;
    public const ACTION_CUSTOM = 3;

    /** @AppConfig(entity/route/storage) */
    protected string $storageName;
    protected EntityRoute $entityRoute;

    public function get(int|string $id): array
    {
        if (!$data = parent::get($id)) {
            return [];
        }

        return [
            'action' => $data[0],
            'params' => $data[1],
        ];
    }

    public function batch(): void
    {
        parent::batch();
        $this->entityRoute->batch();
    }

    public function flush(): void
    {
        parent::flush();
        $this->entityRoute->flush();
    }

    protected function getEntityUrls(string $entityName, int $id): array|string
    {
        return $this->entityRoute->get($entityName . '.' . $id);
    }

    protected function setEntityUrls(string $entityName, int $id, array $urls): void
    {
        $this->entityRoute->set($entityName . '.' . $id, $urls);
    }

    protected function unsetEntityUrls(string $entityName, int $id): void
    {
        $this->entityRoute->unset($entityName . '.' . $id);
    }

    public function setEntityRoutes(string $entityName, int $id, array $routes): void
    {
        $this->unsetEntityUrls($entityName, $id);

        $urls = [];
        foreach ($routes as $url => $rule) {
            $urls[] = $url;
            $this->set($url, $rule);
        }
        $this->setEntityUrls($entityName, $id, $urls);
    }

    public function unsetEntityRoutes(string $entityName, int $id): void
    {
        if (($urls = $this->getEntityUrls($entityName, $id)) === "") {
            return;
        }

        foreach ($urls as $url) {
            $this->unset($url);
        }
        $this->unsetEntityUrls($entityName, $id);
    }
}
