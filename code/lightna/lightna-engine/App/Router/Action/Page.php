<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

use Lightna\Engine\App\Layout;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Context;

class Page extends ObjectA
{
    protected Layout $layout;
    protected Context $context;

    protected function init($params): void
    {
        $this->context->entity->type = $params['type'];
        $this->context->entity->id = $params['id'] ?? null;
    }

    public function process()
    {
        $this->layout->render(
            $this->context->entity->type
        );
    }
}
