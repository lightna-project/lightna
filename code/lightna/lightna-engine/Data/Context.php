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

    /** @AppConfig(page_cache) */
    protected array $pageCacheConfig;
    protected AppContext $appContext;
    protected Layout $layout;

    /** @noinspection PhpUnused */
    protected function defineEntity(): void
    {
        $this->entity = $this->appContext->entity;
    }

    /** @noinspection PhpUnused */
    protected function defineMode(): void
    {
        $this->mode = $this->appContext->mode;
    }

    /** @noinspection PhpUnused */
    protected function definePrivateBlocks(): void
    {
        $this->privateBlocks =
            $this->pageCacheConfig['type'] && $this->appContext->visibility === 'public'
                ? $this->layout->getPrivateBlockIds()
                : [];
    }
}
