<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State;

use Lightna\Engine\App\Entity\State as StateEntity;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\State\Index\Entity;
use Lightna\Engine\Data\DataA;

/**
 * @property Entity[] entities
 */
class Index extends DataA
{
    public array $entities = [];

    /** @AppConfig(entity) */
    protected array $allEntities;
    protected StateEntity $entity;
    protected Indexer $indexer;

    protected function init(array $data = []): void
    {
        parent::init($this->entity->get('index'));

        foreach ($this->allEntities as $code => $entity) {
            if (!$this->indexer->getEntityIndex($code)) {
                continue;
            }
            $this->entities[$code] ??= newobj(Entity::class);
        }

        ksort($this->entities);
    }

    public function save(): void
    {
        $this->entity->set('index', o2a($this));
    }

    public function invalidate(array $codes): void
    {
        foreach ($codes as $code) {
            $this->markInvalidated($code);
        }

        $this->save();
    }

    public function invalidateAll(): void
    {
        foreach (array_keys($this->entities) as $code) {
            $this->markInvalidated($code);
        }

        $this->save();
    }

    protected function markInvalidated(string $code): void
    {
        if (!isset($this->entities[$code])) {
            return;
        }

        $this->entities[$code]->invalidatedAt = microtime(true);
    }
}
