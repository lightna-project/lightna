<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\Data\Context\Entity;

class Context extends DataA
{
    public int $scope;
    public Entity $entity;

    protected function init($data = []): void
    {
        parent::init($data);
        $this->resolve();
    }

    protected function resolve(): void
    {
        // To plugin
        $this->scope = 1;
    }
}
