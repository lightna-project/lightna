<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\App\Context as AppContext;
use Lightna\Engine\App\Context\Entity;
use Lightna\Engine\App\Layout;

class Context extends DataA
{
    public Entity $entity;
    public string $mode = '';
    public array $privateBlocks;

    /** @AppConfig(fpc_compatible) */
    protected bool $isFpcCompatible;
    protected AppContext $appContext;
    protected Layout $layout;

    protected function defineEntity(): void
    {
        $this->entity = $this->appContext->entity;
    }

    protected function defineMode(): void
    {
        $this->mode = $this->appContext->mode;
    }

    protected function definePrivateBlocks(): void
    {
        $this->privateBlocks = $this->isFpcCompatible ? $this->layout->getPrivateBlockIds() : [];
    }
}
