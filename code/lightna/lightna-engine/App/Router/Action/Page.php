<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

use Lightna\Engine\App\Layout;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Context;
use Lightna\Engine\Data\Request;

class Page extends ObjectA
{
    protected Layout $layout;
    protected Context $context;
    protected Request $request;
    /** @AppConfig(entity) */
    protected array $entities;

    protected function init(array $params): void
    {
        $this->context->entity->type = $entity = $params['type'];
        $this->context->entity->id = $params['id'] ?? null;
        if ($visibility = ($this->entities[$entity]['visibility'] ?? null)) {
            $this->context->visibility = $visibility;
        }
    }

    public function process(): void
    {
        $this->validateRequest();
        $this->layout->page();
    }

    protected function validateRequest(): void
    {
        if (!$this->request->isGet) {
            throw new \Exception('Page request method must be GET');
        }
    }
}
