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

    public function block(string $blockName = '', array $vars = []): void
    {
        $this->current[] = $block = $this->resolveBlock($blockName);

        $this->beforeBlock($block, $vars);
        if (isset($block['template']) && (!empty($blockName) || $this->isMainCurrent)) {
            $this->isMainCurrent = false;
            $this->renderBlockTemplate($block, $vars);
        } else {
            $this->isMainCurrent = false;
            $this->renderBlockContent($block, $vars);
        }
        $this->afterBlock($block, $vars);

        array_pop($this->current);
    }

    protected function resolveBlock(string $blockName): ?array
    {
        $current = end($this->current);
        if ($blockName !== '') {
            $block = $current['.'][$blockName] ?? null;
            $block ??= $blockName === 'self' ? $current : null;
        } else {
            $block = $current ?: $this->layout;
        }
        if (!$block) {
            throw new Exception("Block \"$blockName\" not found");
        }

        return $block;
    }

    protected function beforeBlock(array &$block, array $vars): void
    {
        if (isset($block['.']['before'])) {
            block('before', $vars);
            unset($block['.']['before']);
        }
    }

    protected function afterBlock(array &$block, array $vars): void
    {
        if (isset($block['.']['after'])) {
            block('after', $vars);
            unset($block['.']['after']);
        }
    }

    public function template(string $template, array $vars = []): void
    {
        if (IS_DEV_MODE && !empty($this->current)) {
            throw new Exception("The template() method call is not intended for use within blocks.");
        }

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

    protected function renderBlockTemplate(array $block, array $vars = []): void
    {
        $this->templating->render(
            $block['template'],
            $this->getBlockVars($block, $vars)
        );
    }

    protected function renderBlockContent(array $block, array $vars = []): void
    {
        $cTag = $block['container'] ?? '';
        $cTag && print('<' . $cTag . $this->getBlockData($block, BlockData::class)->attributes() . '>');

        foreach ($block['.'] as $name => $child) {
            $this->block($name, $vars);
        }

        $cTag && print('</' . $cTag . '>');
    }

    protected function getBlockData(array $block, string $class): BlockData
    {
        foreach ($block['.'] as $name => $child) {
            // Clean deep structure and pass only children of current block
            unset($block['.'][$name]['.']);
        }

        return newobj($class, $block);
    }

    protected function getBlockVars(array $block, array $vars = []): array
    {
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
