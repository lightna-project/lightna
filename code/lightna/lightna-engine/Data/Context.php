<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\App\Context as AppContext;
use Lightna\Engine\App\Context\Entity;

class Context extends DataA
{
    public Entity $entity;
    protected AppContext $appContext;

    protected function defineEntity(): void
    {
        $this->entity = $this->appContext->entity;
    }
}
