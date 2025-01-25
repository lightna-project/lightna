<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

use Exception;
use Lightna\Engine\App\Layout;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Context;
use Lightna\Engine\Data\Request;

class Block extends ObjectA
{
    protected Context $context;
    protected Request $request;
    protected Layout $layout;

    protected function init(array $data = []): void
    {
        $this->context->entity->type = $this->request->param->entityType;
        $this->context->entity->id = $this->request->param->entityId;
        $this->context->visibility = 'private';
    }

    public function process(): void
    {
        $this->validateRequest();
        $this->configureLayout();
        $this->render();
    }

    protected function validateRequest(): void
    {
        if (!$this->request->isPost) {
            throw new Exception('Block request method must be POST');
        }
        if (!$this->request->param->blockIds) {
            throw new Exception('blockIds parameter is missing in request');
        }
    }

    protected function configureLayout(): void
    {
        $this->layout->setRenderLazyBlocks(true);

        if ($this->request->param->renderLazyBlocks === "false") {
            $this->layout->setRenderLazyBlocks(false);
        }
    }

    protected function render(): void
    {
        header('Content-type: application/json');
        echo json($this->getBlocksHtmlData());
    }

    protected function getBlocksHtmlData(): mixed
    {
        $blockIds = $this->request->param->blockIds;

        return is_string($blockIds) ? $this->getBlockHtml($blockIds) : $this->getBlocksHtml($blockIds);
    }

    protected function getBlockHtml(string $blockId): string
    {
        return blockhtml('#' . $blockId);
    }

    protected function getBlocksHtml(array $blockIds): array
    {
        $blocks = [];
        foreach ($blockIds as $blockId) {
            $blocks[$blockId] = $this->getBlockHtml($blockId);
        }

        return $blocks;
    }
}
