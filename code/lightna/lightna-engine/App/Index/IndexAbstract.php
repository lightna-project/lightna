<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index;

use Lightna\Engine\App\ObjectA;

abstract class IndexAbstract extends ObjectA implements IndexInterface
{
    public function refresh(array $ids): void
    {
        $this->entity->batch();

        $data = $this->getBatchData($ids);
        foreach ($data as $id => $item) {
            $this->updateItem($id, $item);
        }

        $remove = array_diff($ids, array_keys($data));
        foreach ($remove as $id) {
            $this->removeItem($id);
        }

        $this->entity->flush();
    }

    protected function updateItem(string|int $id, array $data): void
    {
        $this->entity->set($id, array_camel($data));
    }

    protected function removeItem(string|int $id): void
    {
        $this->entity->unset($id);
    }
}
