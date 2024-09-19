<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

use Lightna\Engine\App\Layout;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Context;
use Lightna\Engine\Data\Request;

class Block extends ObjectA
{
    protected Layout $layout;
    protected Context $context;
    protected Request $request;

    protected function init(): void
    {
        $this->context->entity->type = $this->request->param->entityType;
        $this->context->entity->id = $this->request->param->entityId;
    }

    public function process(): void
    {
        $this->validateRequest();
        $this->renderBlock();
    }

    protected function validateRequest(): void
    {
        if (!$this->request->isPost) {
            throw new \Exception('Block request method must be POST');
        }
        if (!$this->request->param->blockId) {
            throw new \Exception('blockId is missing in request');
        }
    }

    protected function renderBlock(): void
    {
        header('Content-type: application/json');
        echo json(blockhtml('#' . $this->request->param->blockId));
    }
}
