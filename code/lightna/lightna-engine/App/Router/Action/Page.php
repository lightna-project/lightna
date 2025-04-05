<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\Layout;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Response;
use Lightna\Engine\Data\Request;

class Page extends ObjectA implements ActionInterface
{
    protected Layout $layout;
    protected Context $context;
    protected Request $request;
    protected Response $response;
    /** @AppConfig(entity) */
    protected array $entities;
    /** @AppConfig(page_cache) */
    protected array $pageCacheConfig;
    /** @AppConfig(bfcache) */
    protected array $bfCacheConfig;

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
        $this->setHeaders();
        $this->layout->page();
    }

    protected function validateRequest(): void
    {
        if (!$this->request->isGet) {
            throw new LightnaException('Page request method must be GET');
        }
    }

    protected function setHeaders(): void
    {
        $this->setCacheControlHeader();
        $this->response->sendHeaders();
    }

    protected function setCacheControlHeader(): void
    {
        $this->response->setHeader(
            'Cache-Control',
            implode(', ', $this->getCacheControlValues())
        );
    }

    protected function getCacheControlValues(): array
    {
        return merge($this->getPageCacheValues(), $this->getBfCacheValues());
    }

    protected function getPageCacheValues(): array
    {
        if ($this->isPageCacheAllowed()) {
            $values = ['public'];
            if ($maxAge = ($this->pageCacheConfig['max_age'] ?? null)) {
                $values[] = 's-maxage=' . $maxAge;
            }
        } else {
            $values = ['private'];
        }

        return $values;
    }

    protected function isPageCacheAllowed(): bool
    {
        return $this->pageCacheConfig['type'] && $this->context->visibility === 'public';
    }

    protected function getBfCacheValues(): array
    {
        if ($this->bfCacheConfig['enabled']) {
            return [];
        } else {
            return ['no-store'];
        }
    }
}
