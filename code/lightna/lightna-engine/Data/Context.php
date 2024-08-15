<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\Data\Context\Entity;

class Context extends DataA
{
    public int $scope;
    public Entity $entity;

    protected function init($data = [])
    {
        parent::init($data);
        $this->resolve();
    }

    protected function resolve()
    {
        $this->scope = 1;
    }
}
