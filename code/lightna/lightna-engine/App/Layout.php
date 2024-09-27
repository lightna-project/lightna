<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\App\Opcache\Compiled;
use Lightna\Engine\Data\Block as BlockData;
use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\EntityA;
use Throwable;

class Layout extends ObjectA
{
    protected Compiled $compiled;
    protected Templating $templating;
    protected Context $context;
    protected array $layout;
    protected array $current = [];
    protected bool $isMainCurrent;
    /** @AppConfig(entity) */
    protected array $entities;
    protected string $entityType;

    protected function defineEntityType(): void
    {
        $this->entityType = $this->context->entity->type;
    }

    protected function defineLayout(): void
    {
        $layoutName = $this->entities[$this->entityType]['layout'];
        $this->layout = $this->compiled->load('layout/' . $layoutName);
    }

    public function page(): void
    {
        $this->isMainCurrent = true;
        $this->current = [];
        $this->block();
    }

    /**
     * Return is always empty string but declared return type "string" allows to use <?=
     */
    public function block(string $blockName = '', array $vars = []): string
    {
        $this->current[] = $block = $this->resolveBlock($blockName);

        try {
            $before = $this->fetchBeforeBlock($block);
            $after = $this->fetchAfterBlock($block);
            $before && $this->block('before', $vars);

            if (isset($block['template']) && (!empty($blockName) || $this->isMainCurrent)) {
                $this->isMainCurrent = false;
                $this->renderBlockTemplate($block, $vars);
            } else {
                $this->isMainCurrent = false;
                $this->renderBlockContent($block, $vars);
            }

            $after && $this->block('after', $vars);

        } catch (Throwable $e) {
            $this->handleBlockError($e);
        }

        array_pop($this->current);
        $this->flushRendering();

        return '';
    }

    protected function resolveBlock(string $blockName): ?array
    {
        if (str_starts_with($blockName, '#')) {
            return $this->resolveBlockId(substr($blockName, 1));
        } else {
            return $this->resolveBlockName($blockName);
        }
    }

    protected function resolveBlockId(string $blockId): array
    {
        if (!$blockPath = $this->layout['blockById'][$blockId] ?? null) {
            throw new Exception('Block id "' . $blockId . '" not found');
        }
        list($child, $parent) = $this->parseBlockPath($blockPath);

        return array_path_get($this->layout, $parent . '/./' . $child);
    }

    protected function resolveBlockName(string $blockName): array
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

    protected function fetchBeforeBlock(array &$block): ?array
    {
        $before = null;
        if (isset($block['.']['before'])) {
            $before = $block['.']['before'];
            unset($block['.']['before']);
        }

        return $before;
    }

    protected function fetchAfterBlock(array &$block): ?array
    {
        $after = null;
        if (isset($block['.']['after'])) {
            $after = $block['.']['after'];
            unset($block['.']['after']);
        }

        return $after;
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

    protected function parseBlockPath(string $path): array
    {
        $path = explode('/', $path);
        $child = array_pop($path);
        $parent = implode('/./', $path);

        return [$child, $parent];
    }

    protected function flushRendering(): void
    {
        if (IS_PROGRESSIVE_RENDERING && ob_get_level() === 1) {
            ob_flush();
        }
    }

    protected function handleBlockError(Throwable $exception): void
    {
        if (!IS_PROGRESSIVE_RENDERING) {
            throw $exception;
        }

        error_log(($logId = uniqid()) . ' BLOCK ERROR: ' . $exception);
        echo "[BLOCK ERROR $logId]";
    }
}
