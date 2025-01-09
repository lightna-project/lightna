<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;

class Template extends CompilerA
{
    protected array $templates;
    protected array $schema;

    public function make(): void
    {
        $this->collectTemplates();
        $this->collectSchemas();
        $this->applyOverrides();
        $this->save();
    }

    protected function collectTemplates(): void
    {
        $this->walkFilesInModules(
            'template',
            ['phtml'],
            function ($subPath, $file, $modulePath, $moduleName) use (&$templates, &$schema) {
                $this->templates[$moduleName . '/' . $subPath] = $file;
                $schema[$moduleName . '/' . $subPath] = $this->getTemplateSchema($file);
            }
        );
    }

    protected function collectSchemas(): void
    {
        foreach ($this->templates as $name => $file) {
            $this->schema[$name] = $this->getTemplateSchema($file);
        }
    }

    protected function getTemplateSchema(string $template): array
    {
        if (!is_file($file = LIGHTNA_ENTRY . $template)) {
            throw new Exception('Template "' . $template . '" not found');
        }

        $vars = [];
        $content = file_get_contents($file);
        if (!preg_match_all('~/\\*\\*(.+?)\\*/~ism', $content, $ms)) {
            return $vars;
        }

        $docs = $ms[1];
        foreach ($docs as $doc) {
            $ms = [];
            if (!preg_match_all('~@var\s+(\S+)\s+\\$(\S+)~ism', $doc, $ms)) {
                continue;
            }
            for ($i = 0; $i < count($ms[0]); $i++) {
                $type = ltrim($ms[1][$i], '\\');
                if (!ctype_upper($type[0])) {
                    // Skip scalar types
                    continue;
                }
                $this->validateType($type, $template);
                $vars[$ms[2][$i]] = $type;
            }
        }

        return $vars;
    }

    protected function validateType(string $type, string $template): void
    {
        try {
            class_exists($type);
        } catch (Exception $e) {
            throw new Exception('Type "' . $type . '" not found in template "' . $template);
        }
        if (!str_contains($type, '\\Data\\')) {
            throw new Exception('Not allowed object "' . $type . '" requested in template "' . $template . '"');
        }
    }

    protected function applyOverrides(): void
    {
        foreach ($this->templates as $name => &$file) {
            if (isset($this->overrides['template'][$name])) {
                $file = $this->overrides['template'][$name]['rel'];
            }
        }
    }

    protected function save(): void
    {
        $this->build->save('template/map', $this->templates);
        $this->build->save('template/schema', $this->schema);
    }
}
