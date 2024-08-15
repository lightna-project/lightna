<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;

class Template extends CompilerA
{
    public function make(): void
    {
        $templates = [];
        $schema = [];

        $this->walkFilesInModules(
            'template',
            ['phtml'],
            function ($subPath, $file) use (&$templates, &$schema) {
                $templates[$subPath] = $file;
                $schema[$subPath] = $this->getTemplateSchema($file);
            }
        );

        $this->compiled->save('template/map', $templates);
        $this->compiled->save('template/schema', $schema);
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
}
