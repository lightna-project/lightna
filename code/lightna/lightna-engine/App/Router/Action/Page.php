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

    protected function init(array $params): void
    {
        $this->context->entity->type = $params['type'];
        $this->context->entity->id = $params['id'] ?? null;
    }

    public function process()
    {
        if ($blockId = $this->request->param->blockId) {
            $this->renderBlock($blockId);
        } else {
            $this->layout->page();
        }
    }

    protected function renderBlock(string $blockId): void
    {
        header('Content-type: application/json');
        echo json(blockhtml('#' . $blockId));
    }
}
