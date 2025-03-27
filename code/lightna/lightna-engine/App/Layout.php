<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\Data\Block as BlockData;
use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\EntityData;
use Throwable;

class Layout extends ObjectA
{
    protected Build $build;
    protected Templating $templating;
    protected Context $context;
    protected array $layout;
    protected array $current = [];
    protected bool $isMainCurrent;
    /** @AppConfig(entity) */
    protected array $entities;
    protected string $entityType;
    protected string $layoutName;
    protected bool $renderLazyBlocks = false;

    /** @noinspection PhpUnused */
    protected function defineEntityType(): void
    {
        $this->entityType = $this->context->entity->type;
    }

    /** @noinspection PhpUnused */
    protected function defineLayoutName(): void
    {
        $this->layoutName = $this->entities[$this->entityType]['layout'];
    }

    /** @noinspection PhpUnused */
    protected function defineLayout(): void
    {
        $this->layout = $this->build->load('layout/' . $this->layoutName);
    }

    public function page(): void
    {
        $this->isMainCurrent = true;
        $this->current = [];
        $this->block();
    }

    public function setRenderLazyBlocks(bool $value): void
    {
        $this->renderLazyBlocks = $value;
    }

    /**
     * Return is always empty string but declared return type "string" allows to use <?=
     */
    public function block(string $blockName = '', array $vars = []): string
    {
        $this->current[] = $block = $this->resolveBlock($blockName);

        try {
            $this->openBlockWrapper($block);

            if ($this->canRenderBlock($block)) {
                [$before, $after] = $this->fetchBeforeAfterBlocks($block);
                $before && $this->block('before', $vars);

                if (isset($block['template']) && (!empty($blockName) || $this->isMainCurrent)) {
                    $this->isMainCurrent = false;
                    $this->renderBlockTemplate($block, $vars);
                } else {
                    $this->isMainCurrent = false;
                    $this->renderBlockContent($block, $vars);
                }

                $after && $this->block('after', $vars);
            }

            $this->closeBlockWrapper($block);
        } catch (Throwable $e) {
            $this->handleBlockError($e);
        }

        array_pop($this->current);
        $this->flushRendering();

        return '';
    }

    protected function canRenderBlock(array $block): bool
    {
        if (!$this->renderLazyBlocks && ($block['type'] ?? '') === 'lazy') {
            return false;
        }

        return true;
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
            throw new LightnaException('Block id "' . $blockId . '" not found');
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
            throw new LightnaException("Block \"$blockName\" not found");
        }

        return $block;
    }

    protected function fetchBeforeAfterBlocks(array &$block): array
    {
        return [$this->fetchBeforeBlock($block), $this->fetchAfterBlock($block)];
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

    protected function isBlockWrapperNeeded(array $block): bool
    {
        return ($block['id'] ?? '')
            && (
                ($block['private'] ?? false)
                || in_array($block['type'] ?? '', ['lazy', 'dynamic'])
            );
    }

    protected function openBlockWrapper(array $block): void
    {
        if ($this->isBlockWrapperNeeded($block)) {
            print('<div id="block-wrapper-' . escape($block['id']) . '">');
        }
    }

    protected function closeBlockWrapper(array $block): void
    {
        if ($this->isBlockWrapperNeeded($block)) {
            print('</div>');
        }
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
            if (is_a($type, EntityData::class, true)) {
                $vars[$name] = getobj($this->entities[$this->entityType]['data']);
            }
            // Other variables will be defined in \Lightna\Engine\App\Templating::getTemplateVars
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

    public function getPrivateBlockIds(): array
    {
        return array_keys($this->layout['privateBlocks']);
    }
}
