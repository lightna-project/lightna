<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Context\Entity;
use Lightna\Engine\Data\DataA;

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