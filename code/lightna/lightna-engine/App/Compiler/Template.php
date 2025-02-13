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
            function ($subPath, $file, $moduleName) use (&$templates, &$schema) {
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
        $statements = $this->getDocStatements($content);
        foreach ($statements as $statement) {
            $ms = [];
            if (!preg_match('~@var\s+([^\s&|]+).*\s+\\$(\S+)~is', $statement, $ms)) {
                continue;
            }
            $type = ltrim($ms[1], '\\');
            if (!ctype_upper($type[0])) {
                // Skip scalar types
                continue;
            }
            $this->validateType($type, $template);
            $vars[$ms[2]] = $type;
        }

        return $vars;
    }

    protected function getDocStatements(string $content): array
    {
        if (!preg_match_all('~/\*\*(.*?)\*/~ism', $content, $ms)) {
            return [];
        }

        $statements = [];
        foreach ($ms[1] as $doc) {
            $lines = explode("\n", $doc);
            $isImport = false;
            foreach ($lines as $line) {
                if (preg_match('~^\s*\*\s*Import\s*:\s*$~ism', $line)) {
                    $isImport = true;
                    continue;
                }
                if ($isImport && str_starts_with($line, ' * @var ')) {
                    $statements[] = $line;
                }
            }
        }

        return $statements;
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
