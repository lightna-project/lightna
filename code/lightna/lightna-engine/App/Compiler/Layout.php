<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;

class Layout extends CompilerA
{
    /** @AppConfig(enabled_modules) */
    protected array $enabledModules;
    protected array $layouts;
    protected array $templateMap;

    /** @noinspection PhpUnused */
    protected function defineTemplateMap(): void
    {
        $this->templateMap = $this->build->load('template/map');
    }

    public function make(): void
    {
        $this->collectFiles();
        $this->parseFiles();
        $this->extend();
        $this->applyDirectives();
        $this->applyComponents();
        $this->validateIds();
        $this->indexBlockIds();
        $this->indexPrivateBlocks();
        $this->save();
    }

    protected function collectFiles(): void
    {
        $directories = [];
        foreach ($this->enabledModules as $module) {
            $directories[] = LIGHTNA_ENTRY . $module['path'] . '/';
        }

        $files = [];
        foreach ($directories as $dir) {
            $files = merge($files, rscan($dir, '~layout/.+\.yaml~'));
        }

        $this->layouts = [];
        foreach ($files as $file) {
            $key = preg_replace(['~^.+?/layout/~', '~\.yaml$~'], '', $file);
            $this->layouts[$key]['files'][] = $file;
        }
    }

    protected function parseFiles(): void
    {
        foreach ($this->layouts as $key => $value) {
            $final = [];
            foreach ($value['files'] as $file) {
                $layout = yaml_parse_file($file);
                $layout = $this->expandKeys($layout);
                $layout = $this->alignValues($layout);
                $layout = $this->expandIds($layout);
                $final = merge($final, $layout);
            }
            $this->layouts[$key]['data'] = $final;
        }
    }

    protected function expandKeys(array $data): array
    {
        $expanded = [];
        foreach ($data as $k => $v) {
            $v = is_array($v) ? $this->expandKeys($v) : $v;
            $k = (string)$k;
            if ($k[0] === '.' && strlen($k) >= 2) {
                $k = trim($k, '.');
                $path = explode('.', $k);
                $dest = &$expanded;
                foreach ($path as $part) {
                    $dest = &$dest['.'][$part];
                }
                $dest = $v;
            } else {
                $expanded[$k] = $v;
            }
        }

        return $expanded;
    }

