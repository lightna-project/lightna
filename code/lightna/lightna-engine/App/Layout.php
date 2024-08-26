<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\Data\Block as BlockData;
use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\EntityA;
use Lightna\Engine\Data\Request;

class Layout extends ObjectA
{
    protected Compiled $compiled;
    protected Templating $templating;
    protected Request $request;
    protected array $layout;
    protected array $current;
    protected bool $isMainCurrent;
    /** @AppConfig(entity) */
    protected array $entities;
    protected string $entityType;

    public function render(string $entityType): void
    {
        $this->entityType = $entityType;
        $this->load();
        $this->isMainCurrent = true;

        if ($block = $this->request->block) {
            $this->renderSingleBlock($block);
        } else {
            $this->renderPage();
        }
    }

    protected function renderPage(): void
    {
        $this->current = [];
        $this->block();
    }

    public function block(string $blockName = ''): void
    {
        $current = end($this->current);
        $block = $blockName ? $current['.'][$blockName] : ($current ?: $this->layout);
        if (!$block) {
            return;
        }

        $this->current[] = $block;

        if (isset($block['template']) && (!empty($blockName) || $this->isMainCurrent)) {
            $this->isMainCurrent = false;
            $this->renderBlockTemplate($block);
        } else {
            $this->isMainCurrent = false;
            $this->renderBlockContent($block);
        }

        array_pop($this->current);
    }

    public function template(string $template, array $vars = []): void
    {
        $this->templating->render(
            $template,
            $this->getTemplateVars($template, $vars)
        );
    }

    protected function load(): void
    {
        $layoutName = $this->entities[$this->entityType]['layout'];
        $this->layout = $this->compiled->load('layout/' . $layoutName);
    }

    protected function renderBlockTemplate(array $block): void
    {
        $this->templating->render(
            $block['template'],
            $this->getBlockVars($block)
        );
    }

    protected function renderBlockContent(array $block): void
    {
        $cTag = $block['container'] ?? '';
        $cTag && print('<' . $cTag . $this->getBlockData($block, BlockData::class)->attributes() . '>');

        foreach ($block['.'] as $name => $child) {
            $this->block($name);
        }

        $cTag && print('</' . $cTag . '>');
    }

    protected function getBlockData(array $block, string $class): BlockData
    {
        $data = $block;
        unset($data['.']);

        return newobj($class, $data);
    }

    protected function getBlockVars(array $block): array
    {
        $vars = [];
        foreach ($this->templating->getTemplateSchema($block['template']) as $name => $type) {
            if (is_a($type, BlockData::class, true)) {
                $vars[$name] = $this->getBlockData($block, $type);
            }
            // Other variables will be defined in self::getTemplateVars
        }

        return $this->getTemplateVars($block['template'], $vars);
    }

    protected function getTemplateVars(string $template, array $vars = []): array
    {
        foreach ($this->templating->getTemplateSchema($template) as $name => $type) {
            if (array_key_exists($name, $vars)) {
                // Auto-convert array to Data type if declared
                if (is_array($vars[$name]) && is_a($type, DataA::class, true)) {
                    $vars[$name] = newobj($type, $vars[$name]);
                }
                continue;
            }
            if (is_a($type, EntityA::class, true)) {
                $vars[$name] = getobj($this->entities[$this->entityType]['data']);
            }
            // Other variables will be defined in \Lightna\Engine\App\Template::getTemplateVars
        }

        return $vars;
    }

    protected function renderSingleBlock(string $blockName): void
    {
        list($child, $parent) = $this->parseBlockName($blockName);
        if (!$node = array_path_get($this->layout, $parent)) {
            throw new Exception('Block "' . $blockName . '" not found');
        }

        $this->current[] = $node;
        $this->block($child);
    }

    protected function parseBlockName(string $block): array
    {
        $path = explode('.', $block);
        $child = array_pop($path);
        $parent = implode('/./', $path);

        return [$child, $parent];
    }
}
