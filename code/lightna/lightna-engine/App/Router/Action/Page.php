<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\Layout;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Response;
use Lightna\Engine\Data\Request;

class Page extends ObjectA
{
    protected Layout $layout;
    protected Context $context;
    protected Request $request;
    protected Response $response;
    /** @AppConfig(entity) */
    protected array $entities;
    /** @AppConfig(page_cache) */
    protected array $pageCacheConfig;

    protected function init(array $data = []): void
    {
        $this->context->entity->type = $entity = $data['type'];
        $this->context->entity->id = $data['id'] ?? null;
        if ($visibility = ($this->entities[$entity]['visibility'] ?? null)) {
            $this->context->visibility = $visibility;
        }
    }

    public function process(): void
    {
        $this->validateRequest();
        $this->addPageHeaders();
        $this->layout->page();
    }

    protected function validateRequest(): void
    {
        if (!$this->request->isGet) {
            throw new LightnaException('Page request method must be GET');
        }
    }

    protected function addPageHeaders(): void
    {
        $this->addCacheControlHeader();
    }

    protected function addCacheControlHeader(): void
    {
        if (!$this->pageCacheConfig['type'] || $this->context->visibility === 'private') {
            // Keep default Cache-Control
            return;
        }

        $maxAge = $this->pageCacheConfig['max_age'] ?? null;

        $this->response
            ->setHeader(
                'Cache-Control',
                'public'
                . ($maxAge ? ', s-maxage=' . $maxAge : '')
                . ', no-store, must-revalidate',
            )
            ->sendHeaders();
    }
}