    protected function expandIds(array $data): array
    {
        $ids = $this->getAllBlockIds($data);
        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'id=')) {
                continue;
            }
            $id = substr($key, 3);
            if (!isset($ids[$id])) {
                throw new Exception("Block id not found in '{$key}'");
            }
            $path = str_replace('/', '/./', $ids[$id]);
            $block = merge(array_path_get($data, $path), $value);
            $block = $this->alignValues($block);
            array_path_set($data, $path, $block);
        }

        return $data;
    }

    protected function extend(): void
    {
        // Collect extends
        foreach ($this->layouts as $key => $value) {
            $this->layouts[$key]['extends'] = $this->getExtends($key);
        }

        // Extend
        foreach ($this->layouts as $key => $value) {
            $final = [];
            foreach ($value['extends'] as $extend) {
                $final = merge(
                    $final,
                    $this->layouts[$extend]['data']
                );
            }
            $final = merge(
                $final,
                $value['data']
            );

            $this->layouts[$key]['data'] = $final;
        }
    }

    protected function applyDirectives(): void
    {
        foreach ($this->layouts as &$layout) {
            LayoutDirectives::apply($layout['data']);
        }
    }

    protected function applyComponents(): void
    {
        foreach ($this->layouts as $name => $layout) {
            $this->layouts[$name]['data'] = $this->applyLayoutComponents($layout['data']);
        }
    }

    protected function applyLayoutComponents(array $node, $path = ''): array
    {
        foreach ($node['.'] as $name => $child) {
            if (is_string($child)) {
                $path .= '.' . $name;
                $component = preg_replace('~\.yaml$~', '', $child, -1, $c);
                if ($c !== 1) {
                    throw new Exception("Invalid component reference \"$child\" for \"$path\" - unknown extension.");
                }
                if (!isset($this->layouts[$component])) {
                    throw new Exception("Invalid component reference \"$child\" for \"$path\" - path not found.");
                }

                $childData = $this->layouts[$component]['data'];
            } else {
                $childData = $child;
            }

            $node['.'][$name] = $this->applyLayoutComponents($childData, $path);
        }

        return $node;
    }

    protected function save(): void
    {
        foreach ($this->layouts as $name => $layout) {
            $this->build->save(
                'layout/' . $name,
                $this->layouts[$name]['data']
            );
        }
    }

    protected function getExtends(string $key): array
    {
        $extends = [];
        if (!isset($this->layouts[$key])) {
            throw new Exception('Layout extend "' . $key . '" not found');
        }
        $layout = $this->layouts[$key]['data'];
        if (isset($layout['extends']) && is_array($layout['extends'])) {
            $extends = $layout['extends'];
        }

        foreach ($extends as $extend) {
            $extends = merge(
                $this->getExtends($extend),
                $extends
            );
        }

        return $extends;
    }

    protected function alignValues(array $layout, bool $isBlocksNode = false, string $path = ''): array
    {
        if (preg_match('~\w+/\w+/[.]$~', $path)) {
            throw new Exception('Unexpected "." node in "' . ltrim($path, '/') . '"');
        }

        $aligned = [];
        if ($path === '' && !isset($aligned['.'])) {
            $aligned['.'] = [];
        }

        foreach ($layout as $key => $value) {
            if (is_null($value) && $isBlocksNode) {
                throw new Exception("Block \"$path/$key\" is NULL");
            } elseif (is_array($value)) {
                $aligned[$key] = $this->alignValues($value, $key === '.', $path . '/' . $key);
                if ($isBlocksNode && !isset($value['.'])) {
                    $aligned[$key]['.'] = [];
                }
            } else {
                if ($key === 'template') {
                    $value = ltrim($value, '/');
                    if (!isset($this->templateMap[$value])) {
                        throw new Exception('Template "' . $value . '" not found');
                    }
                }
                $aligned[$key] = $value;
            }
        }

        return $aligned;
    }

    protected function indexBlockIds(): void
    {
        foreach ($this->layouts as $name => $layout) {
            $ids = $this->getAllBlockIds($layout['data']);
            $this->layouts[$name]['data']['blockById'] = $ids;
        }
    }

    protected function getAllBlockIds(array $layout, string $path = ''): array
    {
        $ids = [];
        foreach ($layout['.'] as $key => $block) {
            if (is_string($block)) {
                // Skip components
                continue;
            }
            if (isset($block['id'])) {
                $ids[$block['id']] = $path . '/' . $key;
            }
            $ids = merge($ids, $this->getAllBlockIds($block, $path . '/' . $key));
        }

        return $ids;
    }

    protected function indexPrivateBlocks(): void
    {
        foreach ($this->layouts as $name => $layout) {
            $ids = $this->indexPrivateBlocksRecursive($layout['data']);
            $this->layouts[$name]['data']['privateBlocks'] = $ids;
        }
    }

    protected function indexPrivateBlocksRecursive(array $layout, string $path = ''): array
    {
        $ids = [];
        foreach ($layout['.'] as $key => $block) {
            if (isset($block['id']) && ($block['private'] ?? false)) {
                $ids[$block['id']] = 1;
            }
            $ids = merge($ids, $this->indexPrivateBlocksRecursive($block, $path . '/' . $key));
        }

        return $ids;
    }

    protected function validateIds(): void
    {
        foreach ($this->layouts as $name => $layout) {
            $this->validateIdsRecursive($name, $layout['data']);
        }
    }

    protected function validateIdsRecursive(string $name, array $layout, string $path = ''): void
    {
        foreach ($layout['.'] as $key => $block) {
            $hasId = isset($block['id']) && is_string($block['id']) && $block['id'] !== '';
            $requiresId = ($block['private'] ?? false) || in_array($block['type'] ?? '', ['dynamic', 'lazy']);

            if (!$hasId && $requiresId) {
                throw new Exception(
                    'The block "' . $name . ':' . ($path . '/' . $key)
                    . '" is missing a required "id" attribute. Please define an "id" for this block.',
                );
            }

            $this->validateIdsRecursive($name, $block, $path . '/' . $key);
        }
    }
}
