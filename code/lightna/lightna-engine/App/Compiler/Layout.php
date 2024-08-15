<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;
use Lightna\Engine\App\Compiled;
use Lightna\Engine\App\ObjectA;

class Layout extends ObjectA
{
    protected Compiled $compiled;
    /** @AppConfig(modules) */
    protected ?array $modules;
    protected array $layouts;
    protected array $templateMap;

    public function make(): void
    {
        $this->templateMap = $this->compiled->load('template/map');

        $directories = [LIGHTNA_SRC];
        if (($modules = $this->modules) && is_array($modules)) {
            foreach ($modules as $dir) {
                $directories[] = LIGHTNA_ENTRY . $dir . '/';
            }
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

        foreach ($this->layouts as $key => $value) {
            $final = [];
            foreach ($value['files'] as $file) {
                $layout = yaml_parse_file($file);
                $layout = $this->expandKeys($layout);
                $final = merge($final, $layout);
            }
            $this->layouts[$key]['data'] = $final;
        }

        $this->extend();

        foreach ($this->layouts as $name => $layout) {
            $layoutData = $this->alignValues($layout['data']);
            LayoutDirectives::apply($layoutData);
            $this->layouts[$name]['data'] = $layoutData;

            $this->compiled->save(
                'layout/' . $name,
                $this->layouts[$name]['data']
            );
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
        foreach ($layout as $key => $value) {
            if (is_array($value)) {
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
}
